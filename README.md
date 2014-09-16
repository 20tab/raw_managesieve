raw_managesieve
=========================

A Roundcube plugin to add raw sieve filters.

## Installation

Clone the entire plugin into the <b><i>your-roundcube-installation-folder/plugins</i></b>

Then edit your roundcube config file and activate the plugin:

```php

$config['plugins'] = array(
  'raw_managesieve', 
  ...  
);

```

## Configuration

Edit the raw_managesieve config.inc.php file in the <b><i>plugins/raw_managesieve</i></b> directory
and fill in the informations about your sieve server.


## Example usage

In this example we have a script that provides imap flags for messages that satisfy certain rules.
( You can integrate this functionality with to those of this other plugin https://github.com/20tab/raw_managesieve )






