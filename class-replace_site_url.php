<?php
// replace class
class ReplaceSiteURL {
    public $new_site;
    public $old_site;
    public $wp_path;


    function __construct($new_site, $path, $old_site = '') {
        $this->new_site = untrailingslashit(preg_match('/^https?:\/\//i', $new_site) ? $new_site : 'http://'.$new_site );
        $old_site = (!empty($old_site) && !preg_match('/^https?:\/\//i', $old_site) ? 'http://'.$old_site : $old_site );
        $this->old_site = untrailingslashit(empty($old_site) ? home_url() : $old_site);
        $this->wp_path = $path;
    }

    // wp_options
    public function options() {
        global $wpdb;

        $count = 0;
        $sql = $wpdb->prepare(
            "SELECT * from `{$wpdb->options}` where option_value like \"%s\"",
            '%'.untrailingslashit($this->old_site).'%'
            );
        $results = $wpdb->get_results($sql);
        foreach ($results as $result){
            $sql = $wpdb->prepare(
                "UPDATE `{$wpdb->options}` SET option_value=\"%s\" where option_id = %d",
                $this->replace($this->old_site, $this->new_site, $result->option_value) ,
                $result->option_id
                );
            $wpdb->query($sql);
            $count++;
        }
        return $count;
    }

    // wp_posts
    public function posts() {
        global $wpdb;
        $sql = $wpdb->prepare(
            "UPDATE `{$wpdb->posts}` SET post_content=REPLACE(post_content, \"%s\",\"%s\") where post_content like \"%s\"",
            $this->old_site,
            $this->new_site,
            "%{$this->old_site}%"
        );
        return $wpdb->query($sql);
    }


    // wp_postmeta
    public function postmeta() {
        global $wpdb;

        $count = 0;
        $sql = $wpdb->prepare(
            "SELECT * from `{$wpdb->postmeta}` where meta_value like \"%s\"",
            '%'.untrailingslashit($this->old_site).'%'
            );
        $results = $wpdb->get_results($sql);
        foreach ($results as $result){
            $sql = $wpdb->prepare(
                "UPDATE `{$wpdb->postmeta}` SET meta_value=\"%s\" where meta_id = %d",
                $this->replace($this->old_site, $this->new_site, $result->meta_value) ,
                $result->meta_id
                );
            $wpdb->query($sql);
            $count++;
        }
        return $count;
    }

    // wp_usermeta
    public function usermeta() {
        global $wpdb;

        $count = 0;
        $sql = $wpdb->prepare(
            "SELECT * from `{$wpdb->usermeta}` where meta_value like \"%s\"",
            '%'.untrailingslashit($this->old_site).'%'
            );
        $results = $wpdb->get_results($sql);
        foreach ($results as $result){
            $sql = $wpdb->prepare(
                "UPDATE `{$wpdb->usermeta}` SET meta_value=\"%s\" where umeta_id = %d",
                $this->replace($this->old_site, $this->new_site, $result->meta_value) ,
                $result->umeta_id
                );
            $wpdb->query($sql);
            $count++;
        }
        return $count;
    }

    // wp_commentmeta
    public function commentmeta() {
        global $wpdb;

        $count = 0;
        $sql = $wpdb->prepare(
            "SELECT * from `{$wpdb->commentmeta}` where meta_value like \"%s\"",
            '%'.untrailingslashit($this->old_site).'%'
            );
        $results = $wpdb->get_results($sql);
        foreach ($results as $result){
            $sql = $wpdb->prepare(
                "UPDATE `{$wpdb->commentmeta}` SET meta_value=\"%s\" where meta_id = %d",
                $this->replace($this->old_site, $this->new_site, $result->meta_value) ,
                $result->meta_id
                );
            $wpdb->query($sql);
            $count++;
        }
        return $count;
    }

    private function replace($origin, $replaced, $value) {
        if ( is_serialized($value) ) {
            $value = maybe_unserialize($value);
            $value = $this->deep_replace($origin, $replaced, $value);
            $value = maybe_serialize($value);
        } else {
            $value = str_replace($origin, $replaced, $value);
        }
        return $value;
    }

    private function deep_replace($origin, $replaced, $datas) {
        if ( is_array($datas) || is_object($datas) ) {
            foreach ( $datas as &$data ) {
                if ( is_array($data) || is_object($data) ) {
                    $data = $this->deep_replace($origin, $replaced, $data);
                } else {
                    $data = str_replace($origin, $replaced, $data);
                }
            }
        }
        return $datas;
    }
}
