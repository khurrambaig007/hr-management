<?php

namespace Khurrambaig\HRManagement\Database\Seeders;

use Illuminate\Database\Seeder;
use Khurrambaig\HRManagement\Models\Department;

class HRDepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            'Human Resources',
            'Information Technology',
            'Finance',
            'Operations',
            'Sales',
            'Marketing',
        ];

        foreach ($departments as $name) {
            Department::updateOrCreate(['name' => $name]);
        }
    }
}