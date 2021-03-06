<?php
declare(strict_types=1);
namespace PHPArcade;

use PDO;

{

    class Core
    {
        private static $dbconfig;
        private static $instance;
        protected $act;
        protected $config;
        protected $line;
        protected $links_arr;
        protected $string;
        private function __construct()
        {
        }
        public static function doEvent($event)
        {
            global $actions_array;
            $actions = [];
            if (isset($actions_array[$event])) {
                $actions = $actions_array[$event];
            }
            foreach ($actions as $action) {
                if (is_callable($action)) {
                    $action();
                }
            }
        }
        public static function encodeHTMLEntity($string, $params = null)
        {
            return html_entity_decode($string, $params);
        }
        public static function getCurrentDate()
        {
            return time();
        }
        public static function getFlashModal()
        {
            ?>
            <!--suppress ALL -->
            <div id="myModal" class="modal fade">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                        <h4 class="modal-title bg-danger">Notice Regarding Flash</h4>
                    </div>
                    <div class="modal-body">
                        <p class="text-default">
                            Notice: All of the major browsers are ending support of Adobe Flash, so you will need to
                            enable Flash to have the best experience while we add more mobile-friendly games and apps.
                        </p>
                        <p class="text-danger">
                            Please note: We <strong>ONLY</strong> serve flash games from <?php echo SITE_URL; ?>.  The
                            settings below will only allow flash for <?php echo SITE_URL; ?>, which will help ensure your
                            security.
                        </p>
                        <p class="text-default">
                            Alternatively, you may play our HTML5 games which do not require Flash and are also able
                            to be played on mobile.  Unfortunately, Flash is not available on mobile devices.
                        </p>
                        <div class="pull-left">
                            <a class="btn btn-md btn-info"
                               href="https://helpx.adobe.com/flash-player/kb/enabling-flash-player-firefox.html"
                               target="_blank"
                               rel="noopener">
                                Enable Flash - <i class="fa fa-firefox" aria-hidden="true"></i> Firefox
                            </a>
                        </div>
                        <div class="pull-right">
                            <a class="btn btn-md btn-info"
                               href="<?php echo self::getLinkPage(6); ?>"
                               target="_blank"
                               rel="noopener">
                                Enable Flash - <i class="fa fa-chrome" aria-hidden="true"></i> Chrome
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            </div><?php
        }
        public static function getLinkPage($id = 0)
        {
            global $links_arr;
            $page = Pages::getPage($id);
            return str_replace(
                array('%id%', '%name%'),
                array($id, self::getCleanURL($page['title'])),
                $links_arr['page']
            );
        }
        public static function getINIConfig()
        {
            return parse_ini_file($_SERVER['DOCUMENT_ROOT'] . '/../phpArcade.ini', true);
        }
        public static function getLinkGame($id = 0)
        {
            global $gamelist;
            $links_arr = self::loadLinks();
            return str_replace('%name%', $gamelist[$id], str_replace('%id%', $id, $links_arr['game']));
        }
        public static function getCleanURL($string)
        {
            $string = preg_replace("/\W/", '-', $string); //Non-word characters, including spaces
            $string = preg_replace("/\-$/", "", $string); //Dashes at end of name (e.g. test-.html)
            $string = preg_replace('/-{2,}/', '-', $string); //Double dashes at end of name (e.g. test--.html)
            return $string;
        }
        public static function getLinkCategory($name = 'all', $page = 1)
        {
            global $links_arr;
            return str_replace('%page%', $page, str_replace('%name%', $name, $links_arr['category']));
        }
        public static function getLinkLogout()
        {
            global $links_arr;
            return $links_arr['logout'];
        }
        public static function getLinkRegister()
        {
            global $links_arr;
            return $links_arr['register'];
        }
        public static function getLinkProfile($userid)
        {
            global $links_arr;
            if ($userid != 0) {
                $find = $replace = [];
                $user = Users::getUserbyID($userid);
                // Replaces...
                $find[] = '%id%';
                $replace[] = $userid;
                $find[] = '%username%';
                $replace[] = self::getCleanURL($user['username']);
                return str_replace($find, $replace, $links_arr['profile']);
            } else {
                return 0;
            }
        }
        public static function getLinkProfileEdit()
        {
            global $links_arr;
            return $links_arr['editprofile'];
        }
        public static function getPageMetaData()
        {
            global $params;
            switch (true) {
                case (self::is('game')):
                    $game = Games::getGame($params[1]);
                    $metadata['metapagetitle'] = $game['name'] . ' ' . gettext('game');
                    $metadata['metapagedesc'] = $game['desc'];
                    $metadata['metapagekeywords'] = $game['keywords'];
                    break;
                case (self::is('category')):
                    $category = Games::getCategory($params[1]);
                    $metadata['metapagetitle'] = $category['name'] . ' ' . gettext('games');
                    $metadata['metapagedesc'] = $category['desc'];
                    $metadata['metapagekeywords'] = $category['keywords'];
                    break;
                case (self::is('page')):
                    $params[1] = $params[1] ?? "";
                    $page = Pages::getPage($params[1]);
                    $metadata['metapagetitle'] = $page['title'];
                    $metadata['metapagedesc'] = $page['description'];
                    $metadata['metapagekeywords'] = $page['keywords'];
                    break;
                case (self::is('profile')):
                    if ($params[1] != 'edit') {
                        if ($params[2] != 'editdone') {
                            $params[2] = $params[2] ?? "";
                            $user = Users::getUserbyID($params[2]);
                            $metadata['metapagetitle'] = $user['username'] . "'s " . gettext('profile');
                            $metadata['metapagedesc'] = $user['username'] . "'s " . gettext('profile');
                            $metadata['metapagekeywords'] = "";
                        }
                    } else {
                        $metadata['metapagetitle'] = "";
                        $metadata['metapagedesc'] = "";
                        $metadata['metapagekeywords'] = "";
                    }
                    break;
                default:
                    $metadata['metapagetitle'] = SITE_META_TITLE;
                    $metadata['metapagedesc'] = SITE_META_DESCRIPTION;
                    $metadata['metapagekeywords'] = SITE_META_KEYWORDS;
            }
            /** @noinspection PhpUndefinedVariableInspection */
            return $metadata;
        }
        public static function getDBConfig()
        {
            if (null !== self::$dbconfig) {
                return self::$dbconfig;
            } else {
                $stmt = mySQL::getConnection()->prepare("CALL sp_Config_Get();");
                $stmt->execute();
            }
            /** @noinspection PhpUndefinedVariableInspection */
            return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        }
        public static function getInstance()
        {
            /* Singleton use */
            if (!self::$instance instanceof self) {
                self::$instance = new self();
            }
            return self::$instance;
        }
        public static function getPages($category = '')
        {
            $dbconfig = self::getDBConfig();
            $stmt = mySQL::getConnection()->prepare('CALL sp_Games_GetNameidByCategory(:catname);');
            $stmt->bindParam(':catname', $category);
            $stmt->execute();
            return ceil($stmt->rowCount() / $dbconfig['gamesperpage']);
        }
        public static function getPlayCountTotal()
        {
            $stmt = mySQL::getConnection()->prepare('CALL sp_Games_GetPlaycount_Total();');
            $stmt->execute();
            $line = $stmt->fetch();
            return $line['playcount'];
        }
        public static function is($location)
        {
            global $act;
            if ($act == "" || !isset($act)) {
                $act = 'home';
            }
            return $act == $location;
        }
        public static function loadLinks()
        {
            /* TODO: Need to clean this up somehow. Change to query string and let mod_rewrite do its thing? */

            global $links_arr, $append, $gamelist;
            /** @noinspection OnlyWritesOnParameterInspection */
            $append = '.html';
            $games = Games::getGamesAllIDsNames();
            $links_arr['game'] = 'game/%id%/%name%';
            foreach ($games as $game) {
                $gamelist[$game['id']] = self::getCleanURL($game['name']);
            }
            // Link data
            $links_arr['category'] = 'category/%name%/%page%';
            $links_arr['editprofile'] = 'profile/edit';
            $links_arr['logout'] = 'login/logout';
            $links_arr['page'] = 'page/%id%/%name%';
            $links_arr['passwordchange'] = 'login/recover/change/%code%/%username%';
            $links_arr['profile'] = 'profile/view/%id%/%username%';
            $links_arr['pwrecover'] = 'login/recover';
            $links_arr['register'] = 'register/register';
            $links_arr['rss'] = 'rss/%type%';
            $links_arr = array_map('PHPArcade\preappbase', $links_arr);
            return $links_arr;
        }
        public static function loadRedirect($url = '')
        {
            if ($url == '') {
                $url = $_SERVER['HTTP_REFERER'];
            }
            header("Location: " . $url);
            exit();
        }
        public static function stopDirectAccess()
        {
            if (count(get_included_files()) === 1) {
                return http_response_code(403) . die('Direct access not permitted.');
            }
            return true;
        }
        public static function returnStatusCode($statuscode)
        {
            return http_response_code($statuscode);
        }
        public static function showCategoryList($categories)
        {
            $i = 0;
            foreach ($categories as $category) {
                ++$i; ?>
                <a class="dropdown-item" href="<?php echo self::getLinkCategory($category['name'], 1); ?>">
                    <?php echo $category['name']; ?>
                </a>
                <?php
            }
        }
        public static function showError($text, $glyph = 'ambulance')
        { ?>
            <div class="alert alert-danger mt-4" role="alert">
                <span class="fa fa-<?php echo $glyph; ?> fa-2x text-left" aria-hidden="true"></span>
                <strong><?php echo gettext('error') ?></strong>
                <?php echo $text; ?>
            </div><?php
        }
        public static function showGlyph($glyph, $size = '1x', $hidden = 'true')
        {
            return "<i class='fa fa-$glyph fa-$size' aria-hidden='$hidden'></i>";
        }
        public static function showInfo($text, $glyph = 'info')
        {
            ?>
            <div class="alert alert-info mt-4" role="alert">
                <span class="fa fa-<?php echo $glyph; ?> fa-2x text-left" aria-hidden="true"></span>
                <strong><?php echo gettext('info') ?></strong>
                <?php echo $text; ?>
            </div><?php
        }
        public static function showSuccess($text, $glyph = 'check')
        {
            ?>
            <div class="alert alert-success mt-4" role="alert">
                <span class="fa fa-<?php echo $glyph; ?> fa-2x text-left" aria-hidden="true"></span>
                <strong><?php echo gettext('success') ?></strong>
                <?php echo $text; ?>
            </div><?php
        }
        public static function showWarning($text, $glyph = 'warning')
        {
            ?>
            <div class="alert alert-warning mt-4" role="alert">
                <span class="fa fa-<?php echo $glyph; ?> fa-2x text-left" aria-hidden="true"></span>
                <strong><?php echo gettext('warning') ?></strong>
                <?php echo $text; ?>
            </div><?php
        }
        private function __clone()
        {
        }
    }

    function preappbase($string)
    {
        return $string != SITE_URL_ADMIN ? SITE_URL . $string . '.html' : $string;
    }
}
