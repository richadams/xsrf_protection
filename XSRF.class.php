<?php
if (!defined("__IN_SYMPHONY__")) die("You cannot directly access this file.");

class XSRF
{
    // This generates a secure nonce.
    public static function generateNonce($length = 20)
    {
        // Base64 encode some random binary data, and strip the "=" if there are any.
        if (function_exists("openssl_random_pseudo_bytes"))
        {
            return str_replace("=", "", base64_encode(openssl_random_pseudo_bytes($length)));
        }

        // Fallback if openssl not available
        if (is_readable("/dev/urandom"))
        {
            if (($handle = @fopen("/dev/urandom", "rb")) !== false)
            {
                $bytes = fread($handle, $length);
                fclose($handle);
                return str_replace("=", "", base64_encode($bytes));
            }
        }

        // Fallback if /dev/urandom not readable.
        $state = microtime();
        for ($i = 0; $i < 1000; $i += 20) { $state = sha1(microtime() . $state); }
        return str_replace("=", "", base64_encode(substr($state, 0, $length)));
    }

    // Creates the form input to use.
    public static function formToken()
    {
        // <input type="hidden" name="xsrf" value=" . self::getToken() . " />
        $obj = new XMLElement("input");
        $obj->setAttribute("type", "hidden");
        $obj->setAttribute("name", "xsrf");
        $obj->setAttribute("value", self::getToken());
        return $obj;
    }

    // This is the nonce used to stop CSRF/XSRF attacks. It's stored in the user session.
    public static function getToken()
    {
        $nonce                   = self::generateNonce(20);
        $tokens                  = $_SESSION["xsrf_tokens"];
        $tokens[$nonce]          = strtotime("+" . Symphony::Configuration()->get("token-lifetime", "xsrf-protection"));
        $_SESSION["xsrf_tokens"] = $tokens;
        return $nonce;
    }

    // This will determine if a token is valid.
    public static function validateToken($xsrf)
    {
        $tokens = $_SESSION["xsrf_tokens"];

        // Sanity check
        if ($tokens == null) { return false; }

        // Check that the token exists, and time has not expired.
        foreach ($tokens as $key => $expires)
        {
            if ($key == $xsrf
                && time() <= $expires)
            {
                return true;
            }
        }
        return false;
    }

    // This will validate a request has a good token.
    public static function validateRequest()
    {
        // Only care if we have a POST request.
        if (count($_POST) > 0)
        {
            if (!self::validateToken($_POST["xsrf"]))
            {
                // Token was invalid, show an error page.
                self::throwXSRFException();
            }
        }

        // We're all good, so clear any tokens that can be cleared
        if (Symphony::Configuration()->get("invalidate-tokens-on-request", "xsrf-protection"))
        {
            unset($_SESSION["xsrf_tokens"]);
        }
        // Otherwise, just clear the ones that have expired.
        else
        {
            if (is_array($_SESSION["xsrf_tokens"]))
            {
                $_SESSION["xsrf_tokens"] = array_filter($_SESSION["xsrf_tokens"], function($value) { return (time() <= $value);});
            }
        }
    }

    // The error function that's thrown if the token is invalid.
    public static function throwXSRFException()
    {
        $msg =
            __('Request was rejected for having an invalid cross-site request forgery token.')
            . '<br/><br/>' .
            __('Please go back and try again.');
        throw new SymphonyErrorPage($msg, __('Invalid XSRF Token'));
    }
}
