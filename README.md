# toxyy Moderate While Searching v0.1.0

[Topic on phpBB.com]()

## Requirements

phpBB 3.3+ & PHP 7.4+

**This extension requires a core edit:**

Open ./forum/phpbb/search/fulltext_mysql.php

Find `$this->db->sql_escape(html_entity_decode($this->search_query, ENT_COMPAT)`
Replace with `$this->db->sql_escape(html_entity_decode($search_query, ENT_COMPAT)`

(We are changing `$this->search_query` to `$search_query`)

A pull request will be made for this change by 3.3.9

[![Build Status](https://github.com/toxyy/moderatewhilesearching/workflows/Tests/badge.svg)](https://github.com/toxyy/moderatewhilesearching/actions)
## Features

Allows you to moderate while searching.

## Quick Install

You can install this on the latest release of phpBB 3.3 by following the steps below:

* Create `toxyy/moderatewhilesearching` in the `ext` directory.
* Download and unpack the repository into `ext/toxyy/moderatewhilesearching`
* Enable `Moderate While Searching` in the ACP at `Customise -> Manage extensions`.

## Uninstall

* Disable `Moderate While Searching` in the ACP at `Customise -> Extension Management -> Extensions`.
* To permanently uninstall, click `Delete Data`. Optionally delete the `/ext/toxyy/moderatewhilesearching` directory.

## Support

* Report bugs and other issues to the [Issue Tracker](https://github.com/toxyy/moderatewhilesearching/issues).

## Screenshots

## License

[GPL-2.0](license.txt)
