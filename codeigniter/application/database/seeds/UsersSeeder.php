<?php

class UsersSeeder extends Seeder {

    public function run()
    {
        $this->db->truncate('users');

        /*$data = [
            'id' => 0,
            'first_name' => 'Ghost',
            'last_name' => 'User',
            'username' => '',
            'password' => 'GhostUser',
            'email' => '',
        ];
        */
        //$this->db->insert('users', $data);

        $data = [
            'id' => 1,
            'username' => 'jon',
            'password' => '$2y$10$6/q.yfu9QHcStu5JPLb8teT6lIF0F8z50gts8yvO7Hcz23QAhFGpi',//="test"
            'email' => 'jon@ensta.fr',
        ];

        $this->db->insert('users', $data);

        $data = [
            'user_id' => 1,
            'first_name' => 'Jon',
            'last_name' => 'Snow',
            'bio' => 'Hello my name is jon',
            'notifications' => '1',
        ];

        $this->db->insert('users_infos', $data);

        $data = [
            'id' => 2,
            'username' => 'john',
            'password' => '$2y$10$6/q.yfu9QHcStu5JPLb8teT6lIF0F8z50gts8yvO7Hcz23QAhFGpi',
            'email' => 'jhon@ensta.fr',
        ];

        $this->db->insert('users', $data);

        $data = [
            'user_id' => 2,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'bio' => 'Hello my name is john',
            'notifications' => '0',
        ];

        $this->db->insert('users_infos', $data);

        $data = [
            'id' => 3,
            'username' => 'brendan',
            'password' => '$2y$10$6/q.yfu9QHcStu5JPLb8teT6lIF0F8z50gts8yvO7Hcz23QAhFGpi',
            'email' => 'daoud@ensta.fr',
        ];

        $this->db->insert('users', $data);

        $data = [
            'user_id' => 3,
            'first_name' => 'Brendan',
            'last_name' => 'Daoud',
            'bio' => 'Hello my name is ?',
            'notifications' => '1',
        ];

        $this->db->insert('users_infos', $data);

        $data = [
            'id' => 4,
            'username' => 'antonin',
            'password' => '$2y$10$6/q.yfu9QHcStu5JPLb8teT6lIF0F8z50gts8yvO7Hcz23QAhFGpi',
            'email' => 'antonin.raffin@ensta-paristech.fr',
        ];

        $this->db->insert('users', $data);

        $data = [
            'user_id' => 4,
            'first_name' => 'Antonin',
            'last_name' => 'Raffin',
            'bio' => 'Hello my name is Antonin',
            'notifications' => '1',
        ];

        $this->db->insert('users_infos', $data);
    }
}
