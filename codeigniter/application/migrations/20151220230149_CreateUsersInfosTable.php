<?php
/**
 * Migration: UsersInfos
 *
 * Created by: Cli for CodeIgniter <https://github.com/kenjis/codeigniter-cli>
 * Created on: 2015/12/20 23:01:49
 */
class Migration_CreateUsersInfosTable extends CI_Migration {

	public function up()
	{
		$this->dbforge->add_field(array(
			'user_id' => array(
				'type' => 'INT',
				'constraint' => 11,
			),
			'first_name' => array(
				'type' => 'VARCHAR',
				'constraint' => 255,
				'null' => TRUE
			),
			'last_name' => array(
				'type' =>'VARCHAR',
				'constraint' => 255,
				'null' => TRUE
			),
			'bio' => array(
				'type' => 'TEXT',
				'null' => TRUE,
			),
			'birthdate' => array(
				'type' => 'TIMESTAMP',
				'default' => '0000-00-00 00:00:00',
			),
			'website' => array(
				'type' =>'VARCHAR',
				'constraint' => 255,
				'null' => TRUE
			),
			'notifications' => array(
				'type' => 'TINYINT',
				'constraint' => 4,
				'default' => 0
			),
		));
		$this->dbforge->add_key('user_id', TRUE);
		$this->dbforge->create_table('users_infos');
	}

	public function down()
	{
		$this->dbforge->drop_table('users_infos');
	}

}
