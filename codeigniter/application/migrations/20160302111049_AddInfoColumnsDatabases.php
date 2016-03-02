<?php
/**
 * Migration: AddInfoColumnsDatabases
 *
 * Created by: Cli for CodeIgniter <https://github.com/kenjis/codeigniter-cli>
 * Created on: 2016/03/02 11:10:49
 */
class Migration_AddInfoColumnsDatabases extends CI_Migration {

	public function up()
	{
		$fields = array(
			'description' => array('type' => 'TEXT'),
			'website' => array(
				'type' => 'VARCHAR',
				'constraint' => 255
			)
		);
		$this->dbforge->add_column('databases', $fields);
	}

	public function down()
	{
		$this->dbforge->drop_column('databases', ['description', 'website']);
	}

}
