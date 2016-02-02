<?php

class PanelsSeeder extends Seeder {

    public function run()
    {
        $this->db->truncate('panels');

        $data = [
            'id' => 1,
            'name' => 'test',
            'database_id' => 1,
            'data' => '["Bruce00-1322","Bruce12-73"]',
            'state' => 0,
            'created_at' => '2016-02-02 17:58:47',
            'last_update' => '2016-02-02 17:58:47',
        ];

      $this->db->insert('panels', $data);
    }
}
