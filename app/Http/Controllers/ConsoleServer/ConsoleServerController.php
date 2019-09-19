<?php

namespace IXP\Http\Controllers\ConsoleServer;

/*
 * Copyright (C) 2009 - 2019 Internet Neutral Exchange Association Company Limited By Guarantee.
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

use Auth, D2EM, Former, Log;

use Entities\{
    ConsoleServer       as ConsoleServerEntity,
    Cabinet             as CabinetEntity,
    Vendor              as VendorEntity
};


use Illuminate\Http\RedirectResponse;
use IXP\Http\Controllers\Doctrine2Frontend;

use IXP\Http\Requests\StoreConsoleServer as StoreConsoleServerRequest;

use IXP\Utils\View\Alert\{
    Alert,
    Container as AlertContainer
};

/**
 * ConsoleServerConnection Controller
 * @author     Barry O'Donovan <barry@islandbridgenetworks.ie>
 * @author     Yann Robin <yann@islandbridgenetworks.ie>
 * @category   Controller
 * @copyright  Copyright (C) 2009 - 2019 Internet Neutral Exchange Association Company Limited By Guarantee
 * @license    http://www.gnu.org/licenses/gpl-2.0.html GNU GPL V2.0
 */
class ConsoleServerController extends Doctrine2Frontend {

    /**
     * The object being added / edited
     * @var ConsoleServerEntity
     */
    protected $object = null;

    /**
     * Sometimes we need to pass a custom request object for validation / authorisation.
     *
     * Set the name of the function here and the route for store will be pointed to it instead of doStore()
     *
     * @var string
     */
    protected static $storeFn = 'customStore';

    /**
     * This function sets up the frontend controller
     */
    public function feInit()
    {

        $this->feParams         = (object)[

            'entity'            => ConsoleServerEntity::class,

            'pagetitle'         => 'Console Servers',

            'titleSingular'     => 'Console Server',
            'nameSingular'      => 'a console server',

            'listOrderBy'       => 'id',
            'listOrderByDir'    => 'ASC',

            'viewFolderName'    => 'console-server',

            'listColumns'    => [

                'name'           => [
                    'title'      => 'Name',
                    'type'       => self::$FE_COL_TYPES[ 'HAS_ONE' ],
                    'controller' => 'console-server-connection',
                    'action'     => 'list/port',
                    'idField'    => 'id'
                ],

                'facility'  => [
                    'title'      => 'Facility',
                    'type'       => self::$FE_COL_TYPES[ 'HAS_ONE' ],
                    'controller' => 'facility',
                    'action'     => 'view',
                    'idField'    => 'locationid'
                ],

                'cabinet'  => [
                    'title'      => 'Cabinet',
                    'type'       => self::$FE_COL_TYPES[ 'HAS_ONE' ],
                    'controller' => 'rack',
                    'action'     => 'view',
                    'idField'    => 'cabinetid'
                ],

                'vendor'  => [
                    'title'       => 'Vendor',
                    'type'        => self::$FE_COL_TYPES[ 'HAS_ONE' ],
                    'controller'  => 'vendor',
                    'action'      => 'view',
                    'idField'     => 'vendorid'
                ],

                'model'           => 'Model',

                //'num_connections' => 'Connections',

                'num_connections' => [
                    'title'      => 'Connections',
                    'type'       => self::$FE_COL_TYPES[ 'HAS_ONE' ],
                    'controller' => 'console-server-connection',
                    'action'     => 'list/port',
                    'idField'    => 'id'
                ],

                'active'       => [
                    'title'    => 'Active',
                    'type'     => self::$FE_COL_TYPES[ 'YES_NO' ]
                ]
            ]
        ];

        // display the same information in the view as the list
        $this->feParams->viewColumns = array_merge(
            $this->feParams->listColumns,
            [
                'serialNumber'   => 'Serial Number',
                'notes'       => [
                    'title'         => 'Notes',
                    'type'          => self::$FE_COL_TYPES[ 'PARSDOWN' ]
                ]
            ]
        );


    }

    /**
     * Provide array of rows for the list action and view action
     *
     * @param int $id The `id` of the row to load for `view` action`. `null` if `listAction`
     * @return array
     */
    protected function listGetData( $id = null )
    {
        return D2EM::getRepository( ConsoleServerEntity::class )->getAllForFeList( $this->feParams, $id );
    }



    /**
     * Display the form to add/edit an object
     * @param   int $id ID of the row to edit
     * @return array
     */
    protected function addEditPrepareForm( $id = null ): array
    {
        if( $id !== null ) {

            if( !( $this->object = D2EM::getRepository( ConsoleServerEntity::class )->find( $id) ) ) {
                abort(404, 'Console server not found' );
            }

            Former::populate([
                'name'              => request()->old( 'name',             $this->object->getName() ),
                'hostname'          => request()->old( 'hostname',         $this->object->getHostname() ),
                'model'             => request()->old( 'model',            $this->object->getModel() ),
                'serial_number'     => request()->old( 'serial_number',    $this->object->getSerialNumber() ),
                'cabinet'           => request()->old( 'cabinet',          $this->object->getCabinet()->getId() ),
                'vendor'            => request()->old( 'vendor',           $this->object->getVendor()->getId() ),
                'active'            => request()->old( 'active',           ( $this->object->getActive() ? 1 : 0 ) ),
                'notes'             => request()->old( 'notes',             $this->object->getNote() ),
            ]);
        }

        return [
            'object'                => $this->object,
            'cabinets'              => D2EM::getRepository( CabinetEntity::class    )->getAsArray(),
            'vendors'               => D2EM::getRepository( VendorEntity::class     )->getAsArray(),
        ];
    }

    /**
     * Function to do the actual validation and storing of the submitted object.
     * @param StoreConsoleServerRequest $request
     * @return bool|RedirectResponse
     * @throws
     */
    public function customStore( StoreConsoleServerRequest $request )
    {

        if( $request->input( 'id', false ) ) {
            if( !( $this->object = D2EM::getRepository( ConsoleServerEntity::class )->find( $request->input( 'id' ) ) ) ) {
                abort( 404, 'Console server not found' );
            }
        } else {
            $this->object = new ConsoleServerEntity;
            D2EM::persist( $this->object );
        }

        $this->object->setName(         $request->input( 'name'             ) );
        $this->object->setSerialNumber( $request->input( 'serial_number'    ) );
        $this->object->setHostname(     $request->input( 'hostname'         ) );
        $this->object->setNote(         $request->input( 'notes'            ) );
        $this->object->setModel(        $request->input( 'model'            ) );
        $this->object->setActive(       $request->input( 'active' ) ? 1 : 0 );
        $this->object->setVendor(       D2EM::getRepository( VendorEntity::class    )->find( $request->input( 'vendor'     ) ) );
        $this->object->setCabinet(      D2EM::getRepository( CabinetEntity::class   )->find( $request->input( 'cabinet'    ) ) );

        D2EM::flush( );

        $action = $request->input( 'id' )  ? "edited" : "added";

        Log::notice( ( Auth::check() ? Auth::user()->getUsername() : 'A public user' ) . ' ' . $action . ' ' . $this->feParams->nameSingular . ' with ID ' . $this->object->getId() );

        AlertContainer::push( $this->store_alert_success_message ?? $this->feParams->titleSingular . " " . $action, Alert::SUCCESS );

        return redirect()->to( $this->postStoreRedirect() ?? route( self::route_prefix() . '@' . 'list' ) );
    }


    /**
     * Delete all console server connections before deleting the console server.
     *
     * @inheritdoc
     *
     * @return bool Return false to stop / cancel the deletion
     */
    protected function preDelete(): bool
    {
        foreach( $this->object->getConsoleServerConnections() as $csc ) {
            D2EM::remove( $csc );
        }

        return true;
    }

}
