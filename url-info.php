<?php
/**
 * Plugin Name: URL Information
 * Version: 1.1
 * Description: Adds support for the urlinfo tag, which retrieves information from the resource's HTTP headers.
 * Author: Timothe Litt
 * notPlugin URI: https://wikiworld.litts.net/plugins/url-info
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/* See readme for user documentation */

/* urlinfo shortcode handler */

function urlinfo_tag( $attrs, $content=null, $name ) {
    /* default and filter attributes */
    $attrs = shortcode_atts(
          array(
                'debug' => false,
                'prefix' => '<br />',
                'format' => 'd-M-Y H:i:s',
                'timezone' => null,
                'suffix' => '',
                'item' => 'mtime',
               ), $attrs, $name );

    /* Descriptions for common (and not-so-common) MIME types
     * Extracted from https://www.freeformatter.com/mime-types-list.html
     *  perl -e"local \$/; \$text=<>;foreach my \$rec ( \$text =~ /<tr>(.*?)<\\/tr>/imsg ) {@f = \$rec =~ /<td>(.*?)<\\/td>/ig;s/<a.*?>(.*)<\\/a>/$1/ foreach (@f);s/'/\\'/g foreach(@f);print \"        '\$f[1]' => [ 0 => '\" . substr( \$f[2],1 ) . \"', 1 => '\$f[2]', 2 => '\$f[0]' ],\n\";}" file.txt
     */
    static $types = [ /* mime-type, name, ext, description */
        'application/andrew-inset' => [ 0 => '/A', 1 => 'N/A', 2 => 'Andrew Toolkit' ],
        'application/applixware' => [ 0 => 'aw', 1 => '.aw', 2 => 'Applixware' ],
        'application/atom+xml' => [ 0 => 'atom, .xml', 1 => '.atom, .xml', 2 => 'Atom Syndication Format' ],
        'application/atomcat+xml' => [ 0 => 'atomcat', 1 => '.atomcat', 2 => 'Atom Publishing Protocol' ],
        'application/atomsvc+xml' => [ 0 => 'atomsvc', 1 => '.atomsvc', 2 => 'Atom Publishing Protocol Service Document' ],
        'application/ccxml+xml,' => [ 0 => 'ccxml', 1 => '.ccxml', 2 => 'Voice Browser Call Control' ],
        'application/cdmi-capability' => [ 0 => 'cdmia', 1 => '.cdmia', 2 => 'Cloud Data Management Interface (CDMI) - Capability' ],
        'application/cdmi-container' => [ 0 => 'cdmic', 1 => '.cdmic', 2 => 'Cloud Data Management Interface (CDMI) - Contaimer' ],
        'application/cdmi-domain' => [ 0 => 'cdmid', 1 => '.cdmid', 2 => 'Cloud Data Management Interface (CDMI) - Domain' ],
        'application/cdmi-object' => [ 0 => 'cdmio', 1 => '.cdmio', 2 => 'Cloud Data Management Interface (CDMI) - Object' ],
        'application/cdmi-queue' => [ 0 => 'cdmiq', 1 => '.cdmiq', 2 => 'Cloud Data Management Interface (CDMI) - Queue' ],
        'application/cu-seeme' => [ 0 => 'cu', 1 => '.cu', 2 => 'CU-SeeMe' ],
        'application/davmount+xml' => [ 0 => 'davmount', 1 => '.davmount', 2 => 'Web Distributed Authoring and Versioning' ],
        'application/dssc+der' => [ 0 => 'dssc', 1 => '.dssc', 2 => 'Data Structure for the Security Suitability of Cryptographic Algorithms' ],
        'application/dssc+xml' => [ 0 => 'xdssc', 1 => '.xdssc', 2 => 'Data Structure for the Security Suitability of Cryptographic Algorithms' ],
        'application/ecmascript' => [ 0 => 'es', 1 => '.es', 2 => 'ECMAScript' ],
        'application/emma+xml' => [ 0 => 'emma', 1 => '.emma', 2 => 'Extensible MultiModal Annotation' ],
        'application/epub+zip' => [ 0 => 'epub', 1 => '.epub', 2 => 'Electronic Publication' ],
        'application/exi' => [ 0 => 'exi', 1 => '.exi', 2 => 'Efficient XML Interchange' ],
        'application/font-tdpfr' => [ 0 => 'pfr', 1 => '.pfr', 2 => 'Portable Font Resource' ],
        'application/hyperstudio' => [ 0 => 'stk', 1 => '.stk', 2 => 'Hyperstudio' ],
        'application/ipfix' => [ 0 => 'ipfix', 1 => '.ipfix', 2 => 'Internet Protocol Flow Information Export' ],
        'application/java-archive' => [ 0 => 'jar', 1 => '.jar', 2 => 'Java Archive' ],
        'application/java-serialized-object' => [ 0 => 'ser', 1 => '.ser', 2 => 'Java Serialized Object' ],
        'application/java-vm' => [ 0 => 'class', 1 => '.class', 2 => 'Java Bytecode File' ],
        'application/javascript' => [ 0 => 'js', 1 => '.js', 2 => 'JavaScript' ],
        'application/json' => [ 0 => 'json', 1 => '.json', 2 => 'JavaScript Object Notation (JSON)' ],
        'application/mac-binhex40' => [ 0 => 'hqx', 1 => '.hqx', 2 => 'Macintosh BinHex 4.0' ],
        'application/mac-compactpro' => [ 0 => 'cpt', 1 => '.cpt', 2 => 'Compact Pro' ],
        'application/mads+xml' => [ 0 => 'mads', 1 => '.mads', 2 => 'Metadata Authority  Description Schema' ],
        'application/marc' => [ 0 => 'mrc', 1 => '.mrc', 2 => 'MARC Formats' ],
        'application/marcxml+xml' => [ 0 => 'mrcx', 1 => '.mrcx', 2 => 'MARC21 XML Schema' ],
        'application/mathematica' => [ 0 => 'ma', 1 => '.ma', 2 => 'Mathematica Notebooks' ],
        'application/mathml+xml' => [ 0 => 'mathml', 1 => '.mathml', 2 => 'Mathematical Markup Language' ],
        'application/mbox' => [ 0 => 'mbox', 1 => '.mbox', 2 => 'Mbox database files' ],
        'application/mediaservercontrol+xml' => [ 0 => 'mscml', 1 => '.mscml', 2 => 'Media Server Control Markup Language' ],
        'application/metalink4+xml' => [ 0 => 'meta4', 1 => '.meta4', 2 => 'Metalink' ],
        'application/mets+xml' => [ 0 => 'mets', 1 => '.mets', 2 => 'Metadata Encoding and Transmission Standard' ],
        'application/mods+xml' => [ 0 => 'mods', 1 => '.mods', 2 => 'Metadata Object Description Schema' ],
        'application/mp21' => [ 0 => 'm21', 1 => '.m21', 2 => 'MPEG-21' ],
        'application/mp4' => [ 0 => 'mp4', 1 => '.mp4', 2 => 'MPEG4' ],
        'application/msword' => [ 0 => 'doc', 1 => '.doc', 2 => 'Microsoft Word' ],
        'application/mxf' => [ 0 => 'mxf', 1 => '.mxf', 2 => 'Material Exchange Format' ],
        'application/octet-stream' => [ 0 => 'bin', 1 => '.bin', 2 => 'Binary Data' ],
        'application/oda' => [ 0 => 'oda', 1 => '.oda', 2 => 'Office Document Architecture' ],
        'application/oebps-package+xml' => [ 0 => 'opf', 1 => '.opf', 2 => 'Open eBook Publication Structure' ],
        'application/ogg' => [ 0 => 'ogx', 1 => '.ogx', 2 => 'Ogg' ],
        'application/onenote' => [ 0 => 'onetoc', 1 => '.onetoc', 2 => 'Microsoft OneNote' ],
        'application/patch-ops-error+xml' => [ 0 => 'xer', 1 => '.xer', 2 => 'XML Patch Framework' ],
        'application/pdf' => [ 0 => 'pdf', 1 => '.pdf', 2 => 'Adobe Portable Document Format' ],
        'application/pgp-encrypted' => [ 0 => 'pgp', 1 => '.pgp', 2 => 'Pretty Good Privacy' ],
        'application/pgp-signature' => [ 0 => 'pgp', 1 => '.pgp', 2 => 'Pretty Good Privacy - Signature' ],
        'application/pics-rules' => [ 0 => 'prf', 1 => '.prf', 2 => 'PICSRules' ],
        'application/pkcs10' => [ 0 => 'p10', 1 => '.p10', 2 => 'PKCS #10 - Certification Request Standard' ],
        'application/pkcs7-mime' => [ 0 => 'p7m', 1 => '.p7m', 2 => 'PKCS #7 - Cryptographic Message Syntax Standard' ],
        'application/pkcs7-signature' => [ 0 => 'p7s', 1 => '.p7s', 2 => 'PKCS #7 - Cryptographic Message Syntax Standard' ],
        'application/pkcs8' => [ 0 => 'p8', 1 => '.p8', 2 => 'PKCS #8 - Private-Key Information Syntax Standard' ],
        'application/pkix-attr-cert' => [ 0 => 'ac', 1 => '.ac', 2 => 'Attribute Certificate' ],
        'application/pkix-cert' => [ 0 => 'cer', 1 => '.cer', 2 => 'Internet Public Key Infrastructure - Certificate' ],
        'application/pkix-crl' => [ 0 => 'crl', 1 => '.crl', 2 => 'Internet Public Key Infrastructure - Certificate Revocation Lists' ],
        'application/pkix-pkipath' => [ 0 => 'pkipath', 1 => '.pkipath', 2 => 'Internet Public Key Infrastructure - Certification Path' ],
        'application/pkixcmp' => [ 0 => 'pki', 1 => '.pki', 2 => 'Internet Public Key Infrastructure - Certificate Management Protocole' ],
        'application/pls+xml' => [ 0 => 'pls', 1 => '.pls', 2 => 'Pronunciation Lexicon Specification' ],
        'application/postscript' => [ 0 => 'ai', 1 => '.ai', 2 => 'PostScript' ],
        'application/prs.cww' => [ 0 => 'cww', 1 => '.cww', 2 => 'CU-Writer' ],
        'application/pskc+xml' => [ 0 => 'pskcxml', 1 => '.pskcxml', 2 => 'Portable Symmetric Key Container' ],
        'application/rdf+xml' => [ 0 => 'rdf', 1 => '.rdf', 2 => 'Resource Description Framework' ],
        'application/reginfo+xml' => [ 0 => 'rif', 1 => '.rif', 2 => 'IMS Networks' ],
        'application/relax-ng-compact-syntax' => [ 0 => 'rnc', 1 => '.rnc', 2 => 'Relax NG Compact Syntax' ],
        'application/resource-lists+xml' => [ 0 => 'rl', 1 => '.rl', 2 => 'XML Resource Lists' ],
        'application/resource-lists-diff+xml' => [ 0 => 'rld', 1 => '.rld', 2 => 'XML Resource Lists Diff' ],
        'application/rls-services+xml' => [ 0 => 'rs', 1 => '.rs', 2 => 'XML Resource Lists' ],
        'application/rsd+xml' => [ 0 => 'rsd', 1 => '.rsd', 2 => 'Really Simple Discovery' ],
        'application/rss+xml' => [ 0 => 'rss, .xml', 1 => '.rss, .xml', 2 => 'RSS - Really Simple Syndication' ],
        'application/rtf' => [ 0 => 'rtf', 1 => '.rtf', 2 => 'Rich Text Format' ],
        'application/sbml+xml' => [ 0 => 'sbml', 1 => '.sbml', 2 => 'Systems Biology Markup Language' ],
        'application/scvp-cv-request' => [ 0 => 'scq', 1 => '.scq', 2 => 'Server-Based Certificate Validation Protocol - Validation Request' ],
        'application/scvp-cv-response' => [ 0 => 'scs', 1 => '.scs', 2 => 'Server-Based Certificate Validation Protocol - Validation Response' ],
        'application/scvp-vp-request' => [ 0 => 'spq', 1 => '.spq', 2 => 'Server-Based Certificate Validation Protocol - Validation Policies - Request' ],
        'application/scvp-vp-response' => [ 0 => 'spp', 1 => '.spp', 2 => 'Server-Based Certificate Validation Protocol - Validation Policies - Response' ],
        'application/sdp' => [ 0 => 'sdp', 1 => '.sdp', 2 => 'Session Description Protocol' ],
        'application/set-payment-initiation' => [ 0 => 'setpay', 1 => '.setpay', 2 => 'Secure Electronic Transaction - Payment' ],
        'application/set-registration-initiation' => [ 0 => 'setreg', 1 => '.setreg', 2 => 'Secure Electronic Transaction - Registration' ],
        'application/shf+xml' => [ 0 => 'shf', 1 => '.shf', 2 => 'S Hexdump Format' ],
        'application/smil+xml' => [ 0 => 'smi', 1 => '.smi', 2 => 'Synchronized Multimedia Integration Language' ],
        'application/sparql-query' => [ 0 => 'rq', 1 => '.rq', 2 => 'SPARQL - Query' ],
        'application/sparql-results+xml' => [ 0 => 'srx', 1 => '.srx', 2 => 'SPARQL - Results' ],
        'application/srgs' => [ 0 => 'gram', 1 => '.gram', 2 => 'Speech Recognition Grammar Specification' ],
        'application/srgs+xml' => [ 0 => 'grxml', 1 => '.grxml', 2 => 'Speech Recognition Grammar Specification - XML' ],
        'application/sru+xml' => [ 0 => 'sru', 1 => '.sru', 2 => 'Search/Retrieve via URL Response Format' ],
        'application/ssml+xml' => [ 0 => 'ssml', 1 => '.ssml', 2 => 'Speech Synthesis Markup Language' ],
        'application/tei+xml' => [ 0 => 'tei', 1 => '.tei', 2 => 'Text Encoding and Interchange' ],
        'application/thraud+xml' => [ 0 => 'tfi', 1 => '.tfi', 2 => 'Sharing Transaction Fraud Data' ],
        'application/timestamped-data' => [ 0 => 'tsd', 1 => '.tsd', 2 => 'Time Stamped Data Envelope' ],
        'application/vnd.3gpp.pic-bw-large' => [ 0 => 'plb', 1 => '.plb', 2 => '3rd Generation Partnership Project - Pic Large' ],
        'application/vnd.3gpp.pic-bw-small' => [ 0 => 'psb', 1 => '.psb', 2 => '3rd Generation Partnership Project - Pic Small' ],
        'application/vnd.3gpp.pic-bw-var' => [ 0 => 'pvb', 1 => '.pvb', 2 => '3rd Generation Partnership Project - Pic Var' ],
        'application/vnd.3gpp2.tcap' => [ 0 => 'tcap', 1 => '.tcap', 2 => '3rd Generation Partnership Project - Transaction Capabilities Application Part' ],
        'application/vnd.3m.post-it-notes' => [ 0 => 'pwn', 1 => '.pwn', 2 => '3M Post It Notes' ],
        'application/vnd.accpac.simply.aso' => [ 0 => 'aso', 1 => '.aso', 2 => 'Simply Accounting' ],
        'application/vnd.accpac.simply.imp' => [ 0 => 'imp', 1 => '.imp', 2 => 'Simply Accounting - Data Import' ],
        'application/vnd.acucobol' => [ 0 => 'acu', 1 => '.acu', 2 => 'ACU Cobol' ],
        'application/vnd.acucorp' => [ 0 => 'atc', 1 => '.atc', 2 => 'ACU Cobol' ],
        'application/vnd.adobe.air-application-installer-package+zip' => [ 0 => 'air', 1 => '.air', 2 => 'Adobe AIR Application' ],
        'application/vnd.adobe.fxp' => [ 0 => 'fxp', 1 => '.fxp', 2 => 'Adobe Flex Project' ],
        'application/vnd.adobe.xdp+xml' => [ 0 => 'xdp', 1 => '.xdp', 2 => 'Adobe XML Data Package' ],
        'application/vnd.adobe.xfdf' => [ 0 => 'xfdf', 1 => '.xfdf', 2 => 'Adobe XML Forms Data Format' ],
        'application/vnd.ahead.space' => [ 0 => 'ahead', 1 => '.ahead', 2 => 'Ahead AIR Application' ],
        'application/vnd.airzip.filesecure.azf' => [ 0 => 'azf', 1 => '.azf', 2 => 'AirZip FileSECURE' ],
        'application/vnd.airzip.filesecure.azs' => [ 0 => 'azs', 1 => '.azs', 2 => 'AirZip FileSECURE' ],
        'application/vnd.amazon.ebook' => [ 0 => 'azw', 1 => '.azw', 2 => 'Amazon Kindle eBook format' ],
        'application/vnd.americandynamics.acc' => [ 0 => 'acc', 1 => '.acc', 2 => 'Active Content Compression' ],
        'application/vnd.amiga.ami' => [ 0 => 'ami', 1 => '.ami', 2 => 'AmigaDE' ],
        'application/vnd.android.package-archive' => [ 0 => 'apk', 1 => '.apk', 2 => 'Android Package Archive' ],
        'application/vnd.anser-web-certificate-issue-initiation' => [ 0 => 'cii', 1 => '.cii', 2 => 'ANSER-WEB Terminal Client - Certificate Issue' ],
        'application/vnd.anser-web-funds-transfer-initiation' => [ 0 => 'fti', 1 => '.fti', 2 => 'ANSER-WEB Terminal Client - Web Funds Transfer' ],
        'application/vnd.antix.game-component' => [ 0 => 'atx', 1 => '.atx', 2 => 'Antix Game Player' ],
        'application/vnd.apple.installer+xml' => [ 0 => 'mpkg', 1 => '.mpkg', 2 => 'Apple Installer Package' ],
        'application/vnd.apple.mpegurl' => [ 0 => 'm3u8', 1 => '.m3u8', 2 => 'Multimedia Playlist Unicode' ],
        'application/vnd.aristanetworks.swi' => [ 0 => 'swi', 1 => '.swi', 2 => 'Arista Networks Software Image' ],
        'application/vnd.audiograph' => [ 0 => 'aep', 1 => '.aep', 2 => 'Audiograph' ],
        'application/vnd.blueice.multipass' => [ 0 => 'mpm', 1 => '.mpm', 2 => 'Blueice Research Multipass' ],
        'application/vnd.bmi' => [ 0 => 'bmi', 1 => '.bmi', 2 => 'BMI Drawing Data Interchange' ],
        'application/vnd.businessobjects' => [ 0 => 'rep', 1 => '.rep', 2 => 'BusinessObjects' ],
        'application/vnd.chemdraw+xml' => [ 0 => 'cdxml', 1 => '.cdxml', 2 => 'CambridgeSoft Chem Draw' ],
        'application/vnd.chipnuts.karaoke-mmd' => [ 0 => 'mmd', 1 => '.mmd', 2 => 'Karaoke on Chipnuts Chipsets' ],
        'application/vnd.cinderella' => [ 0 => 'cdy', 1 => '.cdy', 2 => 'Interactive Geometry Software Cinderella' ],
        'application/vnd.claymore' => [ 0 => 'cla', 1 => '.cla', 2 => 'Claymore Data Files' ],
        'application/vnd.cloanto.rp9' => [ 0 => 'rp9', 1 => '.rp9', 2 => 'RetroPlatform Player' ],
        'application/vnd.clonk.c4group' => [ 0 => 'c4g', 1 => '.c4g', 2 => 'Clonk Game' ],
        'application/vnd.cluetrust.cartomobile-config' => [ 0 => 'c11amc', 1 => '.c11amc', 2 => 'ClueTrust CartoMobile - Config' ],
        'application/vnd.cluetrust.cartomobile-config-pkg' => [ 0 => 'c11amz', 1 => '.c11amz', 2 => 'ClueTrust CartoMobile - Config Package' ],
        'application/vnd.commonspace' => [ 0 => 'csp', 1 => '.csp', 2 => 'Sixth Floor Media - CommonSpace' ],
        'application/vnd.contact.cmsg' => [ 0 => 'cdbcmsg', 1 => '.cdbcmsg', 2 => 'CIM Database' ],
        'application/vnd.cosmocaller' => [ 0 => 'cmc', 1 => '.cmc', 2 => 'CosmoCaller' ],
        'application/vnd.crick.clicker' => [ 0 => 'clkx', 1 => '.clkx', 2 => 'CrickSoftware - Clicker' ],
        'application/vnd.crick.clicker.keyboard' => [ 0 => 'clkk', 1 => '.clkk', 2 => 'CrickSoftware - Clicker - Keyboard' ],
        'application/vnd.crick.clicker.palette' => [ 0 => 'clkp', 1 => '.clkp', 2 => 'CrickSoftware - Clicker - Palette' ],
        'application/vnd.crick.clicker.template' => [ 0 => 'clkt', 1 => '.clkt', 2 => 'CrickSoftware - Clicker - Template' ],
        'application/vnd.crick.clicker.wordbank' => [ 0 => 'clkw', 1 => '.clkw', 2 => 'CrickSoftware - Clicker - Wordbank' ],
        'application/vnd.criticaltools.wbs+xml' => [ 0 => 'wbs', 1 => '.wbs', 2 => 'Critical Tools - PERT Chart EXPERT' ],
        'application/vnd.ctc-posml' => [ 0 => 'pml', 1 => '.pml', 2 => 'PosML' ],
        'application/vnd.cups-ppd' => [ 0 => 'ppd', 1 => '.ppd', 2 => 'Adobe PostScript Printer Description File Format' ],
        'application/vnd.curl.car' => [ 0 => 'car', 1 => '.car', 2 => 'CURL Applet' ],
        'application/vnd.curl.pcurl' => [ 0 => 'pcurl', 1 => '.pcurl', 2 => 'CURL Applet' ],
        'application/vnd.data-vision.rdz' => [ 0 => 'rdz', 1 => '.rdz', 2 => 'RemoteDocs R-Viewer' ],
        'application/vnd.denovo.fcselayout-link' => [ 0 => 'fe_launch', 1 => '.fe_launch', 2 => 'FCS Express Layout Link' ],
        'application/vnd.dna' => [ 0 => 'dna', 1 => '.dna', 2 => 'New Moon Liftoff/DNA' ],
        'application/vnd.dolby.mlp' => [ 0 => 'mlp', 1 => '.mlp', 2 => 'Dolby Meridian Lossless Packing' ],
        'application/vnd.dpgraph' => [ 0 => 'dpg', 1 => '.dpg', 2 => 'DPGraph' ],
        'application/vnd.dreamfactory' => [ 0 => 'dfac', 1 => '.dfac', 2 => 'DreamFactory' ],
        'application/vnd.dvb.ait' => [ 0 => 'ait', 1 => '.ait', 2 => 'Digital Video Broadcasting' ],
        'application/vnd.dvb.service' => [ 0 => 'svc', 1 => '.svc', 2 => 'Digital Video Broadcasting' ],
        'application/vnd.dynageo' => [ 0 => 'geo', 1 => '.geo', 2 => 'DynaGeo' ],
        'application/vnd.ecowin.chart' => [ 0 => 'mag', 1 => '.mag', 2 => 'EcoWin Chart' ],
        'application/vnd.enliven' => [ 0 => 'nml', 1 => '.nml', 2 => 'Enliven Viewer' ],
        'application/vnd.epson.esf' => [ 0 => 'esf', 1 => '.esf', 2 => 'QUASS Stream Player' ],
        'application/vnd.epson.msf' => [ 0 => 'msf', 1 => '.msf', 2 => 'QUASS Stream Player' ],
        'application/vnd.epson.quickanime' => [ 0 => 'qam', 1 => '.qam', 2 => 'QuickAnime Player' ],
        'application/vnd.epson.salt' => [ 0 => 'slt', 1 => '.slt', 2 => 'SimpleAnimeLite Player' ],
        'application/vnd.epson.ssf' => [ 0 => 'ssf', 1 => '.ssf', 2 => 'QUASS Stream Player' ],
        'application/vnd.eszigno3+xml' => [ 0 => 'es3', 1 => '.es3', 2 => 'MICROSEC e-SzignÂ¢' ],
        'application/vnd.ezpix-album' => [ 0 => 'ez2', 1 => '.ez2', 2 => 'EZPix Secure Photo Album' ],
        'application/vnd.ezpix-package' => [ 0 => 'ez3', 1 => '.ez3', 2 => 'EZPix Secure Photo Album' ],
        'application/vnd.fdf' => [ 0 => 'fdf', 1 => '.fdf', 2 => 'Forms Data Format' ],
        'application/vnd.fdsn.seed' => [ 0 => 'seed', 1 => '.seed', 2 => 'Digital Siesmograph Networks - SEED Datafiles' ],
        'application/vnd.flographit' => [ 0 => 'gph', 1 => '.gph', 2 => 'NpGraphIt' ],
        'application/vnd.fluxtime.clip' => [ 0 => 'ftc', 1 => '.ftc', 2 => 'FluxTime Clip' ],
        'application/vnd.framemaker' => [ 0 => 'fm', 1 => '.fm', 2 => 'FrameMaker Normal Format' ],
        'application/vnd.frogans.fnc' => [ 0 => 'fnc', 1 => '.fnc', 2 => 'Frogans Player' ],
        'application/vnd.frogans.ltf' => [ 0 => 'ltf', 1 => '.ltf', 2 => 'Frogans Player' ],
        'application/vnd.fsc.weblaunch' => [ 0 => 'fsc', 1 => '.fsc', 2 => 'Friendly Software Corporation' ],
        'application/vnd.fujitsu.oasys' => [ 0 => 'oas', 1 => '.oas', 2 => 'Fujitsu Oasys' ],
        'application/vnd.fujitsu.oasys2' => [ 0 => 'oa2', 1 => '.oa2', 2 => 'Fujitsu Oasys' ],
        'application/vnd.fujitsu.oasys3' => [ 0 => 'oa3', 1 => '.oa3', 2 => 'Fujitsu Oasys' ],
        'application/vnd.fujitsu.oasysgp' => [ 0 => 'fg5', 1 => '.fg5', 2 => 'Fujitsu Oasys' ],
        'application/vnd.fujitsu.oasysprs' => [ 0 => 'bh2', 1 => '.bh2', 2 => 'Fujitsu Oasys' ],
        'application/vnd.fujixerox.ddd' => [ 0 => 'ddd', 1 => '.ddd', 2 => 'Fujitsu - Xerox 2D CAD Data' ],
        'application/vnd.fujixerox.docuworks' => [ 0 => 'xdw', 1 => '.xdw', 2 => 'Fujitsu - Xerox DocuWorks' ],
        'application/vnd.fujixerox.docuworks.binder' => [ 0 => 'xbd', 1 => '.xbd', 2 => 'Fujitsu - Xerox DocuWorks Binder' ],
        'application/vnd.fuzzysheet' => [ 0 => 'fzs', 1 => '.fzs', 2 => 'FuzzySheet' ],
        'application/vnd.genomatix.tuxedo' => [ 0 => 'txd', 1 => '.txd', 2 => 'Genomatix Tuxedo Framework' ],
        'application/vnd.geogebra.file' => [ 0 => 'ggb', 1 => '.ggb', 2 => 'GeoGebra' ],
        'application/vnd.geogebra.tool' => [ 0 => 'ggt', 1 => '.ggt', 2 => 'GeoGebra' ],
        'application/vnd.geometry-explorer' => [ 0 => 'gex', 1 => '.gex', 2 => 'GeoMetry Explorer' ],
        'application/vnd.geonext' => [ 0 => 'gxt', 1 => '.gxt', 2 => 'GEONExT and JSXGraph' ],
        'application/vnd.geoplan' => [ 0 => 'g2w', 1 => '.g2w', 2 => 'GeoplanW' ],
        'application/vnd.geospace' => [ 0 => 'g3w', 1 => '.g3w', 2 => 'GeospacW' ],
        'application/vnd.gmx' => [ 0 => 'gmx', 1 => '.gmx', 2 => 'GameMaker ActiveX' ],
        'application/vnd.google-earth.kml+xml' => [ 0 => 'kml', 1 => '.kml', 2 => 'Google Earth - KML' ],
        'application/vnd.google-earth.kmz' => [ 0 => 'kmz', 1 => '.kmz', 2 => 'Google Earth - Zipped KML' ],
        'application/vnd.grafeq' => [ 0 => 'gqf', 1 => '.gqf', 2 => 'GrafEq' ],
        'application/vnd.groove-account' => [ 0 => 'gac', 1 => '.gac', 2 => 'Groove - Account' ],
        'application/vnd.groove-help' => [ 0 => 'ghf', 1 => '.ghf', 2 => 'Groove - Help' ],
        'application/vnd.groove-identity-message' => [ 0 => 'gim', 1 => '.gim', 2 => 'Groove - Identity Message' ],
        'application/vnd.groove-injector' => [ 0 => 'grv', 1 => '.grv', 2 => 'Groove - Injector' ],
        'application/vnd.groove-tool-message' => [ 0 => 'gtm', 1 => '.gtm', 2 => 'Groove - Tool Message' ],
        'application/vnd.groove-tool-template' => [ 0 => 'tpl', 1 => '.tpl', 2 => 'Groove - Tool Template' ],
        'application/vnd.groove-vcard' => [ 0 => 'vcg', 1 => '.vcg', 2 => 'Groove - Vcard' ],
        'application/vnd.hal+xml' => [ 0 => 'hal', 1 => '.hal', 2 => 'Hypertext Application Language' ],
        'application/vnd.handheld-entertainment+xml' => [ 0 => 'zmm', 1 => '.zmm', 2 => 'ZVUE Media Manager' ],
        'application/vnd.hbci' => [ 0 => 'hbci', 1 => '.hbci', 2 => 'Homebanking Computer Interface (HBCI)' ],
        'application/vnd.hhe.lesson-player' => [ 0 => 'les', 1 => '.les', 2 => 'Archipelago Lesson Player' ],
        'application/vnd.hp-hpgl' => [ 0 => 'hpgl', 1 => '.hpgl', 2 => 'HP-GL/2 and HP RTL' ],
        'application/vnd.hp-hpid' => [ 0 => 'hpid', 1 => '.hpid', 2 => 'Hewlett Packard Instant Delivery' ],
        'application/vnd.hp-hps' => [ 0 => 'hps', 1 => '.hps', 2 => 'Hewlett-Packard\'s WebPrintSmart' ],
        'application/vnd.hp-jlyt' => [ 0 => 'jlt', 1 => '.jlt', 2 => 'HP Indigo Digital Press - Job Layout Languate' ],
        'application/vnd.hp-pcl' => [ 0 => 'pcl', 1 => '.pcl', 2 => 'HP Printer Command Language' ],
        'application/vnd.hp-pclxl' => [ 0 => 'pclxl', 1 => '.pclxl', 2 => 'PCL 6 Enhanced (Formely PCL XL)' ],
        'application/vnd.hydrostatix.sof-data' => [ 0 => 'sfd-hdstx', 1 => '.sfd-hdstx', 2 => 'Hydrostatix Master Suite' ],
        'application/vnd.hzn-3d-crossword' => [ 0 => 'x3d', 1 => '.x3d', 2 => '3D Crossword Plugin' ],
        'application/vnd.ibm.minipay' => [ 0 => 'mpy', 1 => '.mpy', 2 => 'MiniPay' ],
        'application/vnd.ibm.modcap' => [ 0 => 'afp', 1 => '.afp', 2 => 'MO:DCA-P' ],
        'application/vnd.ibm.rights-management' => [ 0 => 'irm', 1 => '.irm', 2 => 'IBM DB2 Rights Manager' ],
        'application/vnd.ibm.secure-container' => [ 0 => 'sc', 1 => '.sc', 2 => 'IBM Electronic Media Management System - Secure Container' ],
        'application/vnd.iccprofile' => [ 0 => 'icc', 1 => '.icc', 2 => 'ICC profile' ],
        'application/vnd.igloader' => [ 0 => 'igl', 1 => '.igl', 2 => 'igLoader' ],
        'application/vnd.immervision-ivp' => [ 0 => 'ivp', 1 => '.ivp', 2 => 'ImmerVision PURE Players' ],
        'application/vnd.immervision-ivu' => [ 0 => 'ivu', 1 => '.ivu', 2 => 'ImmerVision PURE Players' ],
        'application/vnd.insors.igm' => [ 0 => 'igm', 1 => '.igm', 2 => 'IOCOM Visimeet' ],
        'application/vnd.intercon.formnet' => [ 0 => 'xpw', 1 => '.xpw', 2 => 'Intercon FormNet' ],
        'application/vnd.intergeo' => [ 0 => 'i2g', 1 => '.i2g', 2 => 'Interactive Geometry Software' ],
        'application/vnd.intu.qbo' => [ 0 => 'qbo', 1 => '.qbo', 2 => 'Open Financial Exchange' ],
        'application/vnd.intu.qfx' => [ 0 => 'qfx', 1 => '.qfx', 2 => 'Quicken' ],
        'application/vnd.ipunplugged.rcprofile' => [ 0 => 'rcprofile', 1 => '.rcprofile', 2 => 'IP Unplugged Roaming Client' ],
        'application/vnd.irepository.package+xml' => [ 0 => 'irp', 1 => '.irp', 2 => 'iRepository / Lucidoc Editor' ],
        'application/vnd.is-xpr' => [ 0 => 'xpr', 1 => '.xpr', 2 => 'Express by Infoseek' ],
        'application/vnd.isac.fcs' => [ 0 => 'fcs', 1 => '.fcs', 2 => 'International Society for Advancement of Cytometry' ],
        'application/vnd.jam' => [ 0 => 'jam', 1 => '.jam', 2 => 'Lightspeed Audio Lab' ],
        'application/vnd.jcp.javame.midlet-rms' => [ 0 => 'rms', 1 => '.rms', 2 => 'Mobile Information Device Profile' ],
        'application/vnd.jisp' => [ 0 => 'jisp', 1 => '.jisp', 2 => 'RhymBox' ],
        'application/vnd.joost.joda-archive' => [ 0 => 'joda', 1 => '.joda', 2 => 'Joda Archive' ],
        'application/vnd.kahootz' => [ 0 => 'ktz', 1 => '.ktz', 2 => 'Kahootz' ],
        'application/vnd.kde.karbon' => [ 0 => 'karbon', 1 => '.karbon', 2 => 'KDE KOffice Office Suite - Karbon' ],
        'application/vnd.kde.kchart' => [ 0 => 'chrt', 1 => '.chrt', 2 => 'KDE KOffice Office Suite - KChart' ],
        'application/vnd.kde.kformula' => [ 0 => 'kfo', 1 => '.kfo', 2 => 'KDE KOffice Office Suite - Kformula' ],
        'application/vnd.kde.kivio' => [ 0 => 'flw', 1 => '.flw', 2 => 'KDE KOffice Office Suite - Kivio' ],
        'application/vnd.kde.kontour' => [ 0 => 'kon', 1 => '.kon', 2 => 'KDE KOffice Office Suite - Kontour' ],
        'application/vnd.kde.kpresenter' => [ 0 => 'kpr', 1 => '.kpr', 2 => 'KDE KOffice Office Suite - Kpresenter' ],
        'application/vnd.kde.kspread' => [ 0 => 'ksp', 1 => '.ksp', 2 => 'KDE KOffice Office Suite - Kspread' ],
        'application/vnd.kde.kword' => [ 0 => 'kwd', 1 => '.kwd', 2 => 'KDE KOffice Office Suite - Kword' ],
        'application/vnd.kenameaapp' => [ 0 => 'htke', 1 => '.htke', 2 => 'Kenamea App' ],
        'application/vnd.kidspiration' => [ 0 => 'kia', 1 => '.kia', 2 => 'Kidspiration' ],
        'application/vnd.kinar' => [ 0 => 'kne', 1 => '.kne', 2 => 'Kinar Applications' ],
        'application/vnd.koan' => [ 0 => 'skp', 1 => '.skp', 2 => 'SSEYO Koan Play File' ],
        'application/vnd.kodak-descriptor' => [ 0 => 'sse', 1 => '.sse', 2 => 'Kodak Storyshare' ],
        'application/vnd.las.las+xml' => [ 0 => 'lasxml', 1 => '.lasxml', 2 => 'Laser App Enterprise' ],
        'application/vnd.llamagraphics.life-balance.desktop' => [ 0 => 'lbd', 1 => '.lbd', 2 => 'Life Balance - Desktop Edition' ],
        'application/vnd.llamagraphics.life-balance.exchange+xml' => [ 0 => 'lbe', 1 => '.lbe', 2 => 'Life Balance - Exchange Format' ],
        'application/vnd.lotus-1-2-3' => [ 0 => '123', 1 => '.123', 2 => 'Lotus 1-2-3' ],
        'application/vnd.lotus-approach' => [ 0 => 'apr', 1 => '.apr', 2 => 'Lotus Approach' ],
        'application/vnd.lotus-freelance' => [ 0 => 'pre', 1 => '.pre', 2 => 'Lotus Freelance' ],
        'application/vnd.lotus-notes' => [ 0 => 'nsf', 1 => '.nsf', 2 => 'Lotus Notes' ],
        'application/vnd.lotus-organizer' => [ 0 => 'org', 1 => '.org', 2 => 'Lotus Organizer' ],
        'application/vnd.lotus-screencam' => [ 0 => 'scm', 1 => '.scm', 2 => 'Lotus Screencam' ],
        'application/vnd.lotus-wordpro' => [ 0 => 'lwp', 1 => '.lwp', 2 => 'Lotus Wordpro' ],
        'application/vnd.macports.portpkg' => [ 0 => 'portpkg', 1 => '.portpkg', 2 => 'MacPorts Port System' ],
        'application/vnd.mcd' => [ 0 => 'mcd', 1 => '.mcd', 2 => 'Micro CADAM Helix D&D' ],
        'application/vnd.medcalcdata' => [ 0 => 'mc1', 1 => '.mc1', 2 => 'MedCalc' ],
        'application/vnd.mediastation.cdkey' => [ 0 => 'cdkey', 1 => '.cdkey', 2 => 'MediaRemote' ],
        'application/vnd.mfer' => [ 0 => 'mwf', 1 => '.mwf', 2 => 'Medical Waveform Encoding Format' ],
        'application/vnd.mfmp' => [ 0 => 'mfm', 1 => '.mfm', 2 => 'Melody Format for Mobile Platform' ],
        'application/vnd.micrografx.flo' => [ 0 => 'flo', 1 => '.flo', 2 => 'Micrografx' ],
        'application/vnd.micrografx.igx' => [ 0 => 'igx', 1 => '.igx', 2 => 'Micrografx iGrafx Professional' ],
        'application/vnd.mif' => [ 0 => 'mif', 1 => '.mif', 2 => 'FrameMaker Interchange Format' ],
        'application/vnd.mobius.daf' => [ 0 => 'daf', 1 => '.daf', 2 => 'Mobius Management Systems - UniversalArchive' ],
        'application/vnd.mobius.dis' => [ 0 => 'dis', 1 => '.dis', 2 => 'Mobius Management Systems - Distribution Database' ],
        'application/vnd.mobius.mbk' => [ 0 => 'mbk', 1 => '.mbk', 2 => 'Mobius Management Systems - Basket file' ],
        'application/vnd.mobius.mqy' => [ 0 => 'mqy', 1 => '.mqy', 2 => 'Mobius Management Systems - Query File' ],
        'application/vnd.mobius.msl' => [ 0 => 'msl', 1 => '.msl', 2 => 'Mobius Management Systems - Script Language' ],
        'application/vnd.mobius.plc' => [ 0 => 'plc', 1 => '.plc', 2 => 'Mobius Management Systems - Policy Definition Language File' ],
        'application/vnd.mobius.txf' => [ 0 => 'txf', 1 => '.txf', 2 => 'Mobius Management Systems - Topic Index File' ],
        'application/vnd.mophun.application' => [ 0 => 'mpn', 1 => '.mpn', 2 => 'Mophun VM' ],
        'application/vnd.mophun.certificate' => [ 0 => 'mpc', 1 => '.mpc', 2 => 'Mophun Certificate' ],
        'application/vnd.mozilla.xul+xml' => [ 0 => 'xul', 1 => '.xul', 2 => 'XUL - XML User Interface Language' ],
        'application/vnd.ms-artgalry' => [ 0 => 'cil', 1 => '.cil', 2 => 'Microsoft Artgalry' ],
        'application/vnd.ms-cab-compressed' => [ 0 => 'cab', 1 => '.cab', 2 => 'Microsoft Cabinet File' ],
        'application/vnd.ms-excel' => [ 0 => 'xls', 1 => '.xls', 2 => 'Microsoft Excel' ],
        'application/vnd.ms-excel.addin.macroenabled.12' => [ 0 => 'xlam', 1 => '.xlam', 2 => 'Microsoft Excel - Add-In File' ],
        'application/vnd.ms-excel.sheet.binary.macroenabled.12' => [ 0 => 'xlsb', 1 => '.xlsb', 2 => 'Microsoft Excel - Binary Workbook' ],
        'application/vnd.ms-excel.sheet.macroenabled.12' => [ 0 => 'xlsm', 1 => '.xlsm', 2 => 'Microsoft Excel - Macro-Enabled Workbook' ],
        'application/vnd.ms-excel.template.macroenabled.12' => [ 0 => 'xltm', 1 => '.xltm', 2 => 'Microsoft Excel - Macro-Enabled Template File' ],
        'application/vnd.ms-fontobject' => [ 0 => 'eot', 1 => '.eot', 2 => 'Microsoft Embedded OpenType' ],
        'application/vnd.ms-htmlhelp' => [ 0 => 'chm', 1 => '.chm', 2 => 'Microsoft Html Help File' ],
        'application/vnd.ms-ims' => [ 0 => 'ims', 1 => '.ims', 2 => 'Microsoft Class Server' ],
        'application/vnd.ms-lrm' => [ 0 => 'lrm', 1 => '.lrm', 2 => 'Microsoft Learning Resource Module' ],
        'application/vnd.ms-officetheme' => [ 0 => 'thmx', 1 => '.thmx', 2 => 'Microsoft Office System Release Theme' ],
        'application/vnd.ms-pki.seccat' => [ 0 => 'cat', 1 => '.cat', 2 => 'Microsoft Trust UI Provider - Security Catalog' ],
        'application/vnd.ms-pki.stl' => [ 0 => 'stl', 1 => '.stl', 2 => 'Microsoft Trust UI Provider - Certificate Trust Link' ],
        'application/vnd.ms-powerpoint' => [ 0 => 'ppt', 1 => '.ppt', 2 => 'Microsoft PowerPoint' ],
        'application/vnd.ms-powerpoint.addin.macroenabled.12' => [ 0 => 'ppam', 1 => '.ppam', 2 => 'Microsoft PowerPoint - Add-in file' ],
        'application/vnd.ms-powerpoint.presentation.macroenabled.12' => [ 0 => 'pptm', 1 => '.pptm', 2 => 'Microsoft PowerPoint - Macro-Enabled Presentation File' ],
        'application/vnd.ms-powerpoint.slide.macroenabled.12' => [ 0 => 'sldm', 1 => '.sldm', 2 => 'Microsoft PowerPoint - Macro-Enabled Open XML Slide' ],
        'application/vnd.ms-powerpoint.slideshow.macroenabled.12' => [ 0 => 'ppsm', 1 => '.ppsm', 2 => 'Microsoft PowerPoint - Macro-Enabled Slide Show File' ],
        'application/vnd.ms-powerpoint.template.macroenabled.12' => [ 0 => 'potm', 1 => '.potm', 2 => 'Microsoft PowerPoint - Macro-Enabled Template File' ],
        'application/vnd.ms-project' => [ 0 => 'mpp', 1 => '.mpp', 2 => 'Microsoft Project' ],
        'application/vnd.ms-word.document.macroenabled.12' => [ 0 => 'docm', 1 => '.docm', 2 => 'Microsoft Word - Macro-Enabled Document' ],
        'application/vnd.ms-word.template.macroenabled.12' => [ 0 => 'dotm', 1 => '.dotm', 2 => 'Microsoft Word - Macro-Enabled Template' ],
        'application/vnd.ms-works' => [ 0 => 'wps', 1 => '.wps', 2 => 'Microsoft Works' ],
        'application/vnd.ms-wpl' => [ 0 => 'wpl', 1 => '.wpl', 2 => 'Microsoft Windows Media Player Playlist' ],
        'application/vnd.ms-xpsdocument' => [ 0 => 'xps', 1 => '.xps', 2 => 'Microsoft XML Paper Specification' ],
        'application/vnd.mseq' => [ 0 => 'mseq', 1 => '.mseq', 2 => '3GPP MSEQ File' ],
        'application/vnd.musician' => [ 0 => 'mus', 1 => '.mus', 2 => 'MUsical Score Interpreted Code Invented  for the ASCII designation of Notation' ],
        'application/vnd.muvee.style' => [ 0 => 'msty', 1 => '.msty', 2 => 'Muvee Automatic Video Editing' ],
        'application/vnd.neurolanguage.nlu' => [ 0 => 'nlu', 1 => '.nlu', 2 => 'neuroLanguage' ],
        'application/vnd.noblenet-directory' => [ 0 => 'nnd', 1 => '.nnd', 2 => 'NobleNet Directory' ],
        'application/vnd.noblenet-sealer' => [ 0 => 'nns', 1 => '.nns', 2 => 'NobleNet Sealer' ],
        'application/vnd.noblenet-web' => [ 0 => 'nnw', 1 => '.nnw', 2 => 'NobleNet Web' ],
        'application/vnd.nokia.n-gage.data' => [ 0 => 'ngdat', 1 => '.ngdat', 2 => 'N-Gage Game Data' ],
        'application/vnd.nokia.n-gage.symbian.install' => [ 0 => 'n-gage', 1 => '.n-gage', 2 => 'N-Gage Game Installer' ],
        'application/vnd.nokia.radio-preset' => [ 0 => 'rpst', 1 => '.rpst', 2 => 'Nokia Radio Application - Preset' ],
        'application/vnd.nokia.radio-presets' => [ 0 => 'rpss', 1 => '.rpss', 2 => 'Nokia Radio Application - Preset' ],
        'application/vnd.novadigm.edm' => [ 0 => 'edm', 1 => '.edm', 2 => 'Novadigm\'s RADIA and EDM products' ],
        'application/vnd.novadigm.edx' => [ 0 => 'edx', 1 => '.edx', 2 => 'Novadigm\'s RADIA and EDM products' ],
        'application/vnd.novadigm.ext' => [ 0 => 'ext', 1 => '.ext', 2 => 'Novadigm\'s RADIA and EDM products' ],
        'application/vnd.oasis.opendocument.chart' => [ 0 => 'odc', 1 => '.odc', 2 => 'OpenDocument Chart' ],
        'application/vnd.oasis.opendocument.chart-template' => [ 0 => 'otc', 1 => '.otc', 2 => 'OpenDocument Chart Template' ],
        'application/vnd.oasis.opendocument.database' => [ 0 => 'odb', 1 => '.odb', 2 => 'OpenDocument Database' ],
        'application/vnd.oasis.opendocument.formula' => [ 0 => 'odf', 1 => '.odf', 2 => 'OpenDocument Formula' ],
        'application/vnd.oasis.opendocument.formula-template' => [ 0 => 'odft', 1 => '.odft', 2 => 'OpenDocument Formula Template' ],
        'application/vnd.oasis.opendocument.graphics' => [ 0 => 'odg', 1 => '.odg', 2 => 'OpenDocument Graphics' ],
        'application/vnd.oasis.opendocument.graphics-template' => [ 0 => 'otg', 1 => '.otg', 2 => 'OpenDocument Graphics Template' ],
        'application/vnd.oasis.opendocument.image' => [ 0 => 'odi', 1 => '.odi', 2 => 'OpenDocument Image' ],
        'application/vnd.oasis.opendocument.image-template' => [ 0 => 'oti', 1 => '.oti', 2 => 'OpenDocument Image Template' ],
        'application/vnd.oasis.opendocument.presentation' => [ 0 => 'odp', 1 => '.odp', 2 => 'OpenDocument Presentation' ],
        'application/vnd.oasis.opendocument.presentation-template' => [ 0 => 'otp', 1 => '.otp', 2 => 'OpenDocument Presentation Template' ],
        'application/vnd.oasis.opendocument.spreadsheet' => [ 0 => 'ods', 1 => '.ods', 2 => 'OpenDocument Spreadsheet' ],
        'application/vnd.oasis.opendocument.spreadsheet-template' => [ 0 => 'ots', 1 => '.ots', 2 => 'OpenDocument Spreadsheet Template' ],
        'application/vnd.oasis.opendocument.text' => [ 0 => 'odt', 1 => '.odt', 2 => 'OpenDocument Text' ],
        'application/vnd.oasis.opendocument.text-master' => [ 0 => 'odm', 1 => '.odm', 2 => 'OpenDocument Text Master' ],
        'application/vnd.oasis.opendocument.text-template' => [ 0 => 'ott', 1 => '.ott', 2 => 'OpenDocument Text Template' ],
        'application/vnd.oasis.opendocument.text-web' => [ 0 => 'oth', 1 => '.oth', 2 => 'Open Document Text Web' ],
        'application/vnd.olpc-sugar' => [ 0 => 'xo', 1 => '.xo', 2 => 'Sugar Linux Application Bundle' ],
        'application/vnd.oma.dd2+xml' => [ 0 => 'dd2', 1 => '.dd2', 2 => 'OMA Download Agents' ],
        'application/vnd.openofficeorg.extension' => [ 0 => 'oxt', 1 => '.oxt', 2 => 'Open Office Extension' ],
        'application/vnd.openxmlformats-officedocument.presentationml.presentation' => [ 0 => 'pptx', 1 => '.pptx', 2 => 'Microsoft Office - OOXML - Presentation' ],
        'application/vnd.openxmlformats-officedocument.presentationml.slide' => [ 0 => 'sldx', 1 => '.sldx', 2 => 'Microsoft Office - OOXML - Presentation (Slide)' ],
        'application/vnd.openxmlformats-officedocument.presentationml.slideshow' => [ 0 => 'ppsx', 1 => '.ppsx', 2 => 'Microsoft Office - OOXML - Presentation (Slideshow)' ],
        'application/vnd.openxmlformats-officedocument.presentationml.template' => [ 0 => 'potx', 1 => '.potx', 2 => 'Microsoft Office - OOXML - Presentation Template' ],
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => [ 0 => 'xlsx', 1 => '.xlsx', 2 => 'Microsoft Office - OOXML - Spreadsheet' ],
        'application/vnd.openxmlformats-officedocument.spreadsheetml.template' => [ 0 => 'xltx', 1 => '.xltx', 2 => 'Microsoft Office - OOXML - Spreadsheet Template' ],
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => [ 0 => 'docx', 1 => '.docx', 2 => 'Microsoft Office - OOXML - Word Document' ],
        'application/vnd.openxmlformats-officedocument.wordprocessingml.template' => [ 0 => 'dotx', 1 => '.dotx', 2 => 'Microsoft Office - OOXML - Word Document Template' ],
        'application/vnd.osgeo.mapguide.package' => [ 0 => 'mgp', 1 => '.mgp', 2 => 'MapGuide DBXML' ],
        'application/vnd.osgi.dp' => [ 0 => 'dp', 1 => '.dp', 2 => 'OSGi Deployment Package' ],
        'application/vnd.palm' => [ 0 => 'pdb', 1 => '.pdb', 2 => 'PalmOS Data' ],
        'application/vnd.pawaafile' => [ 0 => 'paw', 1 => '.paw', 2 => 'PawaaFILE' ],
        'application/vnd.pg.format' => [ 0 => 'str', 1 => '.str', 2 => 'Proprietary P&G Standard Reporting System' ],
        'application/vnd.pg.osasli' => [ 0 => 'ei6', 1 => '.ei6', 2 => 'Proprietary P&G Standard Reporting System' ],
        'application/vnd.picsel' => [ 0 => 'efif', 1 => '.efif', 2 => 'Pcsel eFIF File' ],
        'application/vnd.pmi.widget' => [ 0 => 'wg', 1 => '.wg', 2 => 'Qualcomm\'s Plaza Mobile Internet' ],
        'application/vnd.pocketlearn' => [ 0 => 'plf', 1 => '.plf', 2 => 'PocketLearn Viewers' ],
        'application/vnd.powerbuilder6' => [ 0 => 'pbd', 1 => '.pbd', 2 => 'PowerBuilder' ],
        'application/vnd.previewsystems.box' => [ 0 => 'box', 1 => '.box', 2 => 'Preview Systems ZipLock/VBox' ],
        'application/vnd.proteus.magazine' => [ 0 => 'mgz', 1 => '.mgz', 2 => 'EFI Proteus' ],
        'application/vnd.publishare-delta-tree' => [ 0 => 'qps', 1 => '.qps', 2 => 'PubliShare Objects' ],
        'application/vnd.pvi.ptid1' => [ 0 => 'ptid', 1 => '.ptid', 2 => 'Princeton Video Image' ],
        'application/vnd.quark.quarkxpress' => [ 0 => 'qxd', 1 => '.qxd', 2 => 'QuarkXpress' ],
        'application/vnd.realvnc.bed' => [ 0 => 'bed', 1 => '.bed', 2 => 'RealVNC' ],
        'application/vnd.recordare.musicxml' => [ 0 => 'mxl', 1 => '.mxl', 2 => 'Recordare Applications' ],
        'application/vnd.recordare.musicxml+xml' => [ 0 => 'musicxml', 1 => '.musicxml', 2 => 'Recordare Applications' ],
        'application/vnd.rig.cryptonote' => [ 0 => 'cryptonote', 1 => '.cryptonote', 2 => 'CryptoNote' ],
        'application/vnd.rim.cod' => [ 0 => 'cod', 1 => '.cod', 2 => 'Blackberry COD File' ],
        'application/vnd.rn-realmedia' => [ 0 => 'rm', 1 => '.rm', 2 => 'RealMedia' ],
        'application/vnd.route66.link66+xml' => [ 0 => 'link66', 1 => '.link66', 2 => 'ROUTE 66 Location Based Services' ],
        'application/vnd.sailingtracker.track' => [ 0 => 'st', 1 => '.st', 2 => 'SailingTracker' ],
        'application/vnd.seemail' => [ 0 => 'see', 1 => '.see', 2 => 'SeeMail' ],
        'application/vnd.sema' => [ 0 => 'sema', 1 => '.sema', 2 => 'Secured eMail' ],
        'application/vnd.semd' => [ 0 => 'semd', 1 => '.semd', 2 => 'Secured eMail' ],
        'application/vnd.semf' => [ 0 => 'semf', 1 => '.semf', 2 => 'Secured eMail' ],
        'application/vnd.shana.informed.formdata' => [ 0 => 'ifm', 1 => '.ifm', 2 => 'Shana Informed Filler' ],
        'application/vnd.shana.informed.formtemplate' => [ 0 => 'itp', 1 => '.itp', 2 => 'Shana Informed Filler' ],
        'application/vnd.shana.informed.interchange' => [ 0 => 'iif', 1 => '.iif', 2 => 'Shana Informed Filler' ],
        'application/vnd.shana.informed.package' => [ 0 => 'ipk', 1 => '.ipk', 2 => 'Shana Informed Filler' ],
        'application/vnd.simtech-mindmapper' => [ 0 => 'twd', 1 => '.twd', 2 => 'SimTech MindMapper' ],
        'application/vnd.smaf' => [ 0 => 'mmf', 1 => '.mmf', 2 => 'SMAF File' ],
        'application/vnd.smart.teacher' => [ 0 => 'teacher', 1 => '.teacher', 2 => 'SMART Technologies Apps' ],
        'application/vnd.solent.sdkm+xml' => [ 0 => 'sdkm', 1 => '.sdkm', 2 => 'SudokuMagic' ],
        'application/vnd.spotfire.dxp' => [ 0 => 'dxp', 1 => '.dxp', 2 => 'TIBCO Spotfire' ],
        'application/vnd.spotfire.sfs' => [ 0 => 'sfs', 1 => '.sfs', 2 => 'TIBCO Spotfire' ],
        'application/vnd.stardivision.calc' => [ 0 => 'sdc', 1 => '.sdc', 2 => 'StarOffice - Calc' ],
        'application/vnd.stardivision.draw' => [ 0 => 'sda', 1 => '.sda', 2 => 'StarOffice - Draw' ],
        'application/vnd.stardivision.impress' => [ 0 => 'sdd', 1 => '.sdd', 2 => 'StarOffice - Impress' ],
        'application/vnd.stardivision.math' => [ 0 => 'smf', 1 => '.smf', 2 => 'StarOffice - Math' ],
        'application/vnd.stardivision.writer' => [ 0 => 'sdw', 1 => '.sdw', 2 => 'StarOffice - Writer' ],
        'application/vnd.stardivision.writer-global' => [ 0 => 'sgl', 1 => '.sgl', 2 => 'StarOffice - Writer  (Global)' ],
        'application/vnd.stepmania.stepchart' => [ 0 => 'sm', 1 => '.sm', 2 => 'StepMania' ],
        'application/vnd.sun.xml.calc' => [ 0 => 'sxc', 1 => '.sxc', 2 => 'OpenOffice - Calc (Spreadsheet)' ],
        'application/vnd.sun.xml.calc.template' => [ 0 => 'stc', 1 => '.stc', 2 => 'OpenOffice - Calc Template (Spreadsheet)' ],
        'application/vnd.sun.xml.draw' => [ 0 => 'sxd', 1 => '.sxd', 2 => 'OpenOffice - Draw (Graphics)' ],
        'application/vnd.sun.xml.draw.template' => [ 0 => 'std', 1 => '.std', 2 => 'OpenOffice - Draw Template (Graphics)' ],
        'application/vnd.sun.xml.impress' => [ 0 => 'sxi', 1 => '.sxi', 2 => 'OpenOffice - Impress (Presentation)' ],
        'application/vnd.sun.xml.impress.template' => [ 0 => 'sti', 1 => '.sti', 2 => 'OpenOffice - Impress Template (Presentation)' ],
        'application/vnd.sun.xml.math' => [ 0 => 'sxm', 1 => '.sxm', 2 => 'OpenOffice - Math (Formula)' ],
        'application/vnd.sun.xml.writer' => [ 0 => 'sxw', 1 => '.sxw', 2 => 'OpenOffice - Writer (Text - HTML)' ],
        'application/vnd.sun.xml.writer.global' => [ 0 => 'sxg', 1 => '.sxg', 2 => 'OpenOffice - Writer (Text - HTML)' ],
        'application/vnd.sun.xml.writer.template' => [ 0 => 'stw', 1 => '.stw', 2 => 'OpenOffice - Writer Template (Text - HTML)' ],
        'application/vnd.sus-calendar' => [ 0 => 'sus', 1 => '.sus', 2 => 'ScheduleUs' ],
        'application/vnd.svd' => [ 0 => 'svd', 1 => '.svd', 2 => 'SourceView Document' ],
        'application/vnd.symbian.install' => [ 0 => 'sis', 1 => '.sis', 2 => 'Symbian Install Package' ],
        'application/vnd.syncml+xml' => [ 0 => 'xsm', 1 => '.xsm', 2 => 'SyncML' ],
        'application/vnd.syncml.dm+wbxml' => [ 0 => 'bdm', 1 => '.bdm', 2 => 'SyncML - Device Management' ],
        'application/vnd.syncml.dm+xml' => [ 0 => 'xdm', 1 => '.xdm', 2 => 'SyncML - Device Management' ],
        'application/vnd.tao.intent-module-archive' => [ 0 => 'tao', 1 => '.tao', 2 => 'Tao Intent' ],
        'application/vnd.tmobile-livetv' => [ 0 => 'tmo', 1 => '.tmo', 2 => 'MobileTV' ],
        'application/vnd.trid.tpt' => [ 0 => 'tpt', 1 => '.tpt', 2 => 'TRI Systems Config' ],
        'application/vnd.triscape.mxs' => [ 0 => 'mxs', 1 => '.mxs', 2 => 'Triscape Map Explorer' ],
        'application/vnd.trueapp' => [ 0 => 'tra', 1 => '.tra', 2 => 'True BASIC' ],
        'application/vnd.ufdl' => [ 0 => 'ufd', 1 => '.ufd', 2 => 'Universal Forms Description Language' ],
        'application/vnd.uiq.theme' => [ 0 => 'utz', 1 => '.utz', 2 => 'User Interface Quartz - Theme (Symbian)' ],
        'application/vnd.umajin' => [ 0 => 'umj', 1 => '.umj', 2 => 'UMAJIN' ],
        'application/vnd.unity' => [ 0 => 'unityweb', 1 => '.unityweb', 2 => 'Unity 3d' ],
        'application/vnd.uoml+xml' => [ 0 => 'uoml', 1 => '.uoml', 2 => 'Unique Object Markup Language' ],
        'application/vnd.vcx' => [ 0 => 'vcx', 1 => '.vcx', 2 => 'VirtualCatalog' ],
        'application/vnd.visio' => [ 0 => 'vsd', 1 => '.vsd', 2 => 'Microsoft Visio' ],
        'application/vnd.visio2013' => [ 0 => 'vsdx', 1 => '.vsdx', 2 => 'Microsoft Visio 2013' ],
        'application/vnd.visionary' => [ 0 => 'vis', 1 => '.vis', 2 => 'Visionary' ],
        'application/vnd.vsf' => [ 0 => 'vsf', 1 => '.vsf', 2 => 'Viewport+' ],
        'application/vnd.wap.wbxml' => [ 0 => 'wbxml', 1 => '.wbxml', 2 => 'WAP Binary XML (WBXML)' ],
        'application/vnd.wap.wmlc' => [ 0 => 'wmlc', 1 => '.wmlc', 2 => 'Compiled Wireless Markup Language (WMLC)' ],
        'application/vnd.wap.wmlscriptc' => [ 0 => 'wmlsc', 1 => '.wmlsc', 2 => 'WMLScript' ],
        'application/vnd.webturbo' => [ 0 => 'wtb', 1 => '.wtb', 2 => 'WebTurbo' ],
        'application/vnd.wolfram.player' => [ 0 => 'nbp', 1 => '.nbp', 2 => 'Mathematica Notebook Player' ],
        'application/vnd.wordperfect' => [ 0 => 'wpd', 1 => '.wpd', 2 => 'Wordperfect' ],
        'application/vnd.wqd' => [ 0 => 'wqd', 1 => '.wqd', 2 => 'SundaHus WQ' ],
        'application/vnd.wt.stf' => [ 0 => 'stf', 1 => '.stf', 2 => 'Worldtalk' ],
        'application/vnd.xara' => [ 0 => 'xar', 1 => '.xar', 2 => 'CorelXARA' ],
        'application/vnd.xfdl' => [ 0 => 'xfdl', 1 => '.xfdl', 2 => 'Extensible Forms Description Language' ],
        'application/vnd.yamaha.hv-dic' => [ 0 => 'hvd', 1 => '.hvd', 2 => 'HV Voice Dictionary' ],
        'application/vnd.yamaha.hv-script' => [ 0 => 'hvs', 1 => '.hvs', 2 => 'HV Script' ],
        'application/vnd.yamaha.hv-voice' => [ 0 => 'hvp', 1 => '.hvp', 2 => 'HV Voice Parameter' ],
        'application/vnd.yamaha.openscoreformat' => [ 0 => 'osf', 1 => '.osf', 2 => 'Open Score Format' ],
        'application/vnd.yamaha.openscoreformat.osfpvg+xml' => [ 0 => 'osfpvg', 1 => '.osfpvg', 2 => 'OSFPVG' ],
        'application/vnd.yamaha.smaf-audio' => [ 0 => 'saf', 1 => '.saf', 2 => 'SMAF Audio' ],
        'application/vnd.yamaha.smaf-phrase' => [ 0 => 'spf', 1 => '.spf', 2 => 'SMAF Phrase' ],
        'application/vnd.yellowriver-custom-menu' => [ 0 => 'cmp', 1 => '.cmp', 2 => 'CustomMenu' ],
        'application/vnd.zul' => [ 0 => 'zir', 1 => '.zir', 2 => 'Z.U.L. Geometry' ],
        'application/vnd.zzazz.deck+xml' => [ 0 => 'zaz', 1 => '.zaz', 2 => 'Zzazz Deck' ],
        'application/voicexml+xml' => [ 0 => 'vxml', 1 => '.vxml', 2 => 'VoiceXML' ],
        'application/widget' => [ 0 => 'wgt', 1 => '.wgt', 2 => 'Widget Packaging and XML Configuration' ],
        'application/winhlp' => [ 0 => 'hlp', 1 => '.hlp', 2 => 'WinHelp' ],
        'application/wsdl+xml' => [ 0 => 'wsdl', 1 => '.wsdl', 2 => 'WSDL - Web Services Description Language' ],
        'application/wspolicy+xml' => [ 0 => 'wspolicy', 1 => '.wspolicy', 2 => 'Web Services Policy' ],
        'application/x-7z-compressed' => [ 0 => '7z', 1 => '.7z', 2 => '7-Zip' ],
        'application/x-abiword' => [ 0 => 'abw', 1 => '.abw', 2 => 'AbiWord' ],
        'application/x-ace-compressed' => [ 0 => 'ace', 1 => '.ace', 2 => 'Ace Archive' ],
        'application/x-authorware-bin' => [ 0 => 'aab', 1 => '.aab', 2 => 'Adobe (Macropedia) Authorware - Binary File' ],
        'application/x-authorware-map' => [ 0 => 'aam', 1 => '.aam', 2 => 'Adobe (Macropedia) Authorware - Map' ],
        'application/x-authorware-seg' => [ 0 => 'aas', 1 => '.aas', 2 => 'Adobe (Macropedia) Authorware - Segment File' ],
        'application/x-bcpio' => [ 0 => 'bcpio', 1 => '.bcpio', 2 => 'Binary CPIO Archive' ],
        'application/x-bittorrent' => [ 0 => 'torrent', 1 => '.torrent', 2 => 'BitTorrent' ],
        'application/x-bzip' => [ 0 => 'bz', 1 => '.bz', 2 => 'Bzip Archive' ],
        'application/x-bzip2' => [ 0 => 'bz2', 1 => '.bz2', 2 => 'Bzip2 Archive' ],
        'application/x-cdlink' => [ 0 => 'vcd', 1 => '.vcd', 2 => 'Video CD' ],
        'application/x-chat' => [ 0 => 'chat', 1 => '.chat', 2 => 'pIRCh' ],
        'application/x-chess-pgn' => [ 0 => 'pgn', 1 => '.pgn', 2 => 'Portable Game Notation (Chess Games)' ],
        'application/x-cpio' => [ 0 => 'cpio', 1 => '.cpio', 2 => 'CPIO Archive' ],
        'application/x-csh' => [ 0 => 'csh', 1 => '.csh', 2 => 'C Shell Script' ],
        'application/x-debian-package' => [ 0 => 'deb', 1 => '.deb', 2 => 'Debian Package' ],
        'application/x-director' => [ 0 => 'dir', 1 => '.dir', 2 => 'Adobe Shockwave Player' ],
        'application/x-doom' => [ 0 => 'wad', 1 => '.wad', 2 => 'Doom Video Game' ],
        'application/x-dtbncx+xml' => [ 0 => 'ncx', 1 => '.ncx', 2 => 'Navigation Control file for XML (for ePub)' ],
        'application/x-dtbook+xml' => [ 0 => 'dtb', 1 => '.dtb', 2 => 'Digital Talking Book' ],
        'application/x-dtbresource+xml' => [ 0 => 'res', 1 => '.res', 2 => 'Digital Talking Book - Resource File' ],
        'application/x-dvi' => [ 0 => 'dvi', 1 => '.dvi', 2 => 'Device Independent File Format (DVI)' ],
        'application/x-font-bdf' => [ 0 => 'bdf', 1 => '.bdf', 2 => 'Glyph Bitmap Distribution Format' ],
        'application/x-font-ghostscript' => [ 0 => 'gsf', 1 => '.gsf', 2 => 'Ghostscript Font' ],
        'application/x-font-linux-psf' => [ 0 => 'psf', 1 => '.psf', 2 => 'PSF Fonts' ],
        'application/x-font-otf' => [ 0 => 'otf', 1 => '.otf', 2 => 'OpenType Font File' ],
        'application/x-font-pcf' => [ 0 => 'pcf', 1 => '.pcf', 2 => 'Portable Compiled Format' ],
        'application/x-font-snf' => [ 0 => 'snf', 1 => '.snf', 2 => 'Server Normal Format' ],
        'application/x-font-ttf' => [ 0 => 'ttf', 1 => '.ttf', 2 => 'TrueType Font' ],
        'application/x-font-type1' => [ 0 => 'pfa', 1 => '.pfa', 2 => 'PostScript Fonts' ],
        'application/x-font-woff' => [ 0 => 'woff', 1 => '.woff', 2 => 'Web Open Font Format' ],
        'application/x-futuresplash' => [ 0 => 'spl', 1 => '.spl', 2 => 'FutureSplash Animator' ],
        'application/x-gnumeric' => [ 0 => 'gnumeric', 1 => '.gnumeric', 2 => 'Gnumeric' ],
        'application/x-gtar' => [ 0 => 'gtar', 1 => '.gtar', 2 => 'GNU Tar Files' ],
        'application/x-hdf' => [ 0 => 'hdf', 1 => '.hdf', 2 => 'Hierarchical Data Format' ],
        'application/x-java-jnlp-file' => [ 0 => 'jnlp', 1 => '.jnlp', 2 => 'Java Network Launching Protocol' ],
        'application/x-latex' => [ 0 => 'latex', 1 => '.latex', 2 => 'LaTeX' ],
        'application/x-mobipocket-ebook' => [ 0 => 'prc', 1 => '.prc', 2 => 'Mobipocket' ],
        'application/x-ms-application' => [ 0 => 'application', 1 => '.application', 2 => 'Microsoft ClickOnce' ],
        'application/x-ms-wmd' => [ 0 => 'wmd', 1 => '.wmd', 2 => 'Microsoft Windows Media Player Download Package' ],
        'application/x-ms-wmz' => [ 0 => 'wmz', 1 => '.wmz', 2 => 'Microsoft Windows Media Player Skin Package' ],
        'application/x-ms-xbap' => [ 0 => 'xbap', 1 => '.xbap', 2 => 'Microsoft XAML Browser Application' ],
        'application/x-msaccess' => [ 0 => 'mdb', 1 => '.mdb', 2 => 'Microsoft Access' ],
        'application/x-msbinder' => [ 0 => 'obd', 1 => '.obd', 2 => 'Microsoft Office Binder' ],
        'application/x-mscardfile' => [ 0 => 'crd', 1 => '.crd', 2 => 'Microsoft Information Card' ],
        'application/x-msclip' => [ 0 => 'clp', 1 => '.clp', 2 => 'Microsoft Clipboard Clip' ],
        'application/x-msdownload' => [ 0 => 'exe', 1 => '.exe', 2 => 'Microsoft Application' ],
        'application/x-msmediaview' => [ 0 => 'mvb', 1 => '.mvb', 2 => 'Microsoft MediaView' ],
        'application/x-msmetafile' => [ 0 => 'wmf', 1 => '.wmf', 2 => 'Microsoft Windows Metafile' ],
        'application/x-msmoney' => [ 0 => 'mny', 1 => '.mny', 2 => 'Microsoft Money' ],
        'application/x-mspublisher' => [ 0 => 'pub', 1 => '.pub', 2 => 'Microsoft Publisher' ],
        'application/x-msschedule' => [ 0 => 'scd', 1 => '.scd', 2 => 'Microsoft Schedule+' ],
        'application/x-msterminal' => [ 0 => 'trm', 1 => '.trm', 2 => 'Microsoft Windows Terminal Services' ],
        'application/x-mswrite' => [ 0 => 'wri', 1 => '.wri', 2 => 'Microsoft Wordpad' ],
        'application/x-netcdf' => [ 0 => 'nc', 1 => '.nc', 2 => 'Network Common Data Form (NetCDF)' ],
        'application/x-pkcs12' => [ 0 => 'p12', 1 => '.p12', 2 => 'PKCS #12 - Personal Information Exchange Syntax Standard' ],
        'application/x-pkcs7-certificates' => [ 0 => 'p7b', 1 => '.p7b', 2 => 'PKCS #7 - Cryptographic Message Syntax Standard (Certificates)' ],
        'application/x-pkcs7-certreqresp' => [ 0 => 'p7r', 1 => '.p7r', 2 => 'PKCS #7 - Cryptographic Message Syntax Standard (Certificate Request Response)' ],
        'application/x-rar-compressed' => [ 0 => 'rar', 1 => '.rar', 2 => 'RAR Archive' ],
        'application/x-sh' => [ 0 => 'sh', 1 => '.sh', 2 => 'Bourne Shell Script' ],
        'application/x-shar' => [ 0 => 'shar', 1 => '.shar', 2 => 'Shell Archive' ],
        'application/x-shockwave-flash' => [ 0 => 'swf', 1 => '.swf', 2 => 'Adobe Flash' ],
        'application/x-silverlight-app' => [ 0 => 'xap', 1 => '.xap', 2 => 'Microsoft Silverlight' ],
        'application/x-stuffit' => [ 0 => 'sit', 1 => '.sit', 2 => 'Stuffit Archive' ],
        'application/x-stuffitx' => [ 0 => 'sitx', 1 => '.sitx', 2 => 'Stuffit Archive' ],
        'application/x-sv4cpio' => [ 0 => 'sv4cpio', 1 => '.sv4cpio', 2 => 'System V Release 4 CPIO Archive' ],
        'application/x-sv4crc' => [ 0 => 'sv4crc', 1 => '.sv4crc', 2 => 'System V Release 4 CPIO Checksum Data' ],
        'application/x-tar' => [ 0 => 'tar', 1 => '.tar', 2 => 'Tar File (Tape Archive)' ],
        'application/x-tcl' => [ 0 => 'tcl', 1 => '.tcl', 2 => 'Tcl Script' ],
        'application/x-tex' => [ 0 => 'tex', 1 => '.tex', 2 => 'TeX' ],
        'application/x-tex-tfm' => [ 0 => 'tfm', 1 => '.tfm', 2 => 'TeX Font Metric' ],
        'application/x-texinfo' => [ 0 => 'texinfo', 1 => '.texinfo', 2 => 'GNU Texinfo Document' ],
        'application/x-ustar' => [ 0 => 'ustar', 1 => '.ustar', 2 => 'Ustar (Uniform Standard Tape Archive)' ],
        'application/x-wais-source' => [ 0 => 'src', 1 => '.src', 2 => 'WAIS Source' ],
        'application/x-x509-ca-cert' => [ 0 => 'der', 1 => '.der', 2 => 'X.509 Certificate' ],
        'application/x-xfig' => [ 0 => 'fig', 1 => '.fig', 2 => 'Xfig' ],
        'application/x-xpinstall' => [ 0 => 'xpi', 1 => '.xpi', 2 => 'XPInstall - Mozilla' ],
        'application/xcap-diff+xml' => [ 0 => 'xdf', 1 => '.xdf', 2 => 'XML Configuration Access Protocol - XCAP Diff' ],
        'application/xenc+xml' => [ 0 => 'xenc', 1 => '.xenc', 2 => 'XML Encryption Syntax and Processing' ],
        'application/xhtml+xml' => [ 0 => 'xhtml', 1 => '.xhtml', 2 => 'XHTML - The Extensible HyperText Markup Language' ],
        'application/xml' => [ 0 => 'xml', 1 => '.xml', 2 => 'XML - Extensible Markup Language' ],
        'application/xml-dtd' => [ 0 => 'dtd', 1 => '.dtd', 2 => 'Document Type Definition' ],
        'application/xop+xml' => [ 0 => 'xop', 1 => '.xop', 2 => 'XML-Binary Optimized Packaging' ],
        'application/xslt+xml' => [ 0 => 'xslt', 1 => '.xslt', 2 => 'XML Transformations' ],
        'application/xspf+xml' => [ 0 => 'xspf', 1 => '.xspf', 2 => 'XSPF - XML Shareable Playlist Format' ],
        'application/xv+xml' => [ 0 => 'mxml', 1 => '.mxml', 2 => 'MXML' ],
        'application/yang' => [ 0 => 'yang', 1 => '.yang', 2 => 'YANG Data Modeling Language' ],
        'application/yin+xml' => [ 0 => 'yin', 1 => '.yin', 2 => 'YIN (YANG - XML)' ],
        'application/zip' => [ 0 => 'zip', 1 => '.zip', 2 => 'Zip Archive' ],
        'audio/adpcm' => [ 0 => 'adp', 1 => '.adp', 2 => 'Adaptive differential pulse-code modulation' ],
        'audio/basic' => [ 0 => 'au', 1 => '.au', 2 => 'Sun Audio - Au file format' ],
        'audio/midi' => [ 0 => 'mid', 1 => '.mid', 2 => 'MIDI - Musical Instrument Digital Interface' ],
        'audio/mp4' => [ 0 => 'mp4a', 1 => '.mp4a', 2 => 'MPEG-4 Audio' ],
        'audio/mpeg' => [ 0 => 'mpga', 1 => '.mpga', 2 => 'MPEG Audio' ],
        'audio/ogg' => [ 0 => 'oga', 1 => '.oga', 2 => 'Ogg Audio' ],
        'audio/vnd.dece.audio' => [ 0 => 'uva', 1 => '.uva', 2 => 'DECE Audio' ],
        'audio/vnd.digital-winds' => [ 0 => 'eol', 1 => '.eol', 2 => 'Digital Winds Music' ],
        'audio/vnd.dra' => [ 0 => 'dra', 1 => '.dra', 2 => 'DRA Audio' ],
        'audio/vnd.dts' => [ 0 => 'dts', 1 => '.dts', 2 => 'DTS Audio' ],
        'audio/vnd.dts.hd' => [ 0 => 'dtshd', 1 => '.dtshd', 2 => 'DTS High Definition Audio' ],
        'audio/vnd.lucent.voice' => [ 0 => 'lvp', 1 => '.lvp', 2 => 'Lucent Voice' ],
        'audio/vnd.ms-playready.media.pya' => [ 0 => 'pya', 1 => '.pya', 2 => 'Microsoft PlayReady Ecosystem' ],
        'audio/vnd.nuera.ecelp4800' => [ 0 => 'ecelp4800', 1 => '.ecelp4800', 2 => 'Nuera ECELP 4800' ],
        'audio/vnd.nuera.ecelp7470' => [ 0 => 'ecelp7470', 1 => '.ecelp7470', 2 => 'Nuera ECELP 7470' ],
        'audio/vnd.nuera.ecelp9600' => [ 0 => 'ecelp9600', 1 => '.ecelp9600', 2 => 'Nuera ECELP 9600' ],
        'audio/vnd.rip' => [ 0 => 'rip', 1 => '.rip', 2 => 'Hit\'n\'Mix' ],
        'audio/webm' => [ 0 => 'weba', 1 => '.weba', 2 => 'Open Web Media Project - Audio' ],
        'audio/x-aac' => [ 0 => 'aac', 1 => '.aac', 2 => 'Advanced Audio Coding (AAC)' ],
        'audio/x-aiff' => [ 0 => 'aif', 1 => '.aif', 2 => 'Audio Interchange File Format' ],
        'audio/x-mpegurl' => [ 0 => 'm3u', 1 => '.m3u', 2 => 'M3U (Multimedia Playlist)' ],
        'audio/x-ms-wax' => [ 0 => 'wax', 1 => '.wax', 2 => 'Microsoft Windows Media Audio Redirector' ],
        'audio/x-ms-wma' => [ 0 => 'wma', 1 => '.wma', 2 => 'Microsoft Windows Media Audio' ],
        'audio/x-pn-realaudio' => [ 0 => 'ram', 1 => '.ram', 2 => 'Real Audio Sound' ],
        'audio/x-pn-realaudio-plugin' => [ 0 => 'rmp', 1 => '.rmp', 2 => 'Real Audio Sound' ],
        'audio/x-wav' => [ 0 => 'wav', 1 => '.wav', 2 => 'Waveform Audio File Format (WAV)' ],
        'chemical/x-cdx' => [ 0 => 'cdx', 1 => '.cdx', 2 => 'ChemDraw eXchange file' ],
        'chemical/x-cif' => [ 0 => 'cif', 1 => '.cif', 2 => 'Crystallographic Interchange Format' ],
        'chemical/x-cmdf' => [ 0 => 'cmdf', 1 => '.cmdf', 2 => 'CrystalMaker Data Format' ],
        'chemical/x-cml' => [ 0 => 'cml', 1 => '.cml', 2 => 'Chemical Markup Language' ],
        'chemical/x-csml' => [ 0 => 'csml', 1 => '.csml', 2 => 'Chemical Style Markup Language' ],
        'chemical/x-xyz' => [ 0 => 'xyz', 1 => '.xyz', 2 => 'XYZ File Format' ],
        'image/bmp' => [ 0 => 'bmp', 1 => '.bmp', 2 => 'Bitmap Image File' ],
        'image/cgm' => [ 0 => 'cgm', 1 => '.cgm', 2 => 'Computer Graphics Metafile' ],
        'image/g3fax' => [ 0 => 'g3', 1 => '.g3', 2 => 'G3 Fax Image' ],
        'image/gif' => [ 0 => 'gif', 1 => '.gif', 2 => 'Graphics Interchange Format' ],
        'image/ief' => [ 0 => 'ief', 1 => '.ief', 2 => 'Image Exchange Format' ],
        'image/jpeg' => [ 0 => 'jpeg, .jpg', 1 => '.jpeg, .jpg', 2 => 'JPEG Image' ],
        'image/pjpeg' => [ 0 => 'pjpeg', 1 => '.pjpeg', 2 => 'JPEG Image (Progressive)' ],
        'image/ktx' => [ 0 => 'ktx', 1 => '.ktx', 2 => 'OpenGL Textures (KTX)' ],
        'image/png' => [ 0 => 'png', 1 => '.png', 2 => 'Portable Network Graphics (PNG)' ],
        'image/x-png' => [ 0 => 'png', 1 => '.png', 2 => 'Portable Network Graphics (PNG) (x-token)' ],
        'image/x-citrix-png' => [ 0 => 'png', 1 => '.png', 2 => 'Portable Network Graphics (PNG) (Citrix client)' ],
        'image/prs.btif' => [ 0 => 'btif', 1 => '.btif', 2 => 'BTIF' ],
        'image/svg+xml' => [ 0 => 'svg', 1 => '.svg', 2 => 'Scalable Vector Graphics (SVG)' ],
        'image/tiff' => [ 0 => 'tiff', 1 => '.tiff', 2 => 'Tagged Image File Format' ],
        'image/vnd.adobe.photoshop' => [ 0 => 'psd', 1 => '.psd', 2 => 'Photoshop Document' ],
        'image/vnd.dece.graphic' => [ 0 => 'uvi', 1 => '.uvi', 2 => 'DECE Graphic' ],
        'image/vnd.dvb.subtitle' => [ 0 => 'sub', 1 => '.sub', 2 => 'Close Captioning - Subtitle' ],
        'image/vnd.djvu' => [ 0 => 'djvu', 1 => '.djvu', 2 => 'DjVu' ],
        'image/vnd.dwg' => [ 0 => 'dwg', 1 => '.dwg', 2 => 'DWG Drawing' ],
        'image/vnd.dxf' => [ 0 => 'dxf', 1 => '.dxf', 2 => 'AutoCAD DXF' ],
        'image/vnd.fastbidsheet' => [ 0 => 'fbs', 1 => '.fbs', 2 => 'FastBid Sheet' ],
        'image/vnd.fpx' => [ 0 => 'fpx', 1 => '.fpx', 2 => 'FlashPix' ],
        'image/vnd.fst' => [ 0 => 'fst', 1 => '.fst', 2 => 'FAST Search & Transfer ASA' ],
        'image/vnd.fujixerox.edmics-mmr' => [ 0 => 'mmr', 1 => '.mmr', 2 => 'EDMICS 2000' ],
        'image/vnd.fujixerox.edmics-rlc' => [ 0 => 'rlc', 1 => '.rlc', 2 => 'EDMICS 2000' ],
        'image/vnd.ms-modi' => [ 0 => 'mdi', 1 => '.mdi', 2 => 'Microsoft Document Imaging Format' ],
        'image/vnd.net-fpx' => [ 0 => 'npx', 1 => '.npx', 2 => 'FlashPix' ],
        'image/vnd.wap.wbmp' => [ 0 => 'wbmp', 1 => '.wbmp', 2 => 'WAP Bitamp (WBMP)' ],
        'image/vnd.xiff' => [ 0 => 'xif', 1 => '.xif', 2 => 'eXtended Image File Format (XIFF)' ],
        'image/webp' => [ 0 => 'webp', 1 => '.webp', 2 => 'WebP Image' ],
        'image/x-cmu-raster' => [ 0 => 'ras', 1 => '.ras', 2 => 'CMU Image' ],
        'image/x-cmx' => [ 0 => 'cmx', 1 => '.cmx', 2 => 'Corel Metafile Exchange (CMX)' ],
        'image/x-freehand' => [ 0 => 'fh', 1 => '.fh', 2 => 'FreeHand MX' ],
        'image/x-icon' => [ 0 => 'ico', 1 => '.ico', 2 => 'Icon Image' ],
        'image/x-pcx' => [ 0 => 'pcx', 1 => '.pcx', 2 => 'PCX Image' ],
        'image/x-pict' => [ 0 => 'pic', 1 => '.pic', 2 => 'PICT Image' ],
        'image/x-portable-anymap' => [ 0 => 'pnm', 1 => '.pnm', 2 => 'Portable Anymap Image' ],
        'image/x-portable-bitmap' => [ 0 => 'pbm', 1 => '.pbm', 2 => 'Portable Bitmap Format' ],
        'image/x-portable-graymap' => [ 0 => 'pgm', 1 => '.pgm', 2 => 'Portable Graymap Format' ],
        'image/x-portable-pixmap' => [ 0 => 'ppm', 1 => '.ppm', 2 => 'Portable Pixmap Format' ],
        'image/x-rgb' => [ 0 => 'rgb', 1 => '.rgb', 2 => 'Silicon Graphics RGB Bitmap' ],
        'image/x-xbitmap' => [ 0 => 'xbm', 1 => '.xbm', 2 => 'X BitMap' ],
        'image/x-xpixmap' => [ 0 => 'xpm', 1 => '.xpm', 2 => 'X PixMap' ],
        'image/x-xwindowdump' => [ 0 => 'xwd', 1 => '.xwd', 2 => 'X Window Dump' ],
        'message/rfc822' => [ 0 => 'eml', 1 => '.eml', 2 => 'Email Message' ],
        'model/iges' => [ 0 => 'igs', 1 => '.igs', 2 => 'Initial Graphics Exchange Specification (IGES)' ],
        'model/mesh' => [ 0 => 'msh', 1 => '.msh', 2 => 'Mesh Data Type' ],
        'model/vnd.collada+xml' => [ 0 => 'dae', 1 => '.dae', 2 => 'COLLADA' ],
        'model/vnd.dwf' => [ 0 => 'dwf', 1 => '.dwf', 2 => 'Autodesk Design Web Format (DWF)' ],
        'model/vnd.gdl' => [ 0 => 'gdl', 1 => '.gdl', 2 => 'Geometric Description Language (GDL)' ],
        'model/vnd.gtw' => [ 0 => 'gtw', 1 => '.gtw', 2 => 'Gen-Trix Studio' ],
        'model/vnd.mts' => [ 0 => 'mts', 1 => '.mts', 2 => 'Virtue MTS' ],
        'model/vnd.vtu' => [ 0 => 'vtu', 1 => '.vtu', 2 => 'Virtue VTU' ],
        'model/vrml' => [ 0 => 'wrl', 1 => '.wrl', 2 => 'Virtual Reality Modeling Language' ],
        'text/calendar' => [ 0 => 'ics', 1 => '.ics', 2 => 'iCalendar' ],
        'text/css' => [ 0 => 'css', 1 => '.css', 2 => 'Cascading Style Sheets (CSS)' ],
        'text/csv' => [ 0 => 'csv', 1 => '.csv', 2 => 'Comma-Seperated Values' ],
        'text/html' => [ 0 => 'html', 1 => '.html', 2 => 'HyperText Markup Language (HTML)' ],
        'text/n3' => [ 0 => 'n3', 1 => '.n3', 2 => 'Notation3' ],
        'text/plain' => [ 0 => 'txt', 1 => '.txt', 2 => 'Text File' ],
        'text/prs.lines.tag' => [ 0 => 'dsc', 1 => '.dsc', 2 => 'PRS Lines Tag' ],
        'text/richtext' => [ 0 => 'rtx', 1 => '.rtx', 2 => 'Rich Text Format (RTF)' ],
        'text/sgml' => [ 0 => 'sgml', 1 => '.sgml', 2 => 'Standard Generalized Markup Language (SGML)' ],
        'text/tab-separated-values' => [ 0 => 'tsv', 1 => '.tsv', 2 => 'Tab Seperated Values' ],
        'text/troff' => [ 0 => 't', 1 => '.t', 2 => 'troff' ],
        'text/turtle' => [ 0 => 'ttl', 1 => '.ttl', 2 => 'Turtle (Terse RDF Triple Language)' ],
        'text/uri-list' => [ 0 => 'uri', 1 => '.uri', 2 => 'URI Resolution Services' ],
        'text/vnd.curl' => [ 0 => 'curl', 1 => '.curl', 2 => 'Curl - Applet' ],
        'text/vnd.curl.dcurl' => [ 0 => 'dcurl', 1 => '.dcurl', 2 => 'Curl - Detached Applet' ],
        'text/vnd.curl.scurl' => [ 0 => 'scurl', 1 => '.scurl', 2 => 'Curl - Source Code' ],
        'text/vnd.curl.mcurl' => [ 0 => 'mcurl', 1 => '.mcurl', 2 => 'Curl - Manifest File' ],
        'text/vnd.fly' => [ 0 => 'fly', 1 => '.fly', 2 => 'mod_fly / fly.cgi' ],
        'text/vnd.fmi.flexstor' => [ 0 => 'flx', 1 => '.flx', 2 => 'FLEXSTOR' ],
        'text/vnd.graphviz' => [ 0 => 'gv', 1 => '.gv', 2 => 'Graphviz' ],
        'text/vnd.in3d.3dml' => [ 0 => '3dml', 1 => '.3dml', 2 => 'In3D - 3DML' ],
        'text/vnd.in3d.spot' => [ 0 => 'spot', 1 => '.spot', 2 => 'In3D - 3DML' ],
        'text/vnd.sun.j2me.app-descriptor' => [ 0 => 'jad', 1 => '.jad', 2 => 'J2ME App Descriptor' ],
        'text/vnd.wap.wml' => [ 0 => 'wml', 1 => '.wml', 2 => 'Wireless Markup Language (WML)' ],
        'text/vnd.wap.wmlscript' => [ 0 => 'wmls', 1 => '.wmls', 2 => 'Wireless Markup Language Script (WMLScript)' ],
        'text/x-asm' => [ 0 => 's', 1 => '.s', 2 => 'Assembler Source File' ],
        'text/x-c' => [ 0 => 'c', 1 => '.c', 2 => 'C Source File' ],
        'text/x-fortran' => [ 0 => 'f', 1 => '.f', 2 => 'Fortran Source File' ],
        'text/x-pascal' => [ 0 => 'p', 1 => '.p', 2 => 'Pascal Source File' ],
        'text/x-java-source,java' => [ 0 => 'java', 1 => '.java', 2 => 'Java Source File' ],
        'text/x-setext' => [ 0 => 'etx', 1 => '.etx', 2 => 'Setext' ],
        'text/x-uuencode' => [ 0 => 'uu', 1 => '.uu', 2 => 'UUEncode' ],
        'text/x-vcalendar' => [ 0 => 'vcs', 1 => '.vcs', 2 => 'vCalendar' ],
        'text/x-vcard' => [ 0 => 'vcf', 1 => '.vcf', 2 => 'vCard' ],
        'video/3gpp' => [ 0 => '3gp', 1 => '.3gp', 2 => '3GP' ],
        'video/3gpp2' => [ 0 => '3g2', 1 => '.3g2', 2 => '3GP2' ],
        'video/h261' => [ 0 => 'h261', 1 => '.h261', 2 => 'H.261' ],
        'video/h263' => [ 0 => 'h263', 1 => '.h263', 2 => 'H.263' ],
        'video/h264' => [ 0 => 'h264', 1 => '.h264', 2 => 'H.264' ],
        'video/jpeg' => [ 0 => 'jpgv', 1 => '.jpgv', 2 => 'JPGVideo' ],
        'video/jpm' => [ 0 => 'jpm', 1 => '.jpm', 2 => 'JPEG 2000 Compound Image File Format' ],
        'video/mj2' => [ 0 => 'mj2', 1 => '.mj2', 2 => 'Motion JPEG 2000' ],
        'video/mp4' => [ 0 => 'mp4', 1 => '.mp4', 2 => 'MPEG-4 Video' ],
        'video/mpeg' => [ 0 => 'mpeg', 1 => '.mpeg', 2 => 'MPEG Video' ],
        'video/ogg' => [ 0 => 'ogv', 1 => '.ogv', 2 => 'Ogg Video' ],
        'video/quicktime' => [ 0 => 'qt', 1 => '.qt', 2 => 'Quicktime Video' ],
        'video/vnd.dece.hd' => [ 0 => 'uvh', 1 => '.uvh', 2 => 'DECE High Definition Video' ],
        'video/vnd.dece.mobile' => [ 0 => 'uvm', 1 => '.uvm', 2 => 'DECE Mobile Video' ],
        'video/vnd.dece.pd' => [ 0 => 'uvp', 1 => '.uvp', 2 => 'DECE PD Video' ],
        'video/vnd.dece.sd' => [ 0 => 'uvs', 1 => '.uvs', 2 => 'DECE SD Video' ],
        'video/vnd.dece.video' => [ 0 => 'uvv', 1 => '.uvv', 2 => 'DECE Video' ],
        'video/vnd.fvt' => [ 0 => 'fvt', 1 => '.fvt', 2 => 'FAST Search & Transfer ASA' ],
        'video/vnd.mpegurl' => [ 0 => 'mxu', 1 => '.mxu', 2 => 'MPEG Url' ],
        'video/vnd.ms-playready.media.pyv' => [ 0 => 'pyv', 1 => '.pyv', 2 => 'Microsoft PlayReady Ecosystem Video' ],
        'video/vnd.uvvu.mp4' => [ 0 => 'uvu', 1 => '.uvu', 2 => 'DECE MP4' ],
        'video/vnd.vivo' => [ 0 => 'viv', 1 => '.viv', 2 => 'Vivo' ],
        'video/webm' => [ 0 => 'webm', 1 => '.webm', 2 => 'Open Web Media Project - Video' ],
        'video/x-f4v' => [ 0 => 'f4v', 1 => '.f4v', 2 => 'Flash Video' ],
        'video/x-fli' => [ 0 => 'fli', 1 => '.fli', 2 => 'FLI/FLC Animation Format' ],
        'video/x-flv' => [ 0 => 'flv', 1 => '.flv', 2 => 'Flash Video' ],
        'video/x-m4v' => [ 0 => 'm4v', 1 => '.m4v', 2 => 'M4v' ],
        'video/x-ms-asf' => [ 0 => 'asf', 1 => '.asf', 2 => 'Microsoft Advanced Systems Format (ASF)' ],
        'video/x-ms-wm' => [ 0 => 'wm', 1 => '.wm', 2 => 'Microsoft Windows Media' ],
        'video/x-ms-wmv' => [ 0 => 'wmv', 1 => '.wmv', 2 => 'Microsoft Windows Media Video' ],
        'video/x-ms-wmx' => [ 0 => 'wmx', 1 => '.wmx', 2 => 'Microsoft Windows Media Audio/Video Playlist' ],
        'video/x-ms-wvx' => [ 0 => 'wvx', 1 => '.wvx', 2 => 'Microsoft Windows Media Video Playlist' ],
        'video/x-msvideo' => [ 0 => 'avi', 1 => '.avi', 2 => 'Audio Video Interleave (AVI)' ],
        'video/x-sgi-movie' => [ 0 => 'movie', 1 => '.movie', 2 => 'SGI Movie' ],
        'x-conference/x-cooltalk' => [ 0 => 'ice', 1 => '.ice', 2 => 'CoolTalk' ],
        'text/plain-bas' => [ 0 => 'par', 1 => '.par', 2 => 'BAS Partitur Format' ],
        'text/yaml' => [ 0 => 'yaml', 1 => '.yaml', 2 => 'YAML Ain\'t Markup Language / Yet Another Markup Language' ],
        'application/x-apple-diskimage' => [ 0 => 'dmg', 1 => '.dmg', 2 => 'Apple Disk Image' ],
    ];

    /* Decoded copies of prefix and suffix strings */

    $pfx = html_entity_decode(  $attrs['prefix'] );
    $sfx = html_entity_decode( $attrs['suffix'] );

    /* Avoid user confusion by allowing debug only in preview mode */

    $debug = $attrs['debug'] && is_preview();

    /* Content is required */

    if( $content == null ) {
        if( $debug ) {
            return "$pfx$name: no content$sfx";
        }
        return '';
    }

    /* Extract URL from (first) href in content */

    if( !( preg_match( '/\bhref=[\']([^\']+)[\']/i', $content, $refs, 0, 0 ) ||
           preg_match( '/\bhref=["]([^"]+)["]/i', $content, $refs, 0, 0 ) ) ) {
        if( $debug ) {
            return "$content$pfx$name: no URL in " .
                   htmlspecialchars( $content ) . $sfx;
        }
        return "$content$pfx$sfx";
    }
    $ref = $refs[1];

    /* Make a HEAD request for the resource
     * If we get a Location header, assume a redirect & follow to depth 5.
     * Note that the request is unlikely to fail due to WP defaulting.
     */

    for( $i = 0; $i < 5; $i++ ) {
        if( !($h = wp_get_http_headers( $ref )) ) {
            if( $debug ) {
                return "$content$pfx$name: No response for " .
                       htmlspecialchars( $ref ) . $sfx;
            }
            return "$content$pfx$sfx";
        }
        if( isset( $h['Location' ] ) ) {
            $ref = $h['Location'];
        } else {
            break;
        }
    }

    /* Accumulate result for each requested item */

    $result = '';
    foreach( explode( ' ', $attrs['item'] ) as $item ) {
        $isdate = 0;

        /* Map item to HTTP header */

        switch( strtolower( $item ) ) {
    case 'mtime':
            $hdr = 'Last-Modified';
            $isdate = 1;
            break;
    case 'expires':
            $hdr = 'Expires';
            $isdate = 1;
            break;
    case 'size':
            $hdr = 'Content-Length';
            break;
    case 'etag':
            $hdr = 'ETag';
            break;
    case 'type':
    case 'type-name':
    case 'type-ext':
    case 'type-desc':
            $hdr = 'Content-Type';
            break;
    default:
            $result .= "$pfx$name: Invalid item '$item'$sfx";
            continue 2;
        }

        /* Handle missing headers */

        if( !isset( $h[$hdr] ) ) {
            $default = '-';
            switch( strtolower( $item ) ) {
    case 'type-name':
                if( preg_match( '/\.(\w+)$/', $ref, $refs, 0, 0 ) ) {
                    $default = $refs[1];
                }
                break;
    case 'type-ext':
                if( preg_match( '/(\.\w+)$/', $ref, $refs, 0, 0 ) ) {
                    $default = $refs[1];
                }
                break;
            }
            if( $debug ) {
                $result .= "$pfx$name: no $hdr in response for " .
                           htmlspecialchars( $ref ) . "$default$sfx";
            } else {
                $result .= "$pfx$default$sfx";
            }
            continue;
        }

        /* Obtain value of header */

        $value = $h[$hdr];
        if( !$isdate ) {
            /* Non-date. Handle any value mapping for item */

            if( preg_match( '/^type-(\w+)/i', $item, $refs, 0, 0 ) ) {
                if( strtolower( $refs[1] == 'name' ) ) {
                    if( preg_match( '/^([^;]+);/', $value, $vrefs, 0, 0 ) ) {
                        $value = $vrefs[1];
                    }
                }
                if( isset( $types[$value] ) ) {
                    switch( strtolower( $refs[1] ) ) {
            case 'name':
                        $value = $types[$value][0];
                        break;
            case 'ext':
                        $value = $types[$value][1];
                        break;
            case 'desc':
                        $value = $types[$value][2];
                        break;
                    }
                }
            }
            $result .= "$pfx$value$sfx";
            continue;
        }

        /* Value is a date.  Decode the header value.
         * Set result to specified timezone (from GMT), or PHP/server default.
         * Format as specified.
         */

        if( $dv = DateTime::createFromFormat( '!D\, d M Y H:i:s T', $value ) ) {
            if( $attrs['timezone'] != null ) {
                $tz = $attrs['timezone'];
            } elseif( !($tz = ini_get( "date.timezone" )) ) {
                $tz = date_default_timezone_get();
            }
            if( !(isset( $tz ) && $tz != null && $tz != '') ) {
                $tz = 'UTC';
            }
            try {
                $dv->setTimeZone( new DateTimeZone( $tz ) );
            } catch ( Exception $e ) {
                $result .= "$pfx$name: Invalid timezone '" ."$tz': " .
                           $e->getMessage() . $sfx;
                continue;
            }
            $value = $dv->format( $attrs['format'] );
        } else { /* Invalid format or date value */
            if( $debug ) {
                $value .= "$name:$value<pre>" .
                           print_r( DateTime::getLastErrors(), true ) .
                          '</pre>';
            }
        }
        $result .= "$pfx$value$sfx";
    }

    return "$content$result";
}

/* Register shortcode */

add_shortcode( 'urlinfo', 'urlinfo_tag' );
?>
