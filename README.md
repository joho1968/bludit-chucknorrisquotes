[![Software License](https://img.shields.io/badge/License-AGPLv3-green.svg?style=flat-square)](LICENSE) [![Bludit 3.15.x](https://img.shields.io/badge/Bludit-3.15.x-blue.svg?style=flat-square)](https://bludit.com) [![Bludit 3.16.x](https://img.shields.io/badge/Bludit-3.16.x-blue.svg?style=flat-square)](https://bludit.com)

# Chuck Norris Quotes Plugin for Bludit

This is a Chuck Norris Quotes Plugin for Bludit 3.15.x and 3.16.x. Later 3.x versions may work.

## Description

The plugin provides random Chuck Norris quotes that can be included and presented in your Bludit page content.

The intended use case is a a bit of fun :blush:

You can display the retrieved quote almost anywhere in Bludit.

_The plugin contains no tracking code of any kind_

## Demo

You can see the plugin in action on [bludit-bs5simplyblog.joho.se](https://bludit-bs5simplyblog.joho.se/chuck-norris-quotes)

## Requirements

Bludit version 3.15.x or 3.16.x

## Installation

1. Download the latest release from the repository or GitHub
2. Extract the zip file into a folder, such as `tmp`
3. Upload the `chucknorrisquotes` folder to your web server or hosting and put it in the `bl-plugins` folder where Bludit is installed
4. Go your Bludit admin page
5. Klick on Plugins and activate the `Chuck Norris Quotes` plugin

## Usage

Simply put `[chucknorrisquote][/chucknorrisquote]` or `[chucknorrisquote/]` or `[chucknorrisquote]` somewhere in your content.

The plugin will respect `<pre>..</pre>` and not parse for the shortcode in that HTML block.

You can customize the refresh interval, the quote category, and if needed, the URIs used for retrieval.

## Other things I've created for Bludit

* [BS5Docs](https://bludit-bs5docs.joho.se), a fully featured Bootstrap 5 documentation theme for Bludit
* [BS5SimplyBlog](https://bludit-bs5simplyblog.joho.se), a fully featured Bootstrap 5 blog theme for Bludit
* [BS5Plain](https://bludit-bs5plain.joho.se), a simplistic and clean Bootstrap 5 blog theme for Bludit

## Changelog

### 1.0.0 (2024-10-16)
* Initial release

## Other notes

This plugin has only been tested with PHP 8.1.x, but should work with other versions too. If you find an issue with your specific PHP version, please let me know and I will look into it.

## License

Please see [LICENSE](LICENSE) for a full copy of AGPLv3.

Copyright 2024 [Joaquim Homrighausen](https://github.com/joho1968); all rights reserved.

This file is part of chucknorrisquotes. chucknorrisquotes is free software.

chucknorrisquotes is free software: you may redistribute it and/or modify it  under
the terms of the GNU AFFERO GENERAL PUBLIC LICENSE v3 as published by the
Free Software Foundation.

chucknorrisquotes is distributed in the hope that it will be useful, but WITHOUT
ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
FITNESS FOR A PARTICULAR PURPOSE. See the GNU AFFERO GENERAL PUBLIC LICENSE
v3 for more details.

You should have received a copy of the GNU AFFERO GENERAL PUBLIC LICENSE v3
along with the chucknorrisquotes package. If not, write to:
```
The Free Software Foundation, Inc.,
51 Franklin Street, Fifth Floor
Boston, MA  02110-1301, USA.
```

## Disclaimer

Legal disclaimer: This plugin and its creator are not affiliated with Chuck Norris, any motion picture corporation, any television corporation, parent, or affiliate corporation. All motion pictures, products, and brands mentioned in this plugin are the respective trademarks and copyrights of their owners. All material in this plugin is intended for humorous entertainment (satire ) purposes only. The content retrieved by this plugin is not necessarily true and should not be regarded as truth.

## Credits

### Other credits

* Kudos to [Diego Najar](https://github.com/dignajar) for [Bludit](https://bludit.com) :blush:
* Kudos to Mathias Schilling for [chucknorris.io](https://api.chucknorris.io) :blush:

The Chuck Norris Quotes Plugin for Bludit was written by Joaquim Homrighausen while converting :coffee: into code.

The Chuck Norris Quotes Plugin for Bludit is sponsored by [WebbPlatsen i Sverige AB](https://webbplatsen.se), Sweden :sweden:

Commercial support and customizations for this plugin is available from WebbPlatsen i Sverige AB.

If you find this Bludit add-on useful, feel free to donate, review it, and or spread the word :blush:

If there is something you feel to be missing from this Bludit add-on, or if you have found a problem with the code or a feature, please do not hesitate to reach out to bluditcode@webbplatsen.se.
