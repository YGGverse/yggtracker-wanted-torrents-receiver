# yggtracker-wanted-torrents-receiver

Crontab script that allows to receive wanted torrents from multiple [YGGtracker](https://github.com/YGGverse/YGGtracker) nodes

#### Install

`git clone https://github.com/YGGverse/yggtracker-wanted-torrents-receiver.git`

#### Usage

`php src/cli.php`

or

`* * * * * /usr/bin/php src/cli.php > /dev/null 2>&1`

#### Config

Configuration files placed at `/config` folder.

Application works with default settings, below provided documentation about the options for customization.

##### local.json

```
{
  "import":
  {
    "ftp":                   // Common rules for FTP connections
    {
      "timeout":5,           // How many seconds to wait for each provider response
      "directories":         // Remote folders to grab
      [
        "all"
      ]
    },
    "require":
    {
      "approved":true        // Require approved torrents import only (related to provide.approved option in remote.json for each provider)
    },
    "storage":
    {
      "directory":"storage", // Storage directory by default
      "common":true          // Copy all torrents imported from providers folders to the storage/_common/{hash}.torrent
                             // This mode check files MD5 hash sum to prevent duplicates in single folder from different providers
                             // Useful when bittorrent client does not support support listening of multiple folders, recursive mode
    }
  },
  "update":
  {
    "config":
    {

      "remote":             // This option allows to auto-update remote.json file on the fly without updating codebase with git clone
      {
        "enabled": true,    // If disabled, local file will not be updated, but manually
        "cache":86400,      // How many seconds to wait before ask repository for remote.json updates (after last file write)

        "repository":
        "https://raw.githubusercontent.com/YGGverse/yggtracker-wanted-torrents-receiver/main/config/remote.json"
      }
    }
  }
}
```

##### remote.json

Configuration contains available YGGtracker providers.

Could be auto-updated from repository when owner have enabled `update.config.remote.enabled` option in `local.json`
that makes registry actualization simpler for recipients and providers

```
[
  {
    "description":
    {
      "name":"YGGtracker",                                                // Used as storage subfolder
      "description":"YGGtracker official node",                           // Just provider description
      "url":"http://[201:23b4:991a:634d:8359:4521:5576:15b7]/yggtracker/" // Provider's website
    },
    "ftp":
    {
      "host":"201:23b4:991a:634d:8359:4521:5576:15b7",                    // Connection host
      "port":21,                                                          // Connection port
      "passive":true,                                                     // Recommended passive mode for better compatibility
      "username":"anonymous",                                             // YGGtracker instances usually provides public FTP access
      "password":"anonymous",
      "directory":"/yggtracker/torrents/wanted"                           // Directory where wanted torrent files placed
    },
    "provide":
    {
      "approved":true                                                     // Tells to receiver that node administrator check the torrents before send to API
    }
  }
],
...
```

#### Bash, python?

Feel free to contribute!

#### Add new YGGtracker node

Just send PR to remote.json file

#### Feedback

Any questions and bug reports, please send to the [Issues](https://github.com/YGGverse/yggtracker-wanted-torrents-receiver/issues)!

#### See also

* [YGGtracker - BitTorrent Network for Yggdrasil](https://github.com/YGGverse/YGGtracker)
* [YGGtracker Search Plugin for qBittorrent](https://github.com/YGGverse/qbittorrent-yggtracker-search-plugin)