<?php

// managesieve server port. When empty the port will be determined automatically
// using getservbyname() function, with 4190 as a fallback.
$config['raw_sieve_port'] = null;

// managesieve server address, default is localhost.
// Replacement variables supported in host name:
// %h - user's IMAP hostname
// %n - http hostname ($_SERVER['SERVER_NAME'])
// %d - domain (http hostname without the first part)
// For example %n = mail.domain.tld, %d = domain.tld
$config['raw_sieve_host'] = 'mail0001.unbit.it';

// authentication method. Can be CRAM-MD5, DIGEST-MD5, PLAIN, LOGIN, EXTERNAL
// or none. Optional, defaults to best method supported by server.
$config['raw_sieve_auth_type'] = null;

// Optional managesieve authentication identifier to be used as authorization proxy.
// Authenticate as a different user but act on behalf of the logged in user.
// Works with PLAIN and DIGEST-MD5 auth.
$config['raw_sieve_auth_cid'] = null;

// Optional managesieve authentication password to be used for imap_auth_cid
$config['raw_sieve_auth_pw'] = null;

// use or not TLS for managesieve server connection
// Note: tls:// prefix in managesieve_host is also supported
$config['raw_sieve_usetls'] = false;

// default contents of filters script (eg. default spam filter)
$config['raw_sieve_default'] = '/etc/dovecot/sieve/global';

// The name of the script which will be used when there's no user script
$config['raw_sieve_script_name'] = 'managesieve';

// Sieve RFC says that we should use UTF-8 endcoding for mailbox names,
// but some implementations does not covert UTF-8 to modified UTF-7.
// Defaults to UTF7-IMAP
$config['raw_sieve_mbox_encoding'] = 'UTF-8';

// I need this because my dovecot (with listescape plugin) uses
// ':' delimiter, but creates folders with dot delimiter
$config['raw_sieve_replace_delimiter'] = '';

// disabled sieve extensions (body, copy, date, editheader, encoded-character,
// envelope, environment, ereject, fileinto, ihave, imap4flags, index,
// mailbox, mboxmetadata, regex, reject, relational, servermetadata,
// spamtest, spamtestplus, subaddress, vacation, variables, virustest, etc.
// Note: not all extensions are implemented
// $config['raw_sieve_disabled_extensions'] = array();

// Enables debugging of conversation with sieve server. Logs it into <log_dir>/sieve
$config['raw_sieve_debug'] = false;

// Enables features described in http://wiki.kolab.org/KEP:14
$config['raw_sieve_kolab_master'] = false;

// Script name extension used for scripts including. Dovecot uses '.sieve',
// Cyrus uses '.siv'. Doesn't matter if you have managesieve_kolab_master disabled.
$config['raw_sieve_filename_extension'] = '.sieve';

// List of reserved script names (without extension).
// Scripts listed here will be not presented to the user.
$config['raw_sieve_filename_exceptions'] = array();

// List of domains limiting destination emails in redirect action
// If not empty, user will need to select domain from a list
$config['raw_sieve_domains'] = array();

// Enables separate management interface for vacation responses (out-of-office)
// 0 - no separate section (default),
// 1 - add Vacation section,
// 2 - add Vacation section, but hide Filters section
$config['raw_sieve_vacation'] = 0;

// Supported methods of notify extension. Default: 'mailto'
$config['raw_sieve_notify_methods'] = array('mailto');
