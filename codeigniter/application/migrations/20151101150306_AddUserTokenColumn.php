<?php
/**
 * Migration: AddUserTokenColumn
 *
 * Created by: Cli for CodeIgniter <https://github.com/kenjis/codeigniter-cli>
 * Created on: 2015/11/01 15:03:06
 */
class Migration_AddUserTokenColumn extends CI_Migration {

	public function up()
	{
		//Add the column
		$fields = array(
				'token' => array(
					'type' => 'VARCHAR',
					'constraint' => 255,
					'default' => 'user_default_token'
				)
			);
		$this->dbforge->add_column('users', $fields);
	}

	public function down()
	{
		$this->dbforge->drop_column('users', 'token');
	}

}
