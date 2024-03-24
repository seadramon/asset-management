<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. A "local" driver, as well as a variety of cloud
    | based drivers are available for your choosing. Just store away!
    |
    | Supported: "local", "ftp", "s3", "rackspace"
    |
    */

    'default' => 'local',

    /*
    |--------------------------------------------------------------------------
    | Default Cloud Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Many applications store files both locally and in the cloud. For this
    | reason, you may specify a default "cloud" driver here. This driver
    | will be bound as the Cloud disk implementation in the container.
    |
    */

    'cloud' => 's3',

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Here you may configure as many filesystem "disks" as you wish, and you
    | may even configure multiple disks of the same driver. Defaults have
    | been setup for each driver as an example of the required options.
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root'   => storage_path('app').'/uploads/',
        ],

        'ftp' => [
            'driver'   => 'ftp',
            'host'     => 'ftp.example.com',
            'username' => 'your-username',
            'password' => 'your-password',

            // Optional FTP Settings...
            // 'port'     => 21,
            // 'root'     => '',
            // 'passive'  => true,
            // 'ssl'      => true,
            // 'timeout'  => 30,
        ],

        'sftp' => [
            'driver' => 'sftp',
            'host' => '128.46.8.69',
            'port' => 22,
            'username' => 'webadmin',
            'password' => 'webadminpass',
//            'privateKey' => '4e:c1:ae:4a:35:cc:bc:2a:ac:57:29:42:0e:db:de:83',
            //'privateKey' => '44:bb:d7:67:74:30:ef:dd:1f:d3:16:99:02:18:9e:fa',
            'root' => '/data/image/asset',
            'timeout' => 200,
        ],

        'sftp-aset-img' => [
            'driver' => 'sftp',
            'host' => '128.46.8.78',
            'port' => 22,
            'username' => 'asset_user',
            'password' => 'assetpassw0rd',
//            'privateKey' => '4e:c1:ae:4a:35:cc:bc:2a:ac:57:29:42:0e:db:de:83',
            //'privateKey' => '44:bb:d7:67:74:30:ef:dd:1f:d3:16:99:02:18:9e:fa',
            'root' => '/data/asset',
            'timeout' => 200,
        ],

        'sftp-doc' => [
            'driver' => 'sftp',
            'host' => '128.46.8.69',
            'port' => 22,
            'username' => 'webadmin',
            'password' => 'webadminpass',
//            'privateKey' => '4e:c1:ae:4a:35:cc:bc:2a:ac:57:29:42:0e:db:de:83',
            //'privateKey' => '44:bb:d7:67:74:30:ef:dd:1f:d3:16:99:02:18:9e:fa',
            'root' => '/data/document/asset',
            'timeout' => 200,
        ],

        'sftp-tangki' => [
            'driver'        => 'sftp',
            'port'          => 22,
            'host'          => '128.46.8.69',
            'username'      => 'webadmin',
            'password'      => 'webadminpass',
            'root'          => '/data/image/tangkiair/dev/img_ttd/',
            /*'visibility'    => 'public',
            'directoryPerm' => 0775,
            'timeout'       => 10,
            'cache'         => [
                'store'     => 'memcached',
                'expire'    => 600,
                'prefix'    => 'cache-prefix',
            ],*/
        ],

        's3' => [
            'driver' => 's3',
            'key'    => 'your-key',
            'secret' => 'your-secret',
            'region' => 'your-region',
            'bucket' => 'your-bucket',
        ],

        'rackspace' => [
            'driver'    => 'rackspace',
            'username'  => 'your-username',
            'key'       => 'your-key',
            'container' => 'your-container',
            'endpoint'  => 'https://identity.api.rackspacecloud.com/v2.0/',
            'region'    => 'IAD',
            'url_type'  => 'publicURL',
        ],

    ],

];
