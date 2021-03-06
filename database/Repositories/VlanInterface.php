<?php

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
 * FITNESS FOR A PARTICULAR PURPOSE.  See the GpNU General Public License for
 * more details.
 *
 * You should have received a copy of the GNU General Public License v2.0
 * along with IXP Manager.  If not, see:
 *
 * http://www.gnu.org/licenses/gpl-2.0.html
 */

namespace Repositories;

use DB;

use Doctrine\ORM\EntityRepository;

use Entities\{
    Layer2Address as Layer2AddressEntity,
    Router as RouterEntity,
    Vlan as VlanEntity,
    VlanInterface as VlanInterfaceEntity
};

use IXP\Exceptions\GeneralException as IXP_Exception;

/**
 * VlanInterface
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class VlanInterface extends EntityRepository
{

    /**
     * Utility function to provide an array of all VLAN interfaces on a given
     * VLAN for a given protocol.
     *
     * Returns an array of elements such as:
     *
     *     [
     *         [cid] => 999
     *         [cname] => Customer Name
     *         [abrevcname] => Abbreviated Customer Name
     *         [cshortname] => shortname
     *         [autsys] => 65500
     *         [gmaxprefixes] => 20        // from cust table (global)
     *         [peeringmacro] => ABC
     *         [peeringmacrov6] => ABC
     *         [vid]        => 2
     *         [vtag]       => 10,
     *         [vname]      => "Peering LAN #1
     *         [viid] => 120
     *         [vliid] => 159
     *         [canping] => 1
     *         [enabled] => 1              // VLAN interface enabled for requested protocol?
     *         [address] => 192.0.2.123    // assigned address for requested protocol?
     *         [monitorrcbgp] => 1
     *         [bgpmd5secret] => qwertyui  // MD5 for requested protocol
     *         [hostname] => hostname      // Hostname
     *         [maxbgpprefix] => 20        // VLAN interface max prefixes
     *         [as112client] => 1          // if the member is an as112 client or not
     *         [rsclient] => 1             // if the member is a route server client or not
     *         [rsmorespecifics] => 0/1    // if IRRDB filtering should allow more specifics
     *         [busyhost]
     *         [sid]
     *         [sname]
     *         [cabid]
     *         [cabname]
     *         [location_name]
     *         [location_tag]
     *         [location_shortname]
     *     ]
     *
     * @param \Entities\Vlan $vlan The VLAN
     * @param int $proto Either 4 or 6
     * @param int $pistatus The status of the physical interface
     * @return array As defined above.
     * @throws IXP_Exception On bad / no protocol
     */
    public function getForProto( $vlan, $proto, $pistatus = \Entities\PhysicalInterface::STATUS_CONNECTED )
    {
        if( !in_array( $proto, [ 4, 6 ] ) )
            throw new IXP_Exception( 'Invalid protocol specified' );


        $qstr = "SELECT c.id              AS cid, 
                        c.name            AS cname, 
                        c.abbreviatedName AS abrevcname, 
                        c.shortname       AS cshortname, 
                        c.autsys          AS autsys, 
                        c.maxprefixes     AS gmaxprefixes, 
                        c.peeringmacro    AS peeringmacro, 
                        c.peeringmacrov6  AS peeringmacrov6,
                        
                        v.id                 AS vid,
                        v.number             AS vtag,
                        v.name               AS vname,
                        vi.id                AS viid, 

                        vli.id AS vliid, 
                       
                        vli.ipv{$proto}enabled      AS enabled, 
                        vli.ipv{$proto}hostname     AS hostname, 
                        vli.ipv{$proto}monitorrcbgp AS monitorrcbgp, 
                        vli.ipv{$proto}bgpmd5secret AS bgpmd5secret, 
                        vli.maxbgpprefix            AS maxbgpprefix,
                        vli.as112client             AS as112client,
                        vli.rsclient                AS rsclient, 
                        vli.busyhost                AS busyhost, 
                        vli.irrdbfilter             AS irrdbfilter,
                        vli.rsmorespecifics         AS rsmorespecifics,
                        vli.ipv{$proto}canping      AS canping,
                        
                        addr.address AS address,
                       
                        s.id   AS sid,
                        s.name AS sname,
                       
                        cab.id   AS cabid,
                        cab.name AS cabname,
                       
                        l.id        AS location_id,
                        l.name      AS location_name, 
                        l.shortname AS location_shortname, 
                        l.tag       AS location_tag
                       
                    FROM Entities\VlanInterface vli
                        LEFT JOIN vli.VirtualInterface vi
                        LEFT JOIN vli.IPv{$proto}Address addr
                        LEFT JOIN vi.Customer c
                        LEFT JOIN vi.PhysicalInterfaces pi
                        LEFT JOIN pi.SwitchPort sp
                        LEFT JOIN sp.Switcher s
                        LEFT JOIN s.Cabinet cab
                        LEFT JOIN cab.Location l
                        LEFT JOIN vli.Vlan v
                    WHERE
                        v = :vlan
                        AND " . Customer::DQL_CUST_ACTIVE     . "
                        AND " . Customer::DQL_CUST_CURRENT    . "
                        AND " . Customer::DQL_CUST_TRAFFICING . "
                        AND pi.status = :pistatus
                        
                    GROUP BY 
                        vli.id, c.id, c.name, c.abbreviatedName, c.shortname, c.autsys,
                        c.maxprefixes, c.peeringmacro, c.peeringmacrov6,
                        vli.ipv{$proto}enabled, addr.address, vli.ipv{$proto}bgpmd5secret, vli.maxbgpprefix,
                        vli.ipv{$proto}hostname, vli.ipv{$proto}monitorrcbgp, vli.busyhost,
                        vli.as112client, vli.rsclient, vli.irrdbfilter, vli.ipv{$proto}canping,
                        s.id, s.name,
                        cab.id, cab.name,
                        l.name, l.shortname, l.tag
                        ";

        $qstr .= " ORDER BY c.autsys ASC, vli.id ASC";

        $q = $this->getEntityManager()->createQuery( $qstr );
        $q->setParameter( 'vlan', $vlan );
        $q->setParameter( 'pistatus', $pistatus );
        return $q->getArrayResult();
    }


    /**
     * Utility function to provide an array of VLAN interface objects on a given VLAN.
     *
     * @param \Entities\Vlan $vlan The VLAN to gather VlanInterfaces for
     * @return \Entities\VlanInterface[] Indexed by VlanInterface ID
     */
    public function getObjectsForVlan( $vlan, $protocol = null )
    {
        if( in_array( $protocol, [ 4, 6 ] ) ) {
            $pq = " AND vli.ipv{$protocol}enabled = 1";
        } else

        $qstr = "SELECT vli
                    FROM Entities\VlanInterface vli
                        JOIN vli.Vlan v
                        JOIN vli.VirtualInterface vi
                        JOIN vi.PhysicalInterfaces pi
                        JOIN vi.Customer c

                    WHERE
                        v = :vlan
                        AND " . Customer::DQL_CUST_ACTIVE     . "
                        AND " . Customer::DQL_CUST_CURRENT    . "
                        AND " . Customer::DQL_CUST_TRAFFICING . "
                        AND " . Customer::DQL_CUST_EXTERNAL   . "
                        AND pi.status = " . \Entities\PhysicalInterface::STATUS_CONNECTED . ( $pq ?? '' ) . "

                    ORDER BY c.name ASC";

        $q = $this->getEntityManager()->createQuery( $qstr );
        $q->setParameter( 'vlan', $vlan );

        $vlis = [];
        foreach( $q->getResult() as $vli )
            $vlis[ $vli->getId() ] = $vli;

        return $vlis;
    }


    /**
     * Utility function to provide an array of all VLAN interface objects for a given
     * customer at an optionally given IXP.
     *
     * @param \Entities\Customer $customer The customer
     * @return \Entities\VlanInterface[] Index by the VlanInterface ID
     */
    public function getForCustomer( $customer )
    {
        $qstr = "SELECT vli
                    FROM Entities\\VlanInterface vli
                        JOIN vli.VirtualInterface vi
                        JOIN vi.Customer c
                        JOIN vli.Vlan v
                    WHERE c = :customer
                    ORDER BY v.number";

        $q = $this->getEntityManager()->createQuery( $qstr );
        $q->setParameter( 'customer', $customer );

        $vlis = [];

        foreach( $q->getResult() as $vli ) {
            $vlis[ $vli->getId() ] = $vli;
        }

        return $vlis;
    }


    /**
     * Utility function to get and return active VLAN interfaces on the requested protocol
     * suitable for route collector / server configuration.
     *
     * Sample return:
     *
     *     [
     *         [cid] => 999
     *         [cname] => Customer Name
     *         [cshortname] => shortname
     *         [autsys] => 65000
     *         [peeringmacro] => QWE              // or AS65500 if not defined
     *         [vliid] => 159
     *         [fvliid] => 00159                  // formatted %05d
     *         [address] => 192.0.2.123
     *         [bgpmd5secret] => qwertyui         // or false
     *         [as112client] => 1                 // if the member is an as112 client or not
     *         [rsclient] => 1                    // if the member is a route server client or not
     *         [maxprefixes] => 20
     *         [irrdbfilter] => 0/1               // if IRRDB filtering should be applied
     *         [rsmorespecifics] => 0/1           // if IRRDB filtering should allow more specifics
     *         [location_name] => Interxion DUB1
     *         [location_shortname] => IX-DUB1
     *         [location_tag] => ix1
     *     ]
     *
     * @param Vlan $vlan
     * @return array As defined above
     * @throws \Exception
     */
    public function sanitiseVlanInterfaces( VlanEntity $vlan, int $protocol = 4, int $target = RouterEntity::TYPE_ROUTE_SERVER, bool $quarantine = false ): array {

        $ints = $this->getForProto( $vlan, $protocol,
            $quarantine  ? \Entities\PhysicalInterface::STATUS_QUARANTINE : \Entities\PhysicalInterface::STATUS_CONNECTED
        );

        $newints = [];

        foreach( $ints as $int )
        {
            if( !$int['enabled'] ) {
                continue;
            }

            $int['protocol'] = $protocol;

            // don't need this anymore:
            unset( $int['enabled'] );

            if( $target == RouterEntity::TYPE_ROUTE_SERVER && !$int['rsclient'] ) {
                continue;
            }

            if( $target == RouterEntity::TYPE_AS112 && !$int['as112client'] ) {
                continue;
            }

            $int['fvliid'] = sprintf( '%04d', $int['vliid'] );

            if( $int['maxbgpprefix'] && $int['maxbgpprefix'] > $int['gmaxprefixes'] ) {
                $int['maxprefixes'] = $int['maxbgpprefix'];
            } else {
                $int['maxprefixes'] = $int['gmaxprefixes'];
            }

            if( !$int['maxprefixes'] ) {
                $int['maxprefixes'] = 250;
            }

            unset( $int['gmaxprefixes'] );
            unset( $int['maxbgpprefix'] );

            if( $protocol == 6 && $int['peeringmacrov6'] ) {
                $int['peeringmacro'] = $int['peeringmacrov6'];
            }

            if( !$int['peeringmacro'] ) {
                $int['peeringmacro'] = 'AS' . $int['autsys'];
            }

            unset( $int['peeringmacrov6'] );

            if( !$int['bgpmd5secret'] ) {
                $int['bgpmd5secret'] = false;
            }

            $int['allpeeringips'] = $this->getAllIPsForASN( $vlan, $int['autsys'], $protocol );

            if( $int['irrdbfilter'] ) {
                $int['irrdbfilter_prefixes'] = d2r( 'IrrdbPrefix' )->getForCustomerAndProtocol( $int[ 'cid' ], $protocol, true );
                $int['irrdbfilter_asns'    ] = d2r( 'IrrdbAsn'    )->getForCustomerAndProtocol( $int[ 'cid' ], $protocol, true );
            }

            $newints[ $int['address'] ] = $int;
        }

        return $newints;
    }


    /**
     * Find all IP addresses on a given VLAN for a given ASN and protocol.
     *
     * This is used (for example) when generating router configuration
     * which prevents next-hop hijacking but allows the same ASN to
     * set its other IPs as the next hop.
     *
     * @param VlanEntity $v
     * @param int $asn
     * @param int $proto
     * @return array Array of IP addresses [ '192.0.2.2', '192.0.2.23', ]
     * @throws \Exception
     */
    public function getAllIPsForASN( VlanEntity $v, int $asn, int $proto ): array
    {
        if( !in_array( $proto, [4,6] ) ) {
            throw new \Exception( 'Invalid inet protocol' );
        }

        $ipe = "IPv{$proto}Address";

        $dql = "SELECT ip.address AS ipaddress
         
                    FROM Entities\Vlan v
                        LEFT JOIN v.VlanInterfaces vli
                        LEFT JOIN vli.{$ipe} ip
                        LEFT JOIN vli.VirtualInterface vi
                        LEFT JOIN vi.Customer c
            
                    WHERE c.autsys = :asn AND v = :vlan";

        $q = $this->getEntityManager()->createQuery( $dql )
            ->setParameter( 'asn', $asn )
            ->setParameter( 'vlan', $v );

        $ips = array_column( $q->getScalarResult(), 'ipaddress' );
        $vips = [];

        foreach( $ips as $ip ) {
            if( filter_var( $ip, FILTER_VALIDATE_IP ) ) {
                $vips[] = $ip;
            }
        }

        return $vips;
    }


    /**
     * Provide array of vlan interfaces for the list Action
     *
     * @param int $id The VlanInterface to find
     * @return array array of vlan interfaces
     */
    public function getForList( int $id = null )
    {
        $dql = "SELECT vli.id AS id, vli.mcastenabled AS mcastenabled,
                 vli.ipv4enabled AS ipv4enabled, vli.ipv4hostname AS ipv4hostname, vli.ipv4canping AS ipv4canping,
                     vli.ipv4monitorrcbgp AS ipv4monitorrcbgp, vli.ipv4bgpmd5secret AS ipv4bgpmd5secret,
                 vli.ipv6enabled AS ipv6enabled, vli.ipv6hostname AS ipv6hostname, vli.ipv6canping AS ipv6canping,
                     vli.ipv6monitorrcbgp AS ipv6monitorrcbgp, vli.ipv6bgpmd5secret AS ipv6bgpmd5secret,
                 vli.irrdbfilter AS irrdbfilter, vli.bgpmd5secret AS bgpmd5secret, vli.maxbgpprefix AS maxbgpprefix,
                 vli.as112client AS as112client, vli.busyhost AS busyhost, vli.notes AS notes,
                 vli.rsclient AS rsclient,
                 ip4.address AS ipv4, ip6.address AS ipv6,
                 v.id AS vlanid, v.name AS vlan,
                 vi.id AS vintid,
                 c.name AS customer, c.id AS custid
                    FROM \\Entities\\VlanInterface vli
                        LEFT JOIN vli.VirtualInterface vi
                        LEFT JOIN vli.Vlan v
                        LEFT JOIN vli.IPv4Address ip4
                        LEFT JOIN vli.IPv6Address ip6
                        LEFT JOIN vi.Customer c";

        if( $id ){
            $dql .= " WHERE vli.id = $id ";
        }


        $q = $this->getEntityManager()->createQuery( $dql );
        return $q->getArrayResult();
    }

    /**
     * Get vli id / mac address mapping from macaddress table for sflow data processing
     *
     * Returns a hash as follows:
     *
     *     {
     *         '$infrastructure' => {
     *             '$vlan' => {
     *                '$mac' => $vliid
     *                '$mac' => $vliid
     *             }
     *         }
     *     }
     *
     * @return mixed
     */
    public function sflowLearnedMacsHash(): array
    {
        return $this->getEntityManager()->createQuery(
            "SELECT DISTINCT vli.id AS vliid, ma.mac AS mac, vl.number as tag, i.id as infrastructure
                    FROM Entities\VirtualInterface vi
                        LEFT JOIN vi.VlanInterfaces vli
                        JOIN vi.MACAddresses ma
                        LEFT JOIN vli.Vlan vl
                        LEFT JOIN vl.Infrastructure i
                    WHERE ma.mac IS NOT NULL
                        AND vli.id IS NOT NULL
                    ORDER BY vliid"
        )->getArrayResult();
    }

    /**
     * Get vli id / mac address mapping from macaddress table for sflow data processing
     *
     * Returns a hash as follows:
     *
     *     {
     *         '$infrastructure' => {
     *             '$vlan' => {
     *                '$mac' => $vliid
     *                '$mac' => $vliid
     *             }
     *         }
     *     }
     *
     * @return mixed
     */
    public function sflowConfiguredMacsHash(): array
    {
        return $this->getEntityManager()->createQuery(
            "SELECT DISTINCT vli.id AS vliid, l2a.mac AS mac, vl.number as tag, i.id as infrastructure
                    FROM Entities\VlanInterface vli
                        LEFT JOIN vli.VirtualInterface vi
                        LEFT JOIN vli.layer2Addresses l2a
                        LEFT JOIN vli.Vlan vl
                        LEFT JOIN vl.Infrastructure i
                    WHERE l2a.mac IS NOT NULL
                    ORDER BY vliid"
        )->getArrayResult();
    }


    /**
     * Utility function to copy all l2a's from one vli to another
     *
     * @param VlanInterfaceEntity $s Source VLI for l2a's
     * @param VlanInterfaceEntity $d Destinatin VLI for copied l2a's
     * @return VlanInterface
     */
    public function copyLayer2Addresses( VlanInterfaceEntity $s, VlanInterfaceEntity $d ): VlanInterface {
        foreach( $s->getLayer2Addresses() as $l2a ) {
            $n = new Layer2AddressEntity();
            $n->setVlanInterface( $d );
            $d->addLayer2Address( $n );
            $n->setMac( $l2a->getMac() );
            $this->getEntityManager()->persist( $n );
        }

        return $this;
    }


    /**
     * Get statistics of RS clients / total on a per VLAN basis
     *
     * Returns an array of objects such as:
     *
     *     [
     *         {
     *             +"vlanname": "Peering VLAN #1",
     *             ++"overall_count": 60,
     *             ++"rsclient_count": "54",
     *         }
     *     ]
     *
     * @return array
     */
    public function getRsClientUsagePerVlan(): array
    {
        return DB::select('SELECT v.name AS vlanname, COUNT(vli.id) AS overall_count, SUM(vli.rsclient = 1) AS rsclient_count
            FROM `vlaninterface` AS vli
            LEFT JOIN virtualinterface AS vi ON vli.virtualinterfaceid = vi.id
            LEFT JOIN cust AS c ON vi.custid = c.id
            LEFT JOIN vlan AS v ON vli.vlanid = v.id
            WHERE v.`private` = 0 AND c.type IN (1,4)
            GROUP BY vlanname'
        );
    }

    /**
     * Get statistics of ipv6 enabled / total on a per VLAN basis
     *
     * Returns an array of objects such as:
     *
     *     [
     *         {
     *             +"vlanname": "Peering VLAN #1",
     *             ++"overall_count": 60,
     *             ++"ipv6_count": "54",
     *         }
     *     ]
     *
     * @return array
     */
    public function getIPv6UsagePerVlan(): array
    {
        return DB::select('SELECT v.name AS vlanname, COUNT(vli.id) AS overall_count, SUM(vli.ipv6enabled = 1) AS ipv6_count
            FROM `vlaninterface` AS vli
            LEFT JOIN virtualinterface AS vi ON vli.virtualinterfaceid = vi.id
            LEFT JOIN cust AS c ON vi.custid = c.id
            LEFT JOIN vlan AS v ON vli.vlanid = v.id
            WHERE v.`private` = 0 AND c.type IN (1,4)
            GROUP BY vlanname'
        );
    }
}
