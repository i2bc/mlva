<?php

class UsersSeeder extends Seeder {

    public function run()
    {
        $this->db->truncate('users');

        $data = [
            'id' => 0,
            'first_name' => 'Ghost',
            'last_name' => 'User',
            'username' => '',
            'password' => 'GhostUser',
            'email' => '',
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $data = [
            'id' => 1,
            'first_name' => 'Jon',
            'last_name' => 'Snow',
            'username' => 'jon',
            'password' => '$2y$10$6/q.yfu9QHcStu5JPLb8teT6lIF0F8z50gts8yvO7Hcz23QAhFGpi',//="test"
            'email' => 'jon@ensta.fr',
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $this->db->insert('users', $data);

        $data = [
            'id' => 2,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'username' => 'john',
            'password' => '$2y$10$6/q.yfu9QHcStu5JPLb8teT6lIF0F8z50gts8yvO7Hcz23QAhFGpi',
            'email' => 'jhon@ensta.fr',
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $this->db->insert('users', $data);

        $data = [
            'id' => 3,
            'first_name' => 'Brendan',
            'last_name' => 'Daoud',
            'username' => 'brendan',
            'password' => '$2y$10$6/q.yfu9QHcStu5JPLb8teT6lIF0F8z50gts8yvO7Hcz23QAhFGpi',
            'email' => 'daoud@ensta.fr',
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $this->db->insert('users', $data);

        $data = [
            'id' => 4,
            'first_name' => 'Antonin',
            'last_name' => 'Raffin',
            'username' => 'antonin',
            'password' => '$2y$10$6/q.yfu9QHcStu5JPLb8teT6lIF0F8z50gts8yvO7Hcz23QAhFGpi',
            'email' => 'antonin.raffin@ensta-paristech.fr',
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $this->db->insert('users', $data);
    }
}
