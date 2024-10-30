<?php
$games = $this->games;
$page_num = count($games);
$per_page = 30;
$pages = ceil($page_num / $per_page);
$settings = get_option('cgwp_global_option');
?>
<h1>Flash Games Plugin by AGC</h1>
<div class="cgwp_author_link_check">
    Activate Game Credits : <input type="checkbox" id="cgwp_author_link_check" <?php if($settings['cgwp_author_link'] == '1') echo "checked"; ?>>
    <p class="cgwp_author_link_confirm" style="display: none;"> Thank you for your support!</p>
</div>
<ul class="cgwp_pagination">
<?php
for($i = 1; $i <= $pages ; $i++)
{
    ?>
    <li>
        <a class="cgwp_pageing" href="options-general.php?page=card-games-plugin-menu&p=<?php echo $i; ?>"><?php echo $i; ?></a>
    </li>
    <?php
}
?>
</ul>
    <div class="cgwp_search">

            search : <input type="text" id="cgwp_search_game" placeholder="insert game title">
            <input type="hidden" name="cgwp-search-ajax-nonce" id="cgwp-search-ajax-nonce" value="<?php echo( wp_create_nonce( 'cgwp-search-ajax-nonce' ) );?>" />

        sort by : <select class="cgwp_cats" id="cgwp_cats">
            <option>all</option>
            <?php
            foreach ($this->cats as $cat)
            {
                if($cat != '' )
                {
                    echo "<option>".$cat."</option>";
                }
            }
            ?>
        </select>
    </div>
<div class="cgwp_wrapper">
<?php
$page = isset($_GET['p']) ? $_GET['p'] : 1;
for ( $i= $per_page * ($page - 1) ; $i < $per_page * $page ; $i++ )
{
    if($games[$i] == '')
        break;
    $cur_game = explode('||' , $games[$i]);
    $iframe_src = $cur_game['2'];
    $href_real_link = 'http://www.arcadegamescorner.com/game/'.$cur_game['1'];
    $image_src = 'http://www.arcadegamescorner.com/img/'.$cur_game['0'].'.jpg';
    ?>
    <div class="cgwp_game">
        <img src="<?php echo $image_src; ?>"/>
        <p class="cgwp_game_title">title :  <?php echo $cur_game['0']; ?></p>
        shortcode: <input type="text" readonly value="[cardgame title=<?php echo $cur_game['1'];?> id=<?php echo $i; ?>]">
    </div>
    <?php
}
?>
</div>
