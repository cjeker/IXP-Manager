<?php

namespace IXP\Http\Controllers\Api\V4;

/*
 * Copyright (C) 2009-2017 Internet Neutral Exchange Association Company Limited By Guarantee.
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
use Illuminate\Http\Request;

use Entities\{
    Switcher as SwitcherEntity,
    SwitchPort as SwitchPortEntity
};

/**
 * SwitcherController API Controller
 * @author     Barry O'Donovan <barry@islandbridgenetworks.ie>
 * @author     Yann Robin <yann@islandbridgenetworks.ie>
 * @copyright  Copyright (C) 2009-2017 Internet Neutral Exchange Association Company Limited By Guarantee
 * @license    http://www.gnu.org/licenses/gpl-2.0.html GNU GPL V2.0
 */

class SwitcherController extends Controller {

    /**
     * Get the switch port for a Switch for patch panel port
     *
     * @params  $request instance of the current HTTP request
     * @return  JSON array of listPort
     */
    public function switchPortForPPP( Request $request, int $id) {
        $listPorts = D2EM::getRepository(SwitcherEntity::class)->getAllPortsForPPP($id ,$request->input('custId'), $request->input('spId'));
        return response()->json(['listPorts' => $listPorts]);
    }

    /**
     * Get the Prewired switch port for a Switch
     *
     * @params  $request instance of the current HTTP request
     * @return  JSON array of listPort
     */
    public function switchPortPrewired( Request $request, int $id) {
        $listPorts = D2EM::getRepository(SwitcherEntity::class)->getAllPortsPrewired($id ,$request->input('spId'));
        return response()->json(['listPorts' => $listPorts]);
    }

    /**
     * Get the switch port for a Switch mot assigned to a Physical Interface
     *
     * @params  $request instance of the current HTTP request
     * @return  JSON array of listPort
     */
    public function switchPortNotAssignedToPI( Request $request, int $id) {
        $listPorts = D2EM::getRepository(SwitcherEntity::class)->getAllPortsNotAssignedToPI($id ,$request->input('type'), null);
        return response()->json(['listPorts' => $listPorts]);
    }

    /**
     * Get the switch port for a Switch
     *
     * @params  $request instance of the current HTTP request
     * @return  JSON array of listPort
     */
    public function switchPort( Request $request, int $id) {
        $listPorts = D2EM::getRepository(SwitcherEntity::class)->getAllPorts($id ,[SwitchPortEntity::TYPE_CORE,SwitchPortEntity::TYPE_UNSET], $request->input('spIdsexcluded'));
        return response()->json(['listPorts' => $listPorts]);
    }


}