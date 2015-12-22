<?php
/**
 * Migration: CreateUsersTable
 *
 * Created by: Cli for CodeIgniter <https://github.com/kenjis/codeigniter-cli>
 * Created on: 2015/10/24 13:50:23
 */
class Migration_CreateUsersTable extends CI_Migration {

	public function up()
	{
		// Creating a table
		$this->dbforge->add_field(array(
			'id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'auto_increment' => TRUE
			),
			'username' => array(
				'type' =>'VARCHAR',
				'constraint' => 255,
        'unique' => TRUE
			),
			'email' => array(
				'type' => 'VARCHAR',
				'constraint' => 255,
				'unique' => TRUE
			),
			'password' => array(
				'type' => 'VARCHAR',
				'constraint' => 255,
			),
			'permissions' => array(
				'type' => 'TEXT',
				'null' => TRUE,
			),
			'created_at' => array(
				'type' => 'TIMESTAMP',
				'default' => '0000-00-00 00:00:00',
			),
			'last_login' => array(
				'type' => 'TIMESTAMP',
				'default' => '0000-00-00 00:00:00',
			),
		));
		$this->dbforge->add_key('id', TRUE);
		$this->dbforge->create_table('users');

	}

	public function down()
	{
		// Dropping a table
		$this->dbforge->drop_table('users');
	}

}
