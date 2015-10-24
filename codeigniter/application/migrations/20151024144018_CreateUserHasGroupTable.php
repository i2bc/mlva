<?php
/**
 * Migration: CreateUser_has_groupTable
 *
 * Created by: Cli for CodeIgniter <https://github.com/kenjis/codeigniter-cli>
 * Created on: 2015/10/24 14:40:18
 */
class Migration_CreateUserHasGroupTable extends CI_Migration {

	public function up()
	{
		$this->dbforge->add_field(array(
			'user_id' => array(
				'type' => 'INT',
				'constraint' => 11,
			),
			'group_id' => array(
				'type' => 'INT',
				'constraint' => 11,
			),
		));
		$this->dbforge->create_table('user_has_group');

	}

	public function down()
	{
		$this->dbforge->drop_table('user_has_group');
	}

}
