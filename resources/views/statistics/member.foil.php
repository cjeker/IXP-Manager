<?php
/** @var Foil\Template\Template $t */
$this->layout( 'layouts/ixpv4' );
?>


<?php $this->section( 'title' ) ?>
    <?php if( Auth::check() && Auth::user()->isSuperUser() ): ?>
        <a href="<?= route( 'customer@list' )?>">Customers</a>
       <li>
           <a href="<?= route( 'customer@list' )?>" >
               <?= $t->c->getFormattedName() ?>
           </a>
       </li>
    <?php else: ?>
        IXP Interface Statistics :: <?= $t->cust->getFormattedName() ?>
    <?php endif; ?>
<?php $this->append() ?>

<?php if( Auth::check() && Auth::user()->isSuperUser() ): ?>
    <?php $this->section( 'page-header-postamble' ) ?>
        <li>
            Statistics
            (
                <?= IXP\Services\Grapher\Graph::resolveCategory( $t->category ) ?>
                /
                <?= IXP\Services\Grapher\Graph::resolvePeriod( $t->period ) ?>
            )
        </li>
    <?php $this->append() ?>
<?php endif; ?>



<?php $this->section('content') ?>
    <?= $t->alerts() ?>
    <div class="row">
        <div class="col-sm-12">


            <nav class="navbar navbar-default">
                <div class="">

                    <div class="navbar-header">
                        <a class="navbar-brand" href="http://ixp.test/statistics/members">Graph Options:</a>
                    </div>

                    <form class="navbar-form navbar-left form-inline"  action="<?= route( "statistics@member", [ "id" => $t->c->getId() ] ) ?>" method="""et">

                        <div class="form-group">
                            <label for="category">Type:</label>
                            <select id="category" name="category" onchange="" class="form-control">
                                <?php foreach( IXP\Services\Grapher\Graph::CATEGORY_DESCS as $cvalue => $cname ): ?>
                                    <option value="<?= $cvalue ?>" <?php if( $t->category == $cvalue ): ?> selected <?php endif; ?> ><?= $cname ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="period">Period:</label>
                            <select id="period" name="period" onchange="" class="form-control" placeholder="Select State">
                                <option></option>
                                <?php foreach( IXP\Services\Grapher\Graph::PERIOD_DESCS as $pvalue => $pname ): ?>
                                    <option value="<?= $pvalue ?>" <?php if( $t->period == $pvalue ): ?> selected <?php endif; ?>  ><?= $pname ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-default">Change</button>

                    </form>

                </div>
            </nav>



            <div class="row col-sm-6">

                <div class="well">
                    <h3>
                        Aggregate Peering Traffic
                        <?php if( $t->resellerMode() && $t->c->isReseller() ): ?>
                            <small><em>(Peering ports only)</em></small>
                        <?php endif; ?>
                        <a class="btn btn-default pull-right" href="<?= route( "statistics@memberDrilldown" , [ "id" => $t->c->getId(), "type" => "aggregate" ] ) ?>/?category=<?= $t->category ?>">
                            <i class="glyphicon glyphicon-eye-open"></i>
                        </a>
                    </h3>
                    <p>
                        <br />
                        <?= $t->grapher->customer( $t->c )->setCategory( $t->category )->setPeriod( $t->period )->renderer()->boxLegacy() ?>
                    </p>
                </div>
            </div>



            <?php
                /** @var Entities\VirtualInterface $vi */
                foreach( $t->c->getVirtualInterfaces() as $vi ): ?>


                <div class="well col-sm-12" style="background-color: #fafafa">

                    <?php
                        if( !isset( $vi->getPhysicalInterfaces()[ 0 ] ) ) {
                            continue;
                        }

                        $pi = $vi->getPhysicalInterfaces()[ 0 ];
                        $isLAG = count( $vi->getPhysicalInterfaces() ) > 1;
                    ?>


                    <?php if( $isLAG ): ?>

                        <div class="col-sm-6">
                            <div class="well">
                                <h4>
                                    LAG on <?= $pi->getSwitchPort()->getSwitcher()->getCabinet()->getLocation()->getName() ?>
                                    / <?= $pi->getSwitchPort()->getSwitcher()->getName() ?>

                                    <a class="btn btn-default pull-right" href="<?= route( "statistics@memberDrilldown" , [ "id" => $t->c->getId(), "type" => "aggregate", "type" => "vi", "typeid" => $vi->getId()  ] ) ?>/?category=<?= $t->category ?>">
                                        <i class="glyphicon glyphicon-eye-open"></i>
                                    </a>
                                </h4>
                                <p>
                                    <br />
                                    <?= $t->grapher->virtint( $vi )->setCategory( $t->category )->setPeriod( $t->period )->renderer()->boxLegacy() ?>
                                </p>
                            </div>
                        </div>

                    <?php endif; ?>


                    <?php foreach( $vi->getPhysicalInterfaces() as $idx => $pi ): ?>

                        <div class="col-sm-6 <?php if( $isLAG && $idx > 0 ): ?>col-md-offset-6 <?php endif; ?>">

                            <div class="well">

                                <h4>
                                    <?php if( $isLAG ): ?>
                                        <?= $pi->getSwitchPort()->getSwitcher()->getName() ?> ::
                                        <?= $pi->getSwitchPort()->getName() ?> (<?=$pi->resolveSpeed() ?>)
                                    <?php else: ?>
                                        <?= $pi->getSwitchPort()->getSwitcher()->getCabinet()->getLocation()->getName() ?>
                                            / <?= $pi->getSwitchPort()->getSwitcher()->getName() ?> (<?=$pi->resolveSpeed() ?>)
                                    <?php endif; ?>

                                    <a class="btn btn-default pull-right" href="<?= route( "statistics@memberDrilldown" , [ "id" => $t->c->getId(), "type" => "aggregate", "type" => "pi", "typeid" => $pi->getId()  ] ) ?>/?category=<?= $t->category ?>">
                                        <i class="glyphicon glyphicon-eye-open"></i>
                                    </a>

                                    <small>
                                        <br />
                                        <?php if( !$isLAG ): ?>
                                            <?= $pi->getSwitchPort()->getName() ?>
                                        <?php endif; ?>

                                        <?php if( $t->resellerMode() && $t->c->isReseller() ): ?>
                                            <br />
                                            <?php if( $pi->getSwitchPort()->isTypePeering() ): ?>
                                            Peering Port
                                            <?php elseif( $pi->getSwitchPort()->isTypeFanout() ): ?>
                                                Fanout Port for <a href="<?= route( 'customer@overview', [ 'id' => $pi->getRelatedInterface()->getVirtualInterface()->getCustomer()->getId() ] ) ?>">
                                                <?= $pi->getRelatedInterface()->getVirtualInterface()->getCustomer()->getAbbreviatedName() ?>
                                            </a>
                                            <?php elseif( $pi->getSwitchPort()->isTypeReseller() ): ?>
                                                Reseller Uplink Port
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </small>
                                </h4>

                                <p>
                                    <br />
                                    <?= $t->grapher->physint( $pi )->setCategory( $t->category )->setPeriod( $t->period )->renderer()->boxLegacy() ?>
                                </p>

                            </div>
                        </div>

                    <?php endforeach; /* $vi->getPhysicalInterfaces() */ ?>
                </div>

            <?php endforeach; /* $t->c->getVirtualInterfaces() */ ?>
        </div>
        
    </div>
<?php $this->append() ?>