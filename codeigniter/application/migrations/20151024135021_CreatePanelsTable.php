<?php
/**
 * Migration: CreateDatabasesTable
 *
 * Created by: Cli for CodeIgniter <https://github.com/kenjis/codeigniter-cli>
 * Created on: 2015/10/31 20:40:39
 */
class Migration_CreatePanelsTable extends CI_Migration {

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
			),
			'database_id' => array(
				'type' =>'INT',
				'constraint' => 10,
			),
			'data' => array(
				'type' =>'LONGTEXT'
			),
			'state' => array(
				'type' =>'TINYINT',
				'constraint' => 4,
			),
			'created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
			'last_update TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
		));
		$this->dbforge->add_key('id', TRUE);
		$this->dbforge->add_key('database_id');
		$this->dbforge->create_table('panels');
	}

	public function down()
	{
		$this->dbforge->drop_table('panels');
	}

}
