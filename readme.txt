=== url-info ===
Contributors: tlhackque
Tags: HTTP,HEAD,URL,atributes
Requires at least: 4.9.0
Tested up to: 5.7.2
Requires PHP: 5.6.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Stable tag: 1.1

Adds support for the urlinfo shortcode, which retrieves information from the HTTP headers returned from a resource.

== Description ==
Provides the urlinfo shortcode, which displays various items from the HTTP headers provided by a URL

Usage:
    [urlinfo options]text[/urlinfo]
    options:
       debug="0"            True to enable debugging
       item="mtime"         Space-separated list of header values to retrieve
             mtime           Last-Modified date
             expires         Expires date
             size            Content-Length
             etag            ETag
             type            Content-Type
             type-name       Common name of content-type
             type-ext        Common file extension of content-type
             type-desc       Description of content-type
       format="d-M-Y H:i:s" Display format for dates (see PHP date)
       prefix="<br />"      HTML to insert before value
       suffix=''            HTML to insert after value
       timezone=(from php.ini) Timezone used to display dates
    text contains a WordPress link referencing the URL of a remote resource to be queried
    This will be something like <a href="...">label</a>.  The URL is extracted from the href.

The shortcode returns the text, followed by (for each item requested)
the prefix, value, and suffix.

E.g. To add columns to table row, use shortcode with prefix='<td>'
in previous cell.  Merge row cells, set width with with header </td>
is not required & you don't want the </td> from the cell containing
the shortcode.

If a value can't be obtained, an empty string is normally returned.
However, if the debug option is true, an error message will appear.
The prefix and suffix will be applied in either case.  If multiple
items were requested and the resource is inaccessible, only a single
item is returned.

The debug output can be lengthy and is likely to be confusing to
end users.  It is active only in preview mode and ignored for
published pages.

Note that each occurance of [urlinfo] will cause a HEAD network
transaction.  This may slow page loads.  However, multiple items
retrieved by a single [urlinfo] occurance use only one HEAD
transaction.

Also, note that this transaction is initiated by the server, not by
the user.  Therefore, any access controls need to be carefully considered.

== Installation ==
1. Create /wp-content/plugins/url-info/
2. Upload url-info.php and readme.txt to /wp-content/plugins/url-info/url-info.php
3. Activate the plugin through the 'Plugins' menu in WordPress

== Support ==
https://github.com/tlhackque/wp_url-info/issues

== Changelog ==

= 1.0 =
* Initial version.
= 1.1 =
* Remove any attributes (such as ;CHARSET=) from Content-Type values.

