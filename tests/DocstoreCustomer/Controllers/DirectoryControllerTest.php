<?php

namespace Tests\DocstoreCustomer\Controllers;

/*
 * Copyright (C) 2009 - 2020 Internet Neutral Exchange Association Company Limited By Guarantee.
 * All Rights Reserved.
 *
 * This file is part of IXP Manager.
 *
 * IXP Manager is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the Free
 * Software Foundation, version v2.0 of the License.
 *
 * IXP Manager is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for
 * more details.
 *
 * You should have received a copy of the GNU General Public License v2.0
 * along with IXP Manager.  If not, see:
 *
 * http://www.gnu.org/licenses/gpl-2.0.html
 */

use D2EM;

use Entities\User as UserEntity;

use Illuminate\Foundation\Testing\WithoutMiddleware;

use IXP\Models\Customer;
use IXP\Models\DocstoreCustomerDirectory;

use Tests\TestCase;

class DirectoryControllerTest extends TestCase
{

    const testInfo = [
        'custuser'              => 'hecustuser',
        'custadmin'             => 'hecustadmin',
        'superuser'             => 'travis',
        'customerId'            => 5,

        'folderName'            => 'Folder 4',
        'folderDescription'     => 'This is the folder 4',
        'parentDirId'           => null,
        'folderName2'           => 'Folder 4-1',
        'folderDescription2'    => 'This is the folder 4-1',
        'parentDirId2'          => 1,
    ];

    /**
     * Test the access to the list for public user
     *
     * @return void
     */
    public function testListCustomerForPublicUser()
    {
        $response = $this->get( route('docstore-c-dir@customers', [ 'cust' => self::testInfo[ 'customerId' ] ] ) );
        $response->assertStatus(302 )
            ->assertRedirect( route('login@showForm' ) );
    }

    /**
     * Test the access to the list forcust user
     *
     * @return void
     */
    public function testListCustomerForCustUser()
    {
        $user = D2EM::getRepository( UserEntity::class )->findOneBy( [  'username' => self::testInfo[ 'custuser' ] ] );
        $response = $this->actingAs( $user )->get( route('docstore-c-dir@customers', [ 'cust' => self::testInfo[ 'customerId' ] ] ) );
        $response->assertStatus(403 );
    }

    /**
     * Test the access to the list forcust user
     *
     * @return void
     */
    public function testListCustomerForCustAdmin()
    {
        $user = D2EM::getRepository( UserEntity::class )->findOneBy( [  'username' => self::testInfo[ 'custadmin' ] ] );
        $response = $this->actingAs( $user )->get( route('docstore-c-dir@customers', [ 'cust' => self::testInfo[ 'customerId' ] ] ) );
        $response->assertStatus(403 );
    }

    /**
     * Test the access to the list forcust user
     *
     * @return void
     */
    public function testListCustomerFoSuperUser()
    {
        $user = D2EM::getRepository( UserEntity::class )->findOneBy( [  'username' => self::testInfo[ 'superuser' ] ] );
        $response = $this->actingAs( $user )->get( route('docstore-c-dir@customers', [ 'cust' => self::testInfo[ 'customerId' ] ] ) );
        $response->assertStatus(200 )
            ->assertViewIs( 'docstore-customer.dir.customers' )
            ->assertSee( 'HEAnet' )
            ->assertSee( 'Imagine' )
            ->assertSee( 'AS112' );
    }

    /**
     * Test the access to the list for public user
     *
     * @return void
     */
    public function testListForPublicUser()
    {
        $response = $this->get( route('docstore-c-dir@list', [ 'cust' => self::testInfo[ 'customerId' ] ] ) );
        $response->assertStatus(302 )
                ->assertRedirect( route('login@showForm' ) );
    }

    /**
     * Test the access to the create form for a public user
     *
     * @return void
     */
    public function testCreateFormAccessPublicUser()
    {
        // public user
        $response = $this->get( route( 'docstore-c-dir@create', [ 'cust' => self::testInfo[ 'customerId' ] ] ) );
        $response->assertStatus(302 )
            ->assertRedirect( route('login@showForm' ) );
    }

    /**
     * Test the access to the create form for a custuser
     *
     * @return void
     */
    public function testCreateFormAccessCustUser()
    {
        // test custuser
        $user = D2EM::getRepository( UserEntity::class )->findOneBy( [  'username' => self::testInfo[ 'custuser' ] ] );
        $response = $this->actingAs( $user )->get( route( 'docstore-c-dir@create', [ 'cust' => self::testInfo[ 'customerId' ] ] ) );
        $response->assertStatus(403 );
    }

    /**
     * Test the access to the create form for a custadmin
     *
     * @return void
     */
    public function testCreateFormAccessCustAdmin()
    {
        // test custadmin
        $user = D2EM::getRepository( UserEntity::class )->findOneBy( [  'username' => self::testInfo[ 'custadmin' ] ] );
        $response = $this->actingAs( $user )->get( route( 'docstore-c-dir@create', [ 'cust' => self::testInfo[ 'customerId' ] ] ) );
        $response->assertStatus(403 );
    }

    /**
     * Test the access to the create form for a superuser
     *
     * @return void
     */
    public function testCreateFormAccessSuperUser()
    {
        // test Superuser
        $user = D2EM::getRepository( UserEntity::class )->findOneBy( [  'username' => self::testInfo[ 'superuser' ] ] );
        $response = $this->actingAs( $user )->get( route( 'docstore-c-dir@create', [ 'cust' => self::testInfo[ 'customerId' ] ] ) );
        $response->assertOk()
            ->assertViewIs('docstore-customer.dir.create' );
    }


    /**
     * Test to store an object for a public user
     *
     * @return void
     */
    public function testStorePublicUser()
    {
        // public user
        $response = $this->post( route( 'docstore-c-dir@store', [ 'cust' => self::testInfo[ 'customerId' ] ] ), [  'cust_id' => self::testInfo[ 'customerId' ], 'name' =>  self::testInfo[ 'folderName' ], 'description' => self::testInfo[ 'folderDescription' ], 'parent_dir' => self::testInfo[ 'parentDirId' ] ] );
        $response->assertStatus(302 )
            ->assertRedirect( route('login@showForm' ) );
        $this->assertDatabaseMissing( 'docstore_customer_directories', [ 'cust_id' => self::testInfo[ 'customerId' ], 'name' =>  self::testInfo[ 'folderName' ], 'description' => self::testInfo[ 'folderDescription' ], 'parent_dir_id' => self::testInfo[ 'parentDirId' ] ] );
    }

    /**
     * Test store an object for a cust user
     *
     * @return void
     */
    public function testStoreCustUser()
    {
        // test custuser
        $user = D2EM::getRepository( UserEntity::class )->findOneBy( [  'username' => self::testInfo[ 'custuser' ] ] );
        $response = $this->actingAs( $user )->post( route( 'docstore-c-dir@store', [ 'cust' => self::testInfo[ 'customerId' ] ] ), [  'cust_id' => self::testInfo[ 'customerId' ], 'name' =>  self::testInfo[ 'folderName' ], 'description' => self::testInfo[ 'folderDescription' ], 'parent_dir' => self::testInfo[ 'parentDirId' ] ] );
        $response->assertStatus(403 );
        $this->assertDatabaseMissing( 'docstore_customer_directories', [ 'cust_id' => self::testInfo[ 'customerId' ], 'name' =>  self::testInfo[ 'folderName' ], 'description' => self::testInfo[ 'folderDescription' ], 'parent_dir_id' => self::testInfo[ 'parentDirId' ] ] );
    }

    /**
     * Test store an object for a cust admin
     *
     * @return void
     */
    public function testStoreCustAdmin()
    {
        // test custadmin
        $user = D2EM::getRepository( UserEntity::class )->findOneBy( [  'username' => self::testInfo[ 'custadmin' ] ] );
        $response = $this->actingAs( $user )->post( route( 'docstore-c-dir@store', [ 'cust' => self::testInfo[ 'customerId' ] ] ), [  'cust_id' => self::testInfo[ 'customerId' ], 'name' =>  self::testInfo[ 'folderName' ], 'description' => self::testInfo[ 'folderDescription' ], 'parent_dir' => self::testInfo[ 'parentDirId' ] ] );
        $response->assertStatus(403 );
        $this->assertDatabaseMissing( 'docstore_customer_directories', [ 'cust_id' => self::testInfo[ 'customerId' ], 'name' =>  self::testInfo[ 'folderName' ], 'description' => self::testInfo[ 'folderDescription' ], 'parent_dir_id' => self::testInfo[ 'parentDirId' ] ] );

    }

    /**
     * Test store an object for a superuser
     *
     * @return void
     */
    public function testStoreSuperUser()
    {
        // test Superuser
        $user = D2EM::getRepository( UserEntity::class )->findOneBy( [  'username' => self::testInfo[ 'superuser' ] ] );
        $this->actingAs( $user )->post( route( 'docstore-c-dir@store', [ 'cust' => self::testInfo[ 'customerId' ] ] ), [  'cust_id' => self::testInfo[ 'customerId' ], 'name' =>  self::testInfo[ 'folderName' ], 'description' => self::testInfo[ 'folderDescription' ], 'parent_dir' => self::testInfo[ 'parentDirId' ] ] );
        $this->assertDatabaseHas( 'docstore_customer_directories', [ 'cust_id' => self::testInfo[ 'customerId' ], 'name' =>  self::testInfo[ 'folderName' ], 'description' => self::testInfo[ 'folderDescription' ], 'parent_dir_id' => self::testInfo[ 'parentDirId' ] ] );
    }


    /**
     * Test the access to the edit form for a public user
     *
     * @return void
     */
    public function testEditFormAccessPublicUser()
    {
        $dir = DocstoreCustomerDirectory::withoutGlobalScope( 'privs' )->where( 'name', self::testInfo[ 'folderName' ] )->first();

        // public user
        $response = $this->get( route( 'docstore-c-dir@edit', [ 'cust' => $dir->cust_id  , 'dir' => $dir ] ) );
        $response->assertStatus(302 )
            ->assertRedirect( route('login@showForm' ) );
    }

    /**
     * Test the access to the edit form for a custuser
     *
     * @return void
     */
    public function testEditFormAccessCustUser()
    {
        $dir = DocstoreCustomerDirectory::withoutGlobalScope( 'privs' )->where( 'name', self::testInfo[ 'folderName' ] )->first();

        // test custuser
        $user = D2EM::getRepository( UserEntity::class )->findOneBy( [  'username' => self::testInfo[ 'custuser' ]  ] );
        $response = $this->actingAs( $user )->get( route( 'docstore-c-dir@edit', [ 'cust' => $dir->cust_id  ,'dir' => $dir ] ) );
        $response->assertStatus(404 );
    }

    /**
     * Test the access to the edit form for a custadmin
     *
     * @return void
     */
    public function testEditFormAccessCustAdmin()
    {
        $dir = DocstoreCustomerDirectory::withoutGlobalScope( 'privs' )->where( 'name', self::testInfo[ 'folderName' ] )->first();

        // test custadmin
        $user = D2EM::getRepository( UserEntity::class )->findOneBy( [  'username' => self::testInfo[ 'custadmin' ]  ] );
        $response = $this->actingAs( $user )->get( route( 'docstore-c-dir@edit', [ 'cust' => $dir->cust_id  , 'dir' => $dir ] ) );
        $response->assertStatus(404 );
    }

    /**
     * Test the access to the edit form for a superuser
     *
     * @return void
     */
    public function testEditFormAccessSuperUser()
    {
        $dir = DocstoreCustomerDirectory::withoutGlobalScope( 'privs' )->where( 'name', self::testInfo[ 'folderName' ] )->first();

        // test Superuser
        $user = D2EM::getRepository( UserEntity::class )->findOneBy( [  'username' => self::testInfo[ 'superuser' ] ] );
        $response = $this->actingAs( $user )->get( route( 'docstore-c-dir@edit', [ 'cust' => $dir->cust_id  , 'dir' => $dir ] ) );
        $response->assertOk()
            ->assertViewIs('docstore-customer.dir.create' );
    }



    /**
     * Test update an object with a post method
     *
     * @return void
     */
    public function testUpdateWithPostMethod()
    {
        $dir = DocstoreCustomerDirectory::withoutGlobalScope( 'privs' )->where( [ 'name' => self::testInfo[ 'folderName' ] ] )->first();

        // public user
        $response = $this->post( route( 'docstore-c-dir@update', [ 'cust' => $dir->cust_id, 'dir' => $dir ] ), [ 'name' =>  self::testInfo[ 'folderName2' ], 'description' => self::testInfo[ 'folderDescription2' ], 'parent_dir' => self::testInfo[ 'parentDirId2' ] ] );
        $response->assertStatus(405 );
    }

    /**
     * Test update an object for a public user
     *
     * @return void
     */
    public function testUpdatePublicUser()
    {
        $dir = DocstoreCustomerDirectory::withoutGlobalScope( 'privs' )->where( [ 'name' => self::testInfo[ 'folderName' ] ] )->first();

        // public user
        $response = $this->put( route( 'docstore-c-dir@update', [ 'cust' => $dir->cust_id, 'dir' => $dir ] ), [ 'name' =>  self::testInfo[ 'folderName2' ], 'description' => self::testInfo[ 'folderDescription2' ], 'parent_dir' => self::testInfo[ 'parentDirId2' ] ] );
        $response->assertStatus(302 )
            ->assertRedirect( route('login@showForm' ) );
        $this->assertDatabaseHas(       'docstore_customer_directories', [ 'cust_id' => $dir->cust_id, 'name' => self::testInfo[ 'folderName' ],     'description' => self::testInfo[ 'folderDescription' ],     'parent_dir_id' => self::testInfo[ 'parentDirId' ] ] );
        $this->assertDatabaseMissing(   'docstore_customer_directories', [ 'cust_id' => $dir->cust_id, 'name' => self::testInfo[ 'folderName2' ],    'description' => self::testInfo[ 'folderDescription2' ],    'parent_dir_id' => self::testInfo[ 'parentDirId2' ] ] );
    }

    /**
     * Test update an object for a cust user
     *
     * @return void
     */
    public function testUpdateCustUser()
    {
        $dir = DocstoreCustomerDirectory::withoutGlobalScope( 'privs' )->where( [ 'name' => self::testInfo[ 'folderName' ] ] )->first();

        // cust user
        $user = D2EM::getRepository( UserEntity::class )->findOneBy( [  'username' => self::testInfo[ 'custuser' ] ] );
        $response = $this->actingAs( $user )->put( route( 'docstore-c-dir@update', [ 'cust' => $dir->cust_id, 'dir' => $dir ] ), [ 'name' =>  self::testInfo[ 'folderName2' ], 'description' => self::testInfo[ 'folderDescription2' ], 'parent_dir' => self::testInfo[ 'parentDirId2' ] ] );
        $response->assertStatus(404 );
        $this->assertDatabaseHas(       'docstore_customer_directories', [ 'cust_id' => $dir->cust_id, 'name' => self::testInfo[ 'folderName' ],     'description' => self::testInfo[ 'folderDescription' ],     'parent_dir_id' => self::testInfo[ 'parentDirId' ]  ] );
        $this->assertDatabaseMissing(   'docstore_customer_directories', [ 'cust_id' => $dir->cust_id, 'name' => self::testInfo[ 'folderName2' ],    'description' => self::testInfo[ 'folderDescription2' ],    'parent_dir_id' => self::testInfo[ 'parentDirId2' ] ] );
    }

    /**
     * Test update an object for a cust admin
     *
     * @return void
     */
    public function testUpdateCustAdmin()
    {
        $dir = DocstoreCustomerDirectory::withoutGlobalScope( 'privs' )->where( [ 'name' => self::testInfo[ 'folderName' ] ] )->first();

        // cust admin
        $user = D2EM::getRepository( UserEntity::class )->findOneBy( [  'username' => self::testInfo[ 'custadmin' ] ] );
        $response = $this->actingAs( $user )->put( route( 'docstore-c-dir@update', [  'cust' => $dir->cust_id, 'dir' => $dir ] ), [ 'name' =>  self::testInfo[ 'folderName2' ], 'description' => self::testInfo[ 'folderDescription2' ], 'parent_dir' => self::testInfo[ 'parentDirId2' ] ] );
        $response->assertStatus(404 );
        $this->assertDatabaseHas(       'docstore_customer_directories', [ 'cust_id' => $dir->cust_id, 'name' => self::testInfo[ 'folderName' ],     'description' => self::testInfo[ 'folderDescription' ],     'parent_dir_id' => self::testInfo[ 'parentDirId' ]  ] );
        $this->assertDatabaseMissing(   'docstore_customer_directories', [ 'cust_id' => $dir->cust_id, 'name' => self::testInfo[ 'folderName2' ],    'description' => self::testInfo[ 'folderDescription2' ],    'parent_dir_id' => self::testInfo[ 'parentDirId2' ] ] );
    }

    /**
     * Test update an object for a superuser
     *
     * @return void
     */
    public function testUpdateSuperUser()
    {
        $dir = DocstoreCustomerDirectory::withoutGlobalScope( 'privs' )->where( [ 'name' => self::testInfo[ 'folderName' ] ] )->first();

        // superuser
        $user = D2EM::getRepository( UserEntity::class )->findOneBy( [  'username' => self::testInfo[ 'superuser' ] ] );

        $this->actingAs( $user )->put( route( 'docstore-c-dir@update', [ 'cust' => $dir->cust_id , 'dir' => $dir ] ), [ 'cust_id' => $dir->cust_id , 'name' =>  self::testInfo[ 'folderName2' ], 'description' => self::testInfo[ 'folderDescription2' ], 'parent_dir' => self::testInfo[ 'parentDirId2' ] ] );
        $this->assertDatabaseMissing(   'docstore_customer_directories', [ 'name' => self::testInfo[ 'folderName' ],     'description' => self::testInfo[ 'folderDescription' ],     'parent_dir_id' => self::testInfo[ 'parentDirId' ]  ] );
        $this->assertDatabaseHas(       'docstore_customer_directories', [ 'name' => self::testInfo[ 'folderName2' ],    'description' => self::testInfo[ 'folderDescription2' ],    'parent_dir_id' => self::testInfo[ 'parentDirId2' ] ] );
    }

    /**
     * Test delete an object with a post method
     *
     * @return void
     */
    public function testDeleteWithPostMethod()
    {
        $dir = DocstoreCustomerDirectory::withoutGlobalScope( 'privs' )->where( [ 'name' => self::testInfo[ 'folderName2' ] ] )->first();

        // public user
        $response = $this->post( route( 'docstore-c-dir@delete', [ 'dir' => $dir ] ) );
        $response->assertStatus(405 );
    }

    /**
     * Test delete an object for a public user
     *
     * @return void
     */
    public function testDeleteForPublicUser()
    {
        $dir = DocstoreCustomerDirectory::withoutGlobalScope( 'privs' )->where( [ 'name' => self::testInfo[ 'folderName2' ] ] )->first();

        // public user
        $response = $this->delete( route( 'docstore-c-dir@delete', [ 'dir' => $dir ] ) );
        $response->assertStatus(302 )
            ->assertRedirect( route('login@showForm' ) );
        $this->assertDatabaseHas( 'docstore_customer_directories', [ 'name' => self::testInfo[ 'folderName2' ],    'description' => self::testInfo[ 'folderDescription2' ], 'parent_dir_id' => self::testInfo[ 'parentDirId2' ] ] );
    }

    /**
     * Test delete an object for a cust user
     *
     * @return void
     */
    public function testDeleteCustUser()
    {
        $dir = DocstoreCustomerDirectory::withoutGlobalScope( 'privs' )->where( [ 'name' => self::testInfo[ 'folderName2' ] ] )->first();

        // cust user
        $user = D2EM::getRepository( UserEntity::class )->findOneBy( [  'username' => self::testInfo[ 'custuser' ] ] );
        $response = $this->actingAs( $user )->delete( route( 'docstore-c-dir@delete', [ 'dir' => $dir ] ) );
        $response->assertStatus(404 );
        $this->assertDatabaseHas( 'docstore_customer_directories', [ 'name' => self::testInfo[ 'folderName2' ],    'description' => self::testInfo[ 'folderDescription2' ], 'parent_dir_id' => self::testInfo[ 'parentDirId2' ] ] );
    }

    /**
     * Test delete an object for a cust admin
     *
     * @return void
     */
    public function testDeleteCustAdmin()
    {
        $dir = DocstoreCustomerDirectory::withoutGlobalScope( 'privs' )->where( [ 'name' => self::testInfo[ 'folderName2' ] ] )->first();

        // cust user
        $user = D2EM::getRepository( UserEntity::class )->findOneBy( [  'username' => self::testInfo[ 'custadmin' ] ] );
        $response = $this->actingAs( $user )->delete( route( 'docstore-c-dir@delete', [ 'dir' => $dir ] ) );
        $response->assertStatus(404 );
        $this->assertDatabaseHas( 'docstore_customer_directories', [ 'name' => self::testInfo[ 'folderName2' ],    'description' => self::testInfo[ 'folderDescription2' ], 'parent_dir_id' => self::testInfo[ 'parentDirId2' ] ] );
    }

    /**
     * Test delete an object for a superuser
     *
     * @return void
     */
    public function testDeleteSuperUser()
    {
        $dir = DocstoreCustomerDirectory::withoutGlobalScope( 'privs' )->where( [ 'name' => self::testInfo[ 'folderName2' ] ] )->first();

        // superuser
        $user = D2EM::getRepository( UserEntity::class )->findOneBy( [  'username' => self::testInfo[ 'superuser' ] ] );
        $this->actingAs( $user )->delete( route( 'docstore-c-dir@delete', [ 'dir' => $dir ] ) );
        $this->assertDatabaseMissing( 'docstore_customer_directories', [ 'name' => self::testInfo[ 'folderName2' ],    'description' => self::testInfo[ 'folderDescription2' ], 'parent_dir_id' => self::testInfo[ 'parentDirId2' ] ] );
    }

    /**
     * Test delete an object for a public user
     *
     * @return void
     */
    public function testDeleteAllForPublicUser()
    {
        $cust = Customer::whereId( self::testInfo[ 'customerId' ] )->first();

        $dir = DocstoreCustomerDirectory::withoutGlobalScope( 'privs' )->where( 'cust_id', $cust->id )->get()->first();
        // public user
        $response = $this->delete( route( 'docstore-c-dir@delete-for-customer', [ 'cust' => $cust ] ) );
        $response->assertStatus(302 )
            ->assertRedirect( route('login@showForm' ) );
        $this->assertDatabaseHas( 'docstore_customer_directories', [ 'name' => $dir->name,    'description' => $dir->description ] );
    }

    /**
     * Test delete an object for a cust user
     *
     * @return void
     */
    public function testDeleteAllForCustUser()
    {
        $cust = Customer::whereId( self::testInfo[ 'customerId' ] )->first();

        $dir = DocstoreCustomerDirectory::withoutGlobalScope( 'privs' )->where( 'cust_id', $cust->id )->get()->first();

        // cust user
        $user = D2EM::getRepository( UserEntity::class )->findOneBy( [  'username' => self::testInfo[ 'custuser' ] ] );
        $response = $this->actingAs( $user )->delete( route( 'docstore-c-dir@delete-for-customer', [ 'cust' => $cust ] ) );
        $response->assertStatus(403 );
        $this->assertDatabaseHas( 'docstore_customer_directories', [ 'name' => $dir->name,    'description' => $dir->description ] );
    }

    /**
     * Test delete an object for a cust admin
     *
     * @return void
     */
    public function testDeleteAllForCustAdmin()
    {
        $cust = Customer::whereId( self::testInfo[ 'customerId' ] )->first();

        $dir = DocstoreCustomerDirectory::withoutGlobalScope( 'privs' )->where( 'cust_id', $cust->id )->get()->first();
        // cust user
        $user = D2EM::getRepository( UserEntity::class )->findOneBy( [  'username' => self::testInfo[ 'custadmin' ] ] );
        $response = $this->actingAs( $user )->delete( route( 'docstore-c-dir@delete-for-customer', [ 'cust' => $cust ] ) );
        $response->assertStatus(403 );
        $this->assertDatabaseHas( 'docstore_customer_directories', [ 'name' => $dir->name,    'description' => $dir->description, 'parent_dir_id' => $dir->parent_dir ] );
    }

    /**
     * Test delete an object for a superuser
     *
     * @return void
     */
    public function testDeleteAllForSuperUser()
    {
        $cust = Customer::whereId( self::testInfo[ 'customerId' ] )->first();

        $dir = DocstoreCustomerDirectory::withoutGlobalScope( 'privs' )->where( 'cust_id', $cust->id )->get()->first();
        // superuser
        $user = D2EM::getRepository( UserEntity::class )->findOneBy( [  'username' => self::testInfo[ 'superuser' ] ] );
        $this->actingAs( $user )->delete( route( 'docstore-c-dir@delete-for-customer', [ 'cust' => $cust ] ) );
        $this->assertDatabaseMissing( 'docstore_customer_directories', [ 'name' => $dir->name,    'description' => $dir->description, 'parent_dir_id' => $dir->parent_dir ] );
    }
}