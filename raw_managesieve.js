if (window.rcmail) {

    rcmail.addEventListener('init', function(evt) {
    
        rcmail.register_command('plugin.rawsieveadd', load_filter_form, true);
        rcmail.register_command('plugin.rawsievesave', raw_filter_save, true);
        rcmail.register_command('plugin.rawsievedelete', raw_filter_delete, true);
        rcmail.register_command('plugin.rawsieveactivate', raw_filter_activate);
        rcmail.register_command('plugin.rawsievedeactivate', raw_filter_deactivate);

        if(rcmail.env.active) {
            rcmail.enable_command("plugin.rawsievedeactivate", true);
        } else {
            rcmail.enable_command("plugin.rawsieveactivate", true);
        }

        rcmail.filters_list = new rcube_list_widget(
            rcmail.gui_objects.filtersset,
            {
                multiselect:false,
                draggable:true,
                keyboard:false
            }
        );

        rcmail.filters_list.addEventListener('select', function(list) {

            var id = list.get_single_selection()

            if(id) {
                var url = build_url(id, false);
                target = window.frames[rcmail.env.contentframe];
                target.location.href = url;
            }

        });

        rcmail.filters_list.init();
        rcmail.filters_list.focus();

    });

    $(document).ready(function() {
        rcmail.addEventListener('plugin.rawsievereload', reload_filter_form);
        rcmail.addEventListener('plugin.rawsievereload_frame', reload_frame_page);
    });

}

rcube_webmail.prototype.update_filter_list = function(data) {

    var list = this.filters_list;

    this.set_busy(true);

    switch (data.action) {
        case "del":
            list.clear_selection();
            list.remove_row(data.fid);
            this.show_contentframe(false);
            break;
        case "add":
            row = $('<tr><td class="name"></td></tr>');            
            row.attr('id', 'rcmrow'+data.fid);
            $('td', row).text(data.fid);
            list.insert_row(row.get(0), 0);
            list.select(data.fid)
            break;
    }

    this.set_busy(false);

}


function raw_filter_save() {
    rcmail.gui_objects.raw_form.submit();
}

function reload_frame_page() {
    window.location.reload();
}

function build_url(id, action) {

    var lockid = rcmail.set_busy(true, 'loading');
    var url = rcmail.get_task_url('settings');

    if (action) {
        url = rcmail.add_url(url, '_action', action);
    } else {
        url = rcmail.add_url(url, '_action', 'plugin.addrawcode');
    }

    if (id) {
        url = rcmail.add_url(url, '_id', id);
    }

    url = rcmail.add_url(url, '_framed', 1)
    url = rcmail.add_url(url, '_unlock', lockid)

    return url;

}

function load_filter_form() {
    rcmail.filters_list.clear_selection();
    url = build_url(false, false);
    target = window.frames[rcmail.env.contentframe];
    target.location.href = url;
}

function reload_filter_form(data) {
    rcmail.update_filter_list(data);
}

function raw_filter_activate() {
    var raw_form = $(rcmail.gui_objects.raw_form)
    var id = raw_form.find("input[name='_id']").val()
    var lock = rcmail.set_busy(true, 'loading');
    rcmail.http_post('plugin.activate_script', "_id="+id, lock);
}

function raw_filter_deactivate() {
    var lock = rcmail.set_busy(true, 'loading');
    rcmail.http_post('plugin.deactivate_script', "", lock);
}

function raw_filter_delete() {
    
    var id = rcmail.filters_list.get_single_selection()

    if(!id) {
        alert(rcmail.get_label('raw_managesieve.noscritpselected'));
        return;
    }

    if (confirm(rcmail.get_label('raw_managesieve.deletemessage'))) {
        var lock = rcmail.set_busy(true, 'loading');
        rcmail.http_post('plugin.removerawcode', "_id="+id, lock);
    }
}
