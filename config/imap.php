<?php
/*
* File:     imap.php
* Category: config
* Author:   M. Goldenbaum
* Created:  24.09.16 22:36
* Updated:  -
*
* Description:
*  -
*/

return [

    /*
    |--------------------------------------------------------------------------
    | Default account
    |--------------------------------------------------------------------------
    |
    | The default account to use when calling Imap::account('default')
    |
    */
    'default' => env('IMAP_DEFAULT_ACCOUNT', 'default'),

    /*
    |--------------------------------------------------------------------------
    | Available accounts
    |--------------------------------------------------------------------------
    |
    | Please list all accounts you are planning to use within the array below.
    |
    */
    'accounts' => [

        'default' => [//FIXME: ADD YOUR CREDENTIALS
            'host'  => env('IMAP_HOST', 'localhost'),
            'port'  => env('IMAP_PORT', 993),
            'protocol'  => env('IMAP_PROTOCOL', 'imap'), //might be 'imaps' or 'pop3s'
            'encryption'    => env('IMAP_ENCRYPTION', 'ssl'), // Supported: false, 'ssl', 'tls', 'starttls'
            'validate_cert' => env('IMAP_VALIDATE_CERT', true),
            'username' => env('IMAP_USERNAME', 'root'),
            'password' => env('IMAP_PASSWORD', ''),
            'authentication' => env('IMAP_AUTHENTICATION', null),
            'proxy' => [
                'socket' => null,
                'hostname' => null,
                'port' => null,
                'protocol' => null,
                'username' => null,
                'password' => null,
            ]
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Available options
    |--------------------------------------------------------------------------
    |
    | Please list all available options below.
    |
    */
    'options' => [
        'debug' => false,
        'uid_cache' => true,
        'connection_timeout' => 5,
        'response_timeout' => 10,
        'request_timeout' => 10,
        'open' => [
            // 'DISABLE_AUTHENTICATOR' => 'GSSAPI'
        ],
        'fetch_order' => 'asc',
        'dispositions' => ['attachment', 'inline'],
        'soft_fail' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Available events
    |--------------------------------------------------------------------------
    |
    */
    'events' => [
        "message" => [
            'new' => \Webklex\IMAP\Events\MessageNewEvent::class,
            'moved' => \Webklex\IMAP\Events\MessageMovedEvent::class,
            'copied' => \Webklex\IMAP\Events\MessageCopiedEvent::class,
            'deleted' => \Webklex\IMAP\Events\MessageDeletedEvent::class,
            'restored' => \Webklex\IMAP\Events\MessageRestoredEvent::class,
        ],
        "folder" => [
            'new' => \Webklex\IMAP\Events\FolderNewEvent::class,
            'moved' => \Webklex\IMAP\Events\FolderMovedEvent::class,
            'deleted' => \Webklex\IMAP\Events\FolderDeletedEvent::class,
        ],
        "flag" => [
            'new' => \Webklex\IMAP\Events\FlagNewEvent::class,
            'deleted' => \Webklex\IMAP\Events\FlagDeletedEvent::class,
        ],
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Available masks
    |--------------------------------------------------------------------------
    */
    'masks' => [
        'message' => \Webklex\PHPIMAP\Support\Masks\MessageMask::class,
        'attachment' => \Webklex\PHPIMAP\Support\Masks\AttachmentMask::class,
    ],

]; 