<?php
/**
 * Migration: CreateDatabasesTable
 *
 * Created by: Cli for CodeIgniter <https://github.com/kenjis/codeigniter-cli>
 * Created on: 2015/10/31 20:40:39
 */
class Migration_CreateDatabasesTable extends CI_Migration {

	public function up()
	{
		/*

		CREATE TABLE IF NOT EXISTS `databases` (
		  `metadatas` text NOT NULL,
		  `datas` text NOT NULL,
		  `state` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'public',
		  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
		  `last_udpate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
		  PRIMARY KEY (`id`),
		  KEY `user_id` (`user_id`,`group_id`)
		) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;*/
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
			'user_id' => array(
				'type' =>'INT',
				'constraint' => 10,
			),
			'group_id' => array(
				'type' =>'INT',
				'constraint' => 10,
			),
			'marker_num' => array(
				'type' =>'INT',
				'constraint' => 11,
			),
			'metadatas' => array(
				'type' =>'TEXT'
			),
			'datas' => array(
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
		$this->dbforge->create_table('databases');
	}

	public function down()
	{
		$this->dbforge->drop_table('databases');
	}

}
