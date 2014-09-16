<?php

/**
 * Managesieve (Sieve Filters)
 *
 * Plugin that adds a possibility to manage Sieve filters in Thunderbird's style.
 * It's clickable interface which operates on text scripts and communicates
 * with server using managesieve protocol. Adds Filters tab in Settings.
 *
 * @version @package_version@
 * @author Aleksander Machniak <alec@alec.pl>
 *
 * Configuration (see config.inc.php.dist)
 *
 * Copyright (C) 2008-2013, The Roundcube Dev Team
 * Copyright (C) 2011-2013, Kolab Systems AG
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see http://www.gnu.org/licenses/.
 */


include "lib/sieve_manager.php";


class raw_managesieve extends rcube_plugin
{
    public $task = 'settings';
    private $rc;
    private $script;
    private $sieve;
    private $filterslist;
    private $contentframe;

    function init()
    {

        $this->add_texts('localization/', true);
        $this->load_config();

        $this->rc = rcmail::get_instance();

        $this->sieve = new SieveManager($this->rc->config);
        $this->add_hook('settings_actions', array($this, 'settings_actions'));

        $this->register_action('plugin.rawmanagesieve', array($this, 'raw_managesieve_init'));
        $this->register_action('plugin.addrawcode', array($this, 'add_raw_code'));
        $this->register_action('plugin.removerawcode', array($this, 'remove_raw_code'));
        $this->register_action('plugin.activate_script', array($this, 'activate_script'));
        $this->register_action('plugin.deactivate_script', array($this, 'deactivate_script'));

        $this->register_handler('plugin.rawcodeform', array($this, 'raw_code_form'));
        $this->register_handler('plugin.filterslist', array($this, 'filter_list'));
        $this->register_handler('plugin.filterframe', array($this, 'filter_frame'));

        $this->include_script('raw_managesieve.js');
        $this->include_stylesheet('raw_managesieve.css');

    }

    public function add_raw_code() {

        if (get_input_value("_add", RCUBE_INPUT_POST)) {
            $this->do_script_operations();
        }

        $this->rc->output->set_env('formediting', 0);

        if($id  = get_input_value('_id', RCUBE_INPUT_GPC)) {
            $this->rc->output->set_env('formediting', 1);
            if($id == $this->sieve->active_script_name) {
                $this->rc->output->set_env('active', 1);
            }
        }

        $this->rc->output->send("raw_managesieve.code_form");

    }

    public function show_confirmations() {
        $this->rc->output->show_message('Script saved correctly', 'confirmation');
    }

    public function do_script_operations() {
    
        $mod_name = get_input_value("_id", RCUBE_INPUT_POST);
        $new_name = get_input_value("script_name", RCUBE_INPUT_POST);
        $content = get_input_value("code", RCUBE_INPUT_POST);

        $name = $new_name ?: $mod_name;

        $this->sieve->install_script($name, $content);

        if(!$mod_name) {
            $this->rc->output->command(
                "parent.update_filter_list",
                array("action" => "add", "fid" => $name)
            );
        }

        $this->show_confirmations();

    }

    public function raw_code_form() {

        $script = "";
        $server_script_name = "";
        $form_action = ".";

        if($id  = get_input_value('_id', RCUBE_INPUT_GPC)) {
            $server_script_name = $id;
            $script = $this->sieve->getScript($id);
        }

        $hiddenfields = new html_hiddenfield(
            array(
                'name' => '_task',
                'value' => $this->rc->task
            )
        );
        $hiddenfields->add(array('name' => '_action', 'value' => 'plugin.addrawcode'));
        $hiddenfields->add(array('name' => '_add', 'value' => 1));

        if ($server_script_name != "") {
            $hiddenfields->add(array('name' => '_id', 'value' => $server_script_name));
        } 

        $out = "<form action=". $form_action ." id=\"raw_script_form\" method=\"POST\">\n";
        $out .= $hiddenfields->show();
        $out .= "<fieldset><legend>".$this->gettext("insert_raw_code")."</legend>\n";


        $script_name_label = html::label(
            array(
                "for"   => "script_name",
                "class" => "script_raw_name_label"
            ),
            "Script name"
        );

        $script_name = new html_inputfield(
            array(
                'name'  => 'script_name',
                'class' => 'script_raw_name',
                'id'    => 'script_name',
                'type'  => 'text'
            )
        );

        $textarea_code = new html_textarea(
            array(
                'name'  => 'code',
                'class' => 'codetextarea',
                'cols'  => '50',
                'rows'  => '20',
                'value' => $script
            )
        );

        if(!$id) {
            $out .= $script_name_label;
            $out .= '<br>';
            $out .= $script_name->show($server_script_name);
            $out .= '<br><br>';            
        }

        $out .= $textarea_code->show();
        $out .= "\n\n</fieldset>\n</form>";

        $this->rc->output->include_script('list.js');
        $this->rc->output->add_gui_object('raw_form', "raw_script_form");

        return $out;

    }

    public function activate_script() {
        $id = get_input_value("_id", RCUBE_INPUT_GPC);
        $this->sieve->activate_script($id);
        $this->rc->output->show_message('Script activated correctly', 'confirmation');
        $this->rc->output->command("plugin.rawsievereload_frame");

    }

    public function deactivate_script() {
        $this->sieve->deactivate_script();
        $this->rc->output->show_message('Script deactivated correctly', 'confirmation');
        $this->rc->output->command("plugin.rawsievereload_frame");

    }

    public function remove_raw_code() {

        $id = get_input_value("_id", RCUBE_INPUT_GPC);
        if ($this->sieve->remove_script($id)) {
            $this->rc->output->show_message('Script removed correctly', 'confirmation');
            $this->rc->output->command("plugin.rawsievereload", array("action" => "del", "fid" => $id));
        }


    }

    public function filter_frame($attrib) {

        if (!$attrib['id'])
            $attrib['id'] = 'rcmfilterframe';

        $attrib['name'] = $attrib['id'];

        $this->rc->output->set_env('contentframe', $attrib['name']);
        $this->rc->output->set_env('blankpage', $attrib['src'] ?
        $this->rc->output->abs_url($attrib['src']) : 'program/resources/blank.gif');

        return $this->rc->output->frame($attrib);

    }

    public function raw_managesieve_init() {
        $this->rc->output->send("raw_managesieve.add_code");
    }

    public function filter_list() {
        
        $this->filterslist = $this->sieve->list_scripts();

        $tableout = new html_table(
            array(
                "id"          => "filtersset",
                "class"       => "listing",
                "cellspacing" => "0",
                "summary"     => "Filters list",
                "type"        => "list",
                "noheader"    => "true"
            )
        );

        foreach ($this->filterslist as $ind => $filtername) {

            $tableout->add_row(
                array(
                    "id" => "rcmrow".$filtername,
                    "class" => "raw_filter_row",
                )
            );

            $tableout->add(
                "name",
                $filtername
            );

        }

        $this->rc->output->add_gui_object('filtersset', "filtersset");
        $this->rc->output->include_script('list.js');

        return $tableout->show();
    }


    /**
     * Adds Filters section in Settings
     */
    public function settings_actions($args)
    {

        $args["actions"][] = array(
            "action" => "plugin.rawmanagesieve",
            "type" => "link",
            "label" => 'raw_managesieve.rawsievetitle',
            "title" => "Insert row filters",
            'class'  => 'filter-raw',
        );

        // $args["actions"][] = array(
        //     "command" => "plugin.add_raw_code",
        //     "type" => "link",
        //     "label" => "Raw filters",
        //     "title" => "Insert row filters",
        //     'class'  => 'filter-raw',
        // );


        return $args;
    }


}




















