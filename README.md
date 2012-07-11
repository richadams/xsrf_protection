# XSRF/CSRF Protection

Version: 0.1  
Author: Rich Adams (http://richadams.me)  
Licensed under the GPL.

## Overview

Protects the backend of Symphony CMS against cross-site request forgery.

## What it does

  - Adds a `xsrf` input to any form using the POST method with a one use token.
  - When the backend receives a POST request, the token is validated.
  - If the token is incorrect, not provided, or has expired, then the request is rejected.
  
## Requirements

Only tested in Symphony CMS 2.3

## Installation

  - Unzip the file.
  - Put the ***xsrf_protection*** folder into your extension directory.
  - Enable the same as any other extension.
  - Add the following to your configuration file,
  
        "xsrf-protection" => array("token-lifetime"               => "15 mins", // How long the tokens are valid for.
                                   "invalidate-tokens-on-request" => true),     // If true, then tokens are invalidated on every request or after expiry time, whichever is first. If false, tokens only expire after the lifetime.

## Configuration Options

  - **Token Lifetime** - How long before a token expires and becomes invalid. Default is 15 minutes. Can specify any `strtotime()` recognised string.
  - **Invalidate Tokens On Request** - If set, this will invalidate any previous tokens on every request. If not set, then tokens will only be invalidated once their expiry time is reached. Most times you probably want this disabled, otherwise when a user goes back and submits something again, they'll get the XSRF error even if the token is still within it's lifetime.
