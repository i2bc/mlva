<?php
/**
 * Migration: CreateGroupsTable
 *
 * Created by: Cli for CodeIgniter <https://github.com/kenjis/codeigniter-cli>
 * Created on: 2015/10/24 14:24:59
 */
class Migration_CreateGroupsTable extends CI_Migration {

	public function up()
	{
		$this->dbforge->add_field(array(
			'id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'auto_increment' => TRUE
			),
			'name' => array(
				'type' => 'VARCHAR',
				'constraint' => 255,
				'unique' => TRUE
			),
			'label' => array(
				'type' => 'VARCHAR',
				'constraint' => 255,
				'default' => 'info'
			),
			'permissions' => array(
				'type' => 'TEXT',
				'null' => TRUE,
			),
		));
		$this->dbforge->add_key('id', TRUE);
		$this->dbforge->create_table('groups');
	}

	public function down()
	{
		$this->dbforge->drop_table('groups');
	}

}
