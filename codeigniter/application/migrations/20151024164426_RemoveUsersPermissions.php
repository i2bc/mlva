<?php
/**
 * Migration: RemoveUsersPermissions
 *
 * Created by: Cli for CodeIgniter <https://github.com/kenjis/codeigniter-cli>
 * Created on: 2015/10/24 16:44:26
 */
class Migration_RemoveUsersPermissions extends CI_Migration {

	public function up()
	{
		//Remove the permissions column
		$this->dbforge->drop_column('users', 'permissions');
	}

	public function down()
	{
		//Add the column
		$fields = array(
				'permissions' => array(
					'type' => 'TEXT',
					'null' => TRUE
				)
			);
		$this->dbforge->add_column('users', $fields);
	}

}
