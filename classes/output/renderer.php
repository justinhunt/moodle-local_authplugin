<?php
/**
 * Created by PhpStorm.
 * User: ishineguy
 * Date: 2018/05/12
 * Time: 23:42
 */

namespace local_authplugin\output;

defined('MOODLE_INTERNAL') || die();


class renderer extends \plugin_renderer_base {

    /*
* Return HTML to display add first page links
* @param lesson $lesson
* @return string
*/
    public function say_hello() {

        return "say hello";
    }

    // User selection form.
    public function user_selection_form(\user_selector_base $userselector) {
        $output = '';
        $formattributes = array();
        $formattributes['id'] = 'userselectionform';
        $formattributes['action'] = $this->page->url;
        $formattributes['method'] = 'post';
        \html_writer::start_div('userselector');
        $output .= \html_writer::start_tag('form', $formattributes);
        $output .= \html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()));
        $output  .= $userselector->display(true);
        $output .= \html_writer::empty_tag('input', array(
                'type' => 'submit',
                'name' => 'Choose',
                'value' => get_string('chooseuser', 'local_authplugin'),
                'class' => 'actionbutton')
        );


        $output .= \html_writer::end_tag('form');
        \html_writer::start_div('userselector');
        return $output;
    }

    public function show_user_summary($selecteduser, $authplugin_user) {
        global $DB;

        $output = $this->output->heading(get_string("userheader", "local_authplugin",$selecteduser), 3);
        $theuser= $DB->get_record('user',array('id'=>$selecteduser->id));
        $selecteduser->username = $theuser->username;

        $table = new \html_table();
        $table->id = 'local_authplugin_usersummary';
        $table->head = array(
            'ID',
            get_string('username'),
            get_string('resellerid', 'local_authplugin'),
            get_string('awsaccessid', 'local_authplugin'),
            get_string('awsaccesssecret', 'local_authplugin'),
            get_string('actions', 'local_authplugin')
        );
        $table->headspan = array(1, 1, 1, 1,1,1);
        $table->colclasses = array(
            'id','username','rid','awsaccessid','awsaccesssecret', 'edit'
        );

        $row = new \html_table_row();
        $row->cells = array();

        $idcell = new \html_table_cell($authplugin_user->id);
        $row->cells[] = $idcell;

        $usernamecell = new \html_table_cell($selecteduser->username);
        $row->cells[] = $usernamecell;

        $reselleridcell = new \html_table_cell($authplugin_user->resellerid);
        $row->cells[] = $reselleridcell;

        $awsaccessidcell = new \html_table_cell($authplugin_user->awsaccessid);
        $row->cells[] = $awsaccessidcell;

        $awsaccesssecretcell = new \html_table_cell($authplugin_user->awsaccesssecret);
        $row->cells[] = $awsaccesssecretcell;

        $actionurl = '/local/authplugin/usersite/manageusers.php';
        $editurl = new \moodle_url($actionurl, array('userid' => $authplugin_user->userid));
        $editlink = \html_writer::link($editurl, get_string('edititem', 'local_authplugin'));
        $editcell = new \html_table_cell($editlink);
        $row->cells[] = $editcell;

        $table->data[] = $row;

        return $output . \html_writer::table($table);
    }

    public function add_siteitem_link($selecteduser) {
        global $CFG;

        $output = $this->output->heading(get_string("showitemsfor", "local_authplugin",$selecteduser), 4);
        // $output .= $this->output->heading(get_string("whatdonow", "local_authplugin"), 4);
        $links = array();

        $additemurl = new \moodle_url('/local/authplugin/usersite/manageusersites.php',
            array('userid'=>$selecteduser->id));
        $links[] = \html_writer::link($additemurl, get_string('addnewitem', 'local_authplugin'));

        return $this->output->box($output.'<p>'.implode('</p><p>', $links).'</p>', 'generalbox firstpageoptions');
    }

    /**
     * Return the html table of homeworks for a group  / course
     * @param array homework objects
     * @param integer $courseid
     * @return string html of table
     */
    function show_siteitems_list($items)
    {
        global $DB;

        if (!$items) {
            return $this->output->heading(get_string('noitems', 'local_authplugin'), 3, 'main');
        }

        $table = new \html_table();
        $table->id = 'local_authplugin_itempanel';
        $table->head = array(
            get_string('itemid', 'local_authplugin'),
            get_string('itemurl', 'local_authplugin'),
            get_string('actions', 'local_authplugin')
        );
        $table->headspan = array(1, 1, 2);
        $table->colclasses = array(
            'id','url', 'edit','delete'
        );

        //sort by start date
        \core_collator::asort_objects_by_property($items, 'timemodified', \core_collator::SORT_NUMERIC);

        //loop through the items and add to table
        foreach ($items as $item) {
            $row = new \html_table_row();
            $row->cells = array();

            $itemidcell = new \html_table_cell($item->id);
            $row->cells[] = $itemidcell;

            $urlcell = new \html_table_cell($item->url1);
            $row->cells[] = $urlcell;

            $itemactionurl = '/local/authplugin/usersite/manageusersites.php';
            $itemediturl = new \moodle_url($itemactionurl, array('userid' => $item->userid, 'id' => $item->id));
            $itemeditlink = \html_writer::link($itemediturl, get_string('edititem', 'local_authplugin'));
            $itemeditcell = new \html_table_cell($itemeditlink);
            $row->cells[] = $itemeditcell;


            $itemdeleteurl = new \moodle_url($itemactionurl, array('userid' => $item->userid, 'id' => $item->id, 'action' => 'confirmdelete'));
            $itemdeletelink = \html_writer::link($itemdeleteurl, get_string('deleteitem', 'local_authplugin'));
            $itemdeletecell = new \html_table_cell($itemdeletelink);
            $row->cells[] = $itemdeletecell;

            $table->data[] = $row;
        }

        return \html_writer::table($table);
    } //end of function

    public function add_subsitem_link($selecteduser) {
        global $CFG;

        $output = $this->output->heading(get_string("showsubsfor", "local_authplugin",$selecteduser), 4);
        // $output .= $this->output->heading(get_string("whatdonow", "local_authplugin"), 4);
        $links = array();

        $additemurl = new \moodle_url('/local/authplugin/subscriptions/manageusersubs.php',
            array('userid'=>$selecteduser->id));
        $links[] = \html_writer::link($additemurl, get_string('addnewsub', 'local_authplugin'));

        return $this->output->box($output.'<p>'.implode('</p><p>', $links).'</p>', 'generalbox firstpageoptions');
    }

    /**
     * Return the html table of subscriptions for a user
     * @param array usersub objects
     * @param integer $courseid
     * @return string html of table
     */
    function show_subsitems_list($items)
    {
        global $DB;

        if (!$items) {
            return $this->output->heading(get_string('nosubs', 'local_authplugin'), 3, 'main');
        }

        $table = new \html_table();
        $table->id = 'local_authplugin_subsitempanel';
        $table->head = array(
            'ID',
            get_string('subscriptionid', 'local_authplugin'),
            get_string('subscriptionname', 'local_authplugin'),
            get_string('transactionid', 'local_authplugin'),
            get_string('expiredate', 'local_authplugin'),
            get_string('actions', 'local_authplugin')
        );
        $table->headspan = array(1, 1, 1, 1, 1, 2);
        $table->colclasses = array(
            'id','subscriptionid','subscriptionname','transactionid', 'expiredate', 'edit', 'delete'
        );

        //sort by start date
        \core_collator::asort_objects_by_property($items, 'timemodified', \core_collator::SORT_NUMERIC);

        //loop through the items and add to table
        foreach ($items as $item) {
            $row = new \html_table_row();
            $row->cells = array();

            $itemidcell = new \html_table_cell($item->id);
            $row->cells[] = $itemidcell;

            $itemsubidcell = new \html_table_cell($item->subscriptionid);
            $row->cells[] = $itemsubidcell;

            $namecell = new \html_table_cell($item->subscriptionname);
            $row->cells[] = $namecell;

            $itemtranscell = new \html_table_cell($item->transactionid);
            $row->cells[] = $itemtranscell;

            $itemexpiredatecell = new \html_table_cell(($item->expiredate ? date("d/m/Y",$item->expiredate) :'--'));
            $row->cells[] = $itemexpiredatecell;

            $itemactionurl = '/local/authplugin/subscriptions/manageusersubs.php';
            $itemediturl = new \moodle_url($itemactionurl, array('userid' => $item->userid, 'id' => $item->id));
            $itemeditlink = \html_writer::link($itemediturl, get_string('editsub', 'local_authplugin'));
            $itemeditcell = new \html_table_cell($itemeditlink);
            $row->cells[] = $itemeditcell;


            $itemdeleteurl = new \moodle_url($itemactionurl, array('userid' => $item->userid, 'id' => $item->id, 'action' => 'confirmdelete'));
            $itemdeletelink = \html_writer::link($itemdeleteurl, get_string('deletesub', 'local_authplugin'));
            $itemdeletecell = new \html_table_cell($itemdeletelink);
            $row->cells[] = $itemdeletecell;

            $table->data[] = $row;
        }

        return \html_writer::table($table);
    } //end of function


    /*
     * Show the add subscription button
     */
    public function add_sub_link() {
        global $CFG;

        $output = $this->output->heading(get_string("showsubs", "local_authplugin"), 4);
        // $output .= $this->output->heading(get_string("whatdonow", "local_authplugin"), 4);
        $links = array();

        $additemurl = new \moodle_url('/local/authplugin/subscriptions/managesubs.php',array());
        $links[] = \html_writer::link($additemurl, get_string('addnewsub', 'local_authplugin'));

        return $this->output->box($output.'<p>'.implode('</p><p>', $links).'</p>', 'generalbox firstpageoptions');
    }

    /**
     * Return the html table of subscriptions
     * @param array usersub objects
     * @param integer $courseid
     * @return string html of table
     */
    function show_subs_list($items)
    {
        global $DB;

        if (!$items) {
            return $this->output->heading(get_string('nosubs', 'local_authplugin'), 3, 'main');
        }

        $table = new \html_table();
        $table->id = 'local_authplugin_subsitempanel';
        $table->head = array(
            'ID',
            get_string('subscriptionid', 'local_authplugin'),
            get_string('subscriptionname', 'local_authplugin'),
            get_string('apps', 'local_authplugin'),
            get_string('wildcard', 'local_authplugin'),
            get_string('actions', 'local_authplugin')
        );
        $table->headspan = array(1, 1, 1, 1, 2);
        $table->colclasses = array(
            'id','subscriptionid','subscriptionname','apps','wildcard','edit', 'delete'
        );

        //sort by start date
        \core_collator::asort_objects_by_property($items, 'timemodified', \core_collator::SORT_NUMERIC);

        //loop through the items and add to table
        foreach ($items as $item) {
            $row = new \html_table_row();
            $row->cells = array();

            $itemidcell = new \html_table_cell($item->id);
            $row->cells[] = $itemidcell;

            $itemsubidcell = new \html_table_cell($item->subscriptionid);
            $row->cells[] = $itemsubidcell;

            $namecell = new \html_table_cell($item->subscriptionname);
            $row->cells[] = $namecell;

            $appscell = new \html_table_cell($item->apps);
            $row->cells[] = $appscell;

            $wildcardcell = new \html_table_cell($item->wildcard ? get_string('yes'):get_string('no'));
            $row->cells[] = $wildcardcell;

            $itemactionurl = '/local/authplugin/subscriptions/managesubs.php';
            $itemediturl = new \moodle_url($itemactionurl, array('id' => $item->id));
            $itemeditlink = \html_writer::link($itemediturl, get_string('editsub', 'local_authplugin'));
            $itemeditcell = new \html_table_cell($itemeditlink);
            $row->cells[] = $itemeditcell;


            $itemdeleteurl = new \moodle_url($itemactionurl, array('id' => $item->id, 'action' => 'confirmdelete'));
            $itemdeletelink = \html_writer::link($itemdeleteurl, get_string('deletesub', 'local_authplugin'));
            $itemdeletecell = new \html_table_cell($itemdeletelink);
            $row->cells[] = $itemdeletecell;

            $table->data[] = $row;
        }

        return \html_writer::table($table);
    } //end of function


    /*
     * Show the add subscription button
     */
    public function add_app_link() {
        global $CFG;

        $output = $this->output->heading(get_string("showapps", "local_authplugin"), 4);
        // $output .= $this->output->heading(get_string("whatdonow", "local_authplugin"), 4);
        $links = array();

        $additemurl = new \moodle_url('/local/authplugin/subscriptions/manageapps.php',array());
        $links[] = \html_writer::link($additemurl, get_string('addnewapp', 'local_authplugin'));

        return $this->output->box($output.'<p>'.implode('</p><p>', $links).'</p>', 'generalbox firstpageoptions');
    }

    /**
     * Return the html table of subscriptions
     * @param array app objects
     * @param integer $courseid
     * @return string html of table
     */
    function show_apps_list($items)
    {
        global $DB;

        if (!$items) {
            return $this->output->heading(get_string('noapps', 'local_authplugin'), 3, 'main');
        }

        $table = new \html_table();
        $table->id = 'local_authplugin_appsitempanel';
        $table->head = array(
            'ID',
            get_string('appid', 'local_authplugin'),
            get_string('appname', 'local_authplugin'),
            get_string('actions', 'local_authplugin')
        );
        $table->headspan = array(1, 1, 2);
        $table->colclasses = array(
            'id','appid','appname','edit', 'delete'
        );

        //sort by start date
        \core_collator::asort_objects_by_property($items, 'timemodified', \core_collator::SORT_NUMERIC);

        //loop through the items and add to table
        foreach ($items as $item) {
            $row = new \html_table_row();
            $row->cells = array();

            $itemidcell = new \html_table_cell($item->id);
            $row->cells[] = $itemidcell;

            $itemsubidcell = new \html_table_cell($item->appid);
            $row->cells[] = $itemsubidcell;

            $namecell = new \html_table_cell($item->appname);
            $row->cells[] = $namecell;

            $itemactionurl = '/local/authplugin/subscriptions/manageapps.php';
            $itemediturl = new \moodle_url($itemactionurl, array('id' => $item->id));
            $itemeditlink = \html_writer::link($itemediturl, get_string('editapp', 'local_authplugin'));
            $itemeditcell = new \html_table_cell($itemeditlink);
            $row->cells[] = $itemeditcell;


            $itemdeleteurl = new \moodle_url($itemactionurl, array('id' => $item->id, 'action' => 'confirmdelete'));
            $itemdeletelink = \html_writer::link($itemdeleteurl, get_string('deleteapp', 'local_authplugin'));
            $itemdeletecell = new \html_table_cell($itemdeletelink);
            $row->cells[] = $itemdeletecell;

            $table->data[] = $row;
        }

        return \html_writer::table($table);
    } //end of function

}//end of class