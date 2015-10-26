<?php

class GroupsSeeder extends Seeder {

    public function run()
    {
        $this->db->truncate('groups');

        $data = [
            'id' => 1,
            'name' => 'Admin',
            'permissions' => '{"admin":1}',
            'label' => 'primary',
        ];

        $this->db->insert('groups', $data);

        $data = [
            'id' => 2,
            'name' => 'Modérateur',
            'permissions' => '{"moderator":1, "comments.edit":1, "comments.delete":1}',
        ];

        $this->db->insert('groups', $data);

        $data = [
            'id' => 3,
            'name' => 'Rédacteur',
            'permissions' => '{"news.create":1, "news.edit":1}',
        ];

        $this->db->insert('groups', $data);

        $data = [
            'id' => 4,
            'name' => 'User',
            'permissions' => '{"videos.create":1, "comments.create":1}',
        ];

        $this->db->insert('groups', $data);

        $data = [
            'id' => 5,
            'name' => 'Guest',
            'permissions' => '{"databases.show":0}',
        ];
        $data = [
            'id' => 6,
            'name' => 'Validateur',
            'permissions' => '{"videos.edit":1, "videos.moderate":1}',
        ];

        $this->db->insert('groups', $data);

        $this->db->truncate('user_has_group');

        $data = [
            'user_id' => 0,
            'group_id' => 5,
        ];

        $this->db->insert('user_has_group', $data);

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
            'user_id' => 1,
            'group_id' => 6,
        ];

        $this->db->insert('user_has_group', $data);

        $data = [
            'user_id' => 2,
            'group_id' => 3,
        ];

        $this->db->insert('user_has_group', $data);

        $data = [
            'user_id' => 3,
            'group_id' => 4,
        ];

        $this->db->insert('user_has_group', $data);

        $data = [
            'user_id' => 4,
            'group_id' => 4,
        ];

        $this->db->insert('user_has_group', $data);
    }
}
