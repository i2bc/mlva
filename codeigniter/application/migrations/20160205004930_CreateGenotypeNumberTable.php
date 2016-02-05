<?php
/**
 * Migration: UsersInfos
 *
 * Created by: Cli for CodeIgniter <https://github.com/kenjis/codeigniter-cli>
 * Created on: 2016/02/05 00:49:30
 */
class Migration_CreateGenotypeNumberTable extends CI_Migration {

	public function up() {
		$this->dbforge->add_field(array(
			'panel_id' => array(
				'type' => 'INT',
				'constraint' => 11,
			),
			'value' => array(
				'type' => 'INT',
				'constraint' => 11,
			),
			'state' => array(
				'type' => 'INT',
				'constraint' => 3,
			),
			'data' => array(
				'type' =>'LONGTEXT'
			)
		));
		$this->dbforge->create_table('genotype_number');
	}

	public function down() {
		$this->dbforge->drop_table('genotype_number');
	}

}
