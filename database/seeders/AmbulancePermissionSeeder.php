// database/seeders/AmbulancePermissionSeeder.php
Permission::firstOrCreate(['name'=>'ambulance.master']);
Permission::firstOrCreate(['name'=>'ambulance.dispatch']);
Permission::firstOrCreate(['name'=>'ambulance.er']);
Permission::firstOrCreate(['name'=>'ambulance.billing']);
Permission::firstOrCreate(['name'=>'ambulance.audit']);
