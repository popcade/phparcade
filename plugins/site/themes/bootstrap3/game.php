<?php if (!isset($_SESSION)) {
    session_start();
    $user = $_SESSION['user'];
}

PHPArcade\Users::userUpdatePlaycount();
global $params; ?>
<!--suppress Annotator -->
<div><?php
    $dbconfig = PHPArcade\Core::getDBConfig();
    PHPArcade\Core::doEvent('gamepage');
    $metadata = PHPArcade\Core::getPageMetaData();
    $game = PHPArcade\Games::getGame($params[1]);
    if (isset($game['id'])) {
        $time = PHPArcade\Core::getCurrentDate();
        $img = $dbconfig['imgurl'] . $game['nameid'] . EXT_IMG;
        $thumbnailurl = $img;
        $origgamename = $game['name'];
        $epoch = $game['time'];
        $dt = new DateTime("@$epoch");
        $game['time'] = date('M d, Y', $game['time']);
        PHPArcade\Games::updateGamePlaycount($game['id']); ?>
        <div class="col-lg-12">
            <h1 class="page-header" itemprop="headline"><?php echo $game['name']; ?></h1>
        </div>
        <div class="col-lg-6">
            <div class="panel panel-info">
                <div class="panel-heading">
                    <h2 class="panel-title"><?php echo gettext('description'); ?></h2>
                </div>
                <div class="panel-body">
                    <p class="text-info"><?php echo $game['desc']; ?></p>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
        <div class="panel panel-info">
            <div class="panel-heading">
                <h2 class="panel-title"><?php echo gettext('instructions'); ?></h2>
            </div>
            <div class="panel-body">
                <p class="text-info"><?php echo $game['instructions']; ?></p>
            </div>
        </div>
        </div><?php
        // Fix for bad champions
        /* 	This next section corrects the "Games Champs Table.  When you delete the games_champs users that
            don't exist anymore, it causes the "champ" to actually be the next person that plays... not the
            actual champ. This code patches that issue by taking the best score for that game and updating the
            champs table. This code is driven by above if ($scoreslist->getScoreType("lowhighscore", $game['flags'])).
            Also, the champs table had a PK added on nameid to prevent duplicates.  I'm not sure that $scores needs to
            be defined, but I'm not going to change it just yet.
            $info[0] is the score in the games_champs table
            $tscore['x'] is the top player in the games list.*/
        if (PHPArcade\Scores::getScoreType('lowhighscore', $game['flags'])) {
            $scores = PHPArcade\Scores::getGameScore($game['id'], 'ASC', TOP_SCORE_COUNT);
            $tscores = PHPArcade\Scores::getGameScore($game['id'], 'ASC', 1); // Fix scores when users are deleted
        } else {
            $scores = PHPArcade\Scores::getGameScore($game['id'], 'DESC', TOP_SCORE_COUNT);
            $tscores = PHPArcade\Scores::getGameScore($game['id'], 'DESC', 1); //Fix champ scores when users are deleted
        }
        foreach ($tscores as $tscore) { //Get the top score on that game.
            $tnameid = $tscore['nameid'];
            $tuser = $tscore['player'];
            $tscore = $tscore['score'];
            $info[0] = $info ?? '';
            if ($tscore <> $info[0]) {
                /* Compare the top score to the champ ($info) which is defined in scoreinfo.php
                   If the scores don't match, then correct it. */
                PHPArcade\Games::updateGameChamp($tnameid, $tuser, $tscore, $time, $game['id']);
            }
        }
        /* End Games Champs Fix */ ?>
        <!-- Game Code -->
        <div class="clearfix invisible"></div>
        <div class="col-lg-12">
            <div class="panel panel-info">
                <div class="panel-heading">
                    <h3 class="panel-title"><?php echo $game['name']; ?></h3>
                </div>
                <div class="panel-body text-center">
                    <?php echo PHPArcade\Ads::getInstance()->showAds('Responsive'); ?>
                    <div class="clearfix invisible">&nbsp;</div><?php
                    $game['type'] = $game['type'] ?? '';
                    switch ($game['customcode']) {
                        case null:
                        case '':
                            /** @noinspection MissingOrEmptyGroupStatementInspection */
                            if ($game['type'] === 'extlink') {
                            } else {
                                echo $game['code'];
                                PHPArcade\Core::doEvent('gameplay');
                            }
                            break;
                        default:
                            echo $game['customcode'];
                    } ?>
                    <div class="clearfix invisible">&nbsp;</div>
                    <?php echo PHPArcade\Ads::getInstance()->showAds('Responsive'); ?>
                    <div class="clearfix invisible">&nbsp;</div>
                </div>
            </div>
        </div>
        <div class="clearfix invisible"></div><?php
        if ($game['flags'] <> '') {  /* If there are flags set (i.e. NOT a Mochi game), then show the score table*/
            $i = 0; ?>
            <div class="col-lg-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h2 class="panel-title"><?php echo gettext('top10score'); ?></h2>
                    </div>
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="dataTables-example">
                                <thead>
                                    <tr>
                                        <th><?php echo gettext('Ranking'); ?></th>
                                        <th><?php echo gettext('player'); ?></th>
                                        <th><?php echo gettext('score'); ?></th>
                                        <th><?php echo gettext('date'); ?></th>
                                    </tr>
                                </thead>
                                <tbody><?php
                                    if (count($scores) != 0) {
                                        foreach ($scores as $score) {
                                            ++$i;
                                            $d_score = date('m/d/Y', $score['date']);
                                            $champ = PHPArcade\Users::getUserbyID($score['player']); ?>
                                            <tr class="odd gradeA">
                                            <td><?php echo $i; ?></td>
                                            <td>
                                                <img data-src="<?php echo PHPArcade\Users::userGetGravatar($champ['username'],40); ?>"
                                                     class="img img-responsive img-circle"
                                                     style="float:left"
                                                />
                                                &nbsp;
                                                <a href="<?php echo PHPArcade\Core::getLinkProfile($champ['id']); ?>">
                                                    <?php echo $champ['username']; ?>
                                                </a>
                                            </td>
                                            <td><?php echo PHPArcade\Scores::formatScore($score['score']); ?></td>
                                            <td><?php echo $d_score; ?></td>
                                            </tr><?php
                                        }
                                    } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End Game Code --><?php
        }
        if ($dbconfig['disqus_on'] === 'on') {
            ?>
            <div class="clearfix invisible"></div>
            <div class="col-lg-12">
                <div class="panel panel-info">
                    <div class="panel-heading">
                        <h3 class="panel-title"><?php echo gettext('disqus'); ?></h3>
                    </div>
                    <div class="panel-body">
                        <?php include_once(INST_DIR . 'includes/js/Disqus/disqus.php'); ?>
                    </div>
                </div>
            </div><?php
        }
    } else {
        ?>
        <h1><?php echo gettext('404status'); ?></h1>
        <h2><?php echo gettext('404page'); ?></h2><?php
        PHPArcade\Core::returnStatusCode(404);
        die();
    } ?>
    <!-- Related Items -->
    <div class="clearfix invisible"></div>
    <div class="col-lg-12">
        <div class="panel panel-info">
            <div class="panel-heading">
                <h3 class="panel-title"><?php echo gettext('additionalgames'); ?></h3>
            </div>
            <div class="panel-body text-center"><?php
                $gameslikethis = PHPArcade\Games::getGamesLikeThis();
                foreach ($gameslikethis as $gamelikethis) {
                    $link = PHPArcade\Core::getLinkGame($gamelikethis['id']); ?>
                    <div class="col-md-3 col-md-4">
                    <div class="thumbnail">
                        <a href="<?php echo $link; ?>"><?php
                            $img = $dbconfig['imgurl'] . $gamelikethis['nameid'] . EXT_IMG; ?>
                            <img class="img img-responsive img-rounded"
                                 data-src="<?php echo $img; ?>"
                                 alt="Play <?php echo $gamelikethis['name']; ?> online for free!"
                                 title="Play <?php echo $gamelikethis['name']; ?> online for free!"
                                 width="<?php echo $dbconfig['twidth']; ?>"
                                 height="<?php echo $dbconfig['theight']; ?>"
                            />
                        </a>
                        <div class="caption">
                            <div class="caption">
                                <h3><?php echo $gamelikethis['name']; ?></h3>
                                <p><?php echo $gamelikethis['desc']; ?></p>
                                <p>
                                    <a href="<?php echo $link; ?>" class="btn btn-primary">
                                        <?php echo gettext('playnow'); ?>
                                    </a>
                                </p>
                            </div>
                        </div>
                    </div>
                    </div><?php
                }
                unset($gameslikethis); ?>
            </div>
        </div>
        <?php PHPArcade\Core::getFlashModal();?>
    </div>
    <!-- Schema -->
    <script type="application/ld+json" defer>
        {
          "@context": "http://schema.org",
          "@type": "Game",
          "audience":{
            "@type":"PeopleAudience",
            "suggestedMinAge":"13"
          },
          "aggregateRating": {
             "@type": "AggregateRating",
             "ratingValue": "4.75",
             "reviewCount": "<?php echo rand(1,112);?>"
          },
          "numberOfPlayers":{
            "@type":"QuantitativeValue",
            "minValue":"1",
            "maxValue":"1"
          },
          "datePublished":"<?php echo $dt->format('Y-m-d H:i:s'); ?>",
          "description":"<?php echo strip_tags($game['desc']); ?>",
          "headline":"<?php echo $game['name']; ?>",
          "image":"<?php echo $dbconfig['imgurl'] . $game['nameid'] . EXT_IMG; ?>",
          "keywords":"<?php echo $game['keywords']; ?>",
          "name":"<?php echo $game['name']; ?>",
          "thumbnailUrl":"<?php echo $dbconfig['imgurl'] . $game['nameid'] . EXT_IMG; ?>",
          "url":"<?php echo SITE_URL . trim($_SERVER['REQUEST_URI'], '/'); ?>"
        }
    </script>
    <?php if (!empty($dbconfig['mixpanel_id']))
    { ?>
        <script>
            mixpanel.track(
                "Loaded Game",
                {
                    "GameName": "<?php echo $game['name'];?>"
                }
            );
        </script>
    <?php } ?>
</div>
