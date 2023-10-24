<?php

// Init PHP
declare(strict_types=1);

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Prevent multi-thread execution
$semaphore = sem_get(crc32('yggtracker-wanted-torrents-receiver'), 1);

if (false === sem_acquire($semaphore, true))
{
  exit;
}

// Init local config
$local = json_decode(
    file_get_contents(__DIR__ . '/../config/local.json')
);

// Init remote config
if ($local->update->config->remote->enabled)
{
    // Reset remote config cache
    if ($local->update->config->remote->cache + (int) filectime(__DIR__ . '/../config/remote.json') < time())
    {
        // Cache results
        if ($result = @file_get_contents($local->update->config->remote->repository))
        {
            file_put_contents(
                __DIR__ . '/../config/remote.json',
                $result
            );
        }
    }
}

// Sync remotes
foreach (
    json_decode(
        file_get_contents(__DIR__ . '/../config/remote.json')
    ) as $remote)
{
    // Apply approved filters
    if ($local->import->require->approved && !$remote->provide->approved)
    {
        continue;
    }

    // Connect remote
    if (!$connection = ftp_connect($remote->ftp->host, $remote->ftp->port, $local->import->ftp->timeout))
    {
        continue;
    }

    // Login
    if (!ftp_login($connection, $remote->ftp->username, $remote->ftp->password))
    {
        continue;
    }

    // Apply passive mode if required
    if (!ftp_pasv($connection, $remote->ftp->passive))
    {
        continue;
    }

    // Navigate to wanted directory
    if (!ftp_chdir($connection, $remote->ftp->directory))
    {
        continue;
    }

    // Scan directories
    foreach ($local->import->ftp->directories as $directory)
    {
        // Get torrents
        foreach (ftp_nlist($connection, $directory) as $torrent)
        {
            // Init provider directory
            @mkdir(
                $local->import->storage->directory . '/' . $remote->description->name . '/' . $directory,
                0755,
                true
            );

            // Save torrents
            ftp_get(
                $connection,
                $local->import->storage->directory . '/' . $remote->description->name . '/' . $torrent,
                $torrent
            );

            // Common storage mode enabled
            if ($local->import->storage->common)
            {
                // Init common folder
                @mkdir(
                    $local->import->storage->directory . '/_common',
                    0755,
                    true
                );

                // Prevent same file duplicates from different providers
                $hash = md5_file(
                    $local->import->storage->directory . '/' . $remote->description->name . '/' . $torrent
                );

                // Copy torrent file into the common directory if not exists yet
                if (!file_exists($local->import->storage->directory . '/_common/' . $hash . '.torrent'))
                {
                    copy(
                        $local->import->storage->directory . '/' . $remote->description->name . '/' . $torrent,
                        $local->import->storage->directory . '/_common/' . $hash . '.torrent'
                    );
                }
            }
        }
    }
}