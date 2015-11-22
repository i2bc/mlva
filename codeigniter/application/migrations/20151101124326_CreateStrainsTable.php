<?php
/**
 * Migration: CreateStrainsTable
 *
 * Created by: Cli for CodeIgniter <https://github.com/kenjis/codeigniter-cli>
 * Created on: 2015/11/01 12:43:26
 */
class Migration_CreateStrainsTable extends CI_Migration {

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
			'metadata' => array(
				'type' =>'TEXT'
			),
			'data' => array(
				'type' =>'LONGTEXT'
			)
		));
		$this->dbforge->add_key('id', TRUE);
		$this->dbforge->add_key('database_id');
		$this->dbforge->create_table('strains');
	}

	public function down()
	{
		$this->dbforge->drop_table('strains');
	}

}
