<!--Begin Content Below Carousel --><?php
if (!isset($_SESSION)) {
    session_start();
}
$dbconfig = PHPArcade\Core::getDBConfig();
$i = 0;
$games = PHPArcade\Games::getGamesHomePage();
foreach ($games as $game) {
    $link = PHPArcade\Core::getLinkGame($game['id']);
    $img = $dbconfig['imgurl'] . $game['nameid'] . EXT_IMG; ?>
    <div class="col-md-3 col-md-4">
        <div class="thumbnail">
            <a href="<?php echo $link; ?>">
                <img alt="Play <?php echo $game['name']; ?> online for free!"
                     class="img img-responsive img-rounded"
                     data-src="<?php echo $img; ?>"
                     height="<?php echo $dbconfig['theight']; ?>"
                     title="Play <?php echo $game['name']; ?> online for free!"
                     width="<?php echo $dbconfig['twidth']; ?>"
                                     />
            </a>
            <div class="caption">
                <h1 class="home-game-title"><?php echo $game['name']; ?></h1>
                <p>
                    <?php echo $game['desc']; ?>
                </p>
                <p>
                    <a href="<?php echo $link; ?>" class="btn btn-primary">
                        <?php echo gettext('playnow'); ?>
                    </a>
                </p>
            </div>
        </div>
    </div><?php
    ++$i;
    if ($i == 4) {
        ?>
        <div class="clearfix invisible"></div><?php //Resets boxes
        $i = 0;
    }
} ?>
<!--suppress XmlDefaultAttributeValue -->
<script type="text/javascript" src="<?php echo JS_LAZYLOAD; ?>" integrity="<?php echo JS_LAZYLOAD_SRI;?>"
        crossorigin="anonymous" defer></script>
<!--End Content Section -->