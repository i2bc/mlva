<?php

class GroupsSeeder extends Seeder {

    public function run()
    {
        $this->db->truncate('groups');

        $data = [
            'id' => 1,
            'name' => 'Admin',
            'permissions' => '{"admin":1}',
        ];

        $this->db->insert('groups', $data);

        $data = [
            'id' => 2,
            'name' => 'User',
            'permissions' => '{"databases.show":1, "databases.create":1}',
        ];

        $this->db->insert('groups', $data);

        $data = [
            'id' => 3,
            'name' => 'Guest',
            'permissions' => '{"databases.show":0}',
        ];

        $this->db->insert('groups', $data);

        $this->db->truncate('user_has_group');

        $data = [
            'user_id' => 3,
            'group_id' => 1,
        ];

        $this->db->insert('user_has_group', $data);

        $data = [
            'user_id' => 4,
            'group_id' => 1,
        ];

        $this->db->insert('user_has_group', $data);

        $data = [
            'user_id' => 1,
            'group_id' => 2,
        ];

        $this->db->insert('user_has_group', $data);

        $data = [
            'user_id' => 2,
            'group_id' => 2,
        ];

        $this->db->insert('user_has_group', $data);

        $data = [
            'user_id' => 3,
            'group_id' => 2,
        ];

        $this->db->insert('user_has_group', $data);

        $data = [
            'user_id' => 4,
            'group_id' => 2,
        ];

        $this->db->insert('user_has_group', $data);
    }
}
