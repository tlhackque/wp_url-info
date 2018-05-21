=== url-info ===
Contributors: TimotheLitt
Tags: HTTP,HEAD,URL,Last-Modified
Requires at least: 4.9.0
Tested up to: 4.9.6
Requires PHP: 5.6.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Provide information from HTTP headers returned by a URL

== Description ==
Provides the urlinfo shortcode, which displays various items from the HTTP headers from a URL

Usage:
    [url-info options]text[/url-info]
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
    text contains a link referencing the URL of a remote resource to be queried

The shortcode returns the text, followed by (for each item requested)
the prefix, value, and suffix.

E.g. To add columns to table row, use shortcode with prefix='<td>'
in previous cell.  Merge row cells, set width with with header </td>
is not required & you don't want the </td> from the cell containing
the shortcode.

If a value can't be obtained, an empty string is normally returned.
However, if the debug option is true, an error message will appear.

Note that each occurance of [url-info] will cause a HEAD network
transaction.  This may slow page loads.  Also, note that this transaction
is initiated by the server, not by the user.  Therefore, any access
controls need to be carefully considered.

== Installation ==
Install the usual way.

== Comment ==
See https://generatewp.com/plugin-readme/ for generator

