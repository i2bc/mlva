<?php
/**
 * Migration: AddDescGroupColumn
 *
 * Created by: Cli for CodeIgniter <https://github.com/kenjis/codeigniter-cli>
 * Created on: 2016/02/15 15:12:19
 */
class Migration_AddDescGroupColumn extends CI_Migration {

	public function up()
	{
		$fields = array(
			'description' => array('type' => 'TEXT')
		);
		$this->dbforge->add_column('groups', $fields);
	}

	public function down()
	{
		// Dropping a Column From a Table
		$this->dbforge->drop_column('groups', 'description');
	}

}
