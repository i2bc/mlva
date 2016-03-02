<?php
/**
 * Migration: ChangeGNColumnType
 *
 * Created by: Cli for CodeIgniter <https://github.com/kenjis/codeigniter-cli>
 * Created on: 2016/03/02 11:11:54
 */
class Migration_ChangeGNColumnType extends CI_Migration {

	public function up()
	{
		$fields = [
			'value' => array(
				'type' => 'VARCHAR',
				'constraint' => 255,
			)
		];
		$this->dbforge->modify_column('genotype_number', $fields);
	}

	public function down()
	{
		$fields = [
			'value' => array(
				'type' => 'INT',
				'constraint' => 11,
			)
		];
		$this->dbforge->modify_column('genotype_number', $fields);
	}

}
