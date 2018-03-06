<?php
namespace System\Lib;
class Page
{
    public $page;
    public $epage;
    public $total;
    private $url;

    function show()
    {
        return $this->page($this->total, $this->epage, $this->page, $this->url);
    }

    // 分页函数
    function page($num, $perpage, $curpage, $mpurl = '')
    {
        if ($mpurl == '') {
            $mpurl = "?" . $_SERVER['QUERY_STRING'];
            $page = $_REQUEST['page'];
            $mpurl = str_replace("&page=$page", '', $mpurl) . '&';
        } else {
            //$mpurl .= strpos($mpurl, '?') ? '&amp;' : '?';
            if (strpos($mpurl, '?') === false)
                $mpurl .= '?';
            else
                $mpurl .= '&amp;';
        }
        $multipage = '';
        if ($num > $perpage) {
            $page = 7;
            $offset = 3;
            $pages = ceil($num / $perpage);
            if ($page > $pages) {
                $from = 1;
                $to = $pages;
            } else {
                $from = $curpage - $offset;
                $to = $curpage + $page - $offset - 1;
                if ($from < 1) {
                    $to = $curpage + 1 - $from;
                    $from = 1;
                    if (($to - $from) < $page && ($to - $from) < $pages) {
                        $to = $page;
                    }
                } elseif ($to > $pages) {
                    $from = $curpage - $pages + $to;
                    $to = $pages;
                    if (($to - $from) < $page && ($to - $from) < $pages) {
                        $from = $pages - $page + 1;
                    }
                }
            }
            $multipage = ($curpage - $offset > 1 && $pages > $page ? '<a href="' . $mpurl . 'page=1" class="p_redirect" title="首页">&laquo;</a>' : '') . ($curpage > 1 ? '<a href="' . $mpurl . 'page=' . ($curpage - 1) . '" class="p_redirect" title="上一页">&#8249;</a>' : '');
            for ($i = $from; $i <= $to; $i++) {
                $multipage .= $i == $curpage ? '<span class="p_curpage">' . $i . '</span>' : '<a href="' . $mpurl . 'page=' . $i . '" class="p_num">' . $i . '</a>';
            }
            $multipage .= ($curpage < $pages ? '<a href="' . $mpurl . 'page=' . ($curpage + 1) . '" class="p_redirect" title="下一页">&#8250;</a>' : '') . ($to < $pages ? '<a href="' . $mpurl . 'page=' . $pages . '" class="p_redirect" title="尾页">&raquo;</a>' : '');
            $multipage = $multipage ? '<div class="p_bar"><span class="p_info">总' . $num . '条</span>' . $multipage . '</div>' : '';
        }
        return $multipage;
    }
}