<?php

session_start();

include 'init.php';


$action  = isset($_POST['action']) ? $_POST['action'] : '';
$type    = isset($_POST['type']) ? (int)$_POST['type'] : 0;
$keyword = isset($_POST['keyword']) ? strtolower($_POST['keyword']) : '';
$page    = isset($_POST['page']) ? (int)$_POST['page'] : 0;
$idx     = isset($_POST['idx']) ? (int)$_POST['idx'] : 0;
$sess    = isset($_POST['sess']) ? (int)$_POST['sess'] : 0;


switch ($action)
{

	case 'search':

		if ($sess === 0)
		{
			unset($_SESSION['sess_block']);

			$test = $DB->query('SELECT *, count(*) as c FROM session WHERE `type` = "' . $type . '" AND lower(keyword) = "' . $DB->quote($keyword) . '"')->row();

			if ($test->c == 0)
			{
				$total = $o->post('type', $type)->post('keyword', $keyword)->get_count();

				if ($total > 0)
				{
					$DB->insert('session', array(
						'type'    => $type,
						'keyword' => $keyword,
						'total'   => $total));

					$id = $DB->insert_id();
					$count = 0;
				}
			}
			else
			{
				$id = $test->id;
				$total = $test->total;
				$count = ($test->page - 1) * 10 + $test->idx;
			}

			echo json_encode(array(
				'result' => 'sess',
				'value'  => $id,
				'count'  => $count,
				'total'  => $total));
		}
		else
		{
			$res = $DB->query('SELECT * FROM `session` WHERE `id` = ' . $sess)->row();

			/* Trang hiện tại */
			$page = (int)$res->page;
			/* Số dữ liệu trên 1 trang đã thực hiện */
			$idx = (int)$res->idx;
			/* Tổng số trang */
			$pages = ceil($res->total / 10);

			if ($page === 0)
			{
				$page = 1;
			}


			if ($idx < 10)
			{
				$idx++;
			}
			else
			{
				$idx = 1;
				$page++;
			}

			/* Tổng số dữ liệu đã lấy được (tính cả trùng lắp -> skip) */
			$count = ($page - 1) * 10 + $idx;

			/* Không thực hiện nếu đã lấy đủ dữ liệu */
			if ($count <= $res->total && $page <= $pages)
			{
				$sess_page = isset($_SESSION['sess_page']) && $_SESSION['sess_page']['__EVENTVALIDATION'] !== '';

				if (!isset($_SESSION['sess_block']) || !$sess_page)
				{
					/* Go to first pagination block */
					$o->post('type', $res->type)->post('keyword', $res->keyword);

					/* Block page: 10 pages per block */
					$_SESSION['block_page'] = 1;
					$_SESSION['page'] = 1;

					/* Set VIEWSTATE for this block pagination */
					$session = $o->session;
					$_SESSION['sess_block'] = $session;
					$_SESSION['sess_page'] = $session;
				}


				if ($page > 1)
				{
					/* JUMP to block */
					$step = ceil($page / 10) - $_SESSION['block_page'];

					if ($step != 0)
					{
						// Load session block
						$o->session = $_SESSION['sess_block'];

						// Go to next
						if ($step > 0)
						{
							for ($i = 0; $i < $step; $i++)
							{
								$o->post('page', ($_SESSION['block_page'] + $i) * 10 + 1);
							}
						}
						elseif ($step < 0)
						{
							// Go back
							$step = abs($step);

							for ($i = 1; $i <= $step; $i++)
							{
								$o->post('page', ($_SESSION['block_page'] - $i) * 10);
							}
						}

						$_SESSION['block_page'] = ceil($page / 10);

						/* Store session block */
						$_SESSION['sess_block'] = $o->session;
						$_SESSION['sess_page']  = $o->session;
					}


					if ($page % 10 !== 1 && $page != $_SESSION['page'])
					{
						// Load session block
						$o->session = $_SESSION['sess_block'];
						// Move to new page
						$o->post('page', $page);

						/* Save session page */
						$_SESSION['sess_page'] = $o->session;
					}


					/* Store page number */
					$_SESSION['page'] = $page;
				}

				/* Set VIEWSTATE from session */
				$o->session = $_SESSION['sess_page'];

				/* Get detail information */
				$vars = $o->post('detail', $idx)->parse();
                
                if (empty($vars))
                {
                    echo json_encode(array('result' => 'retry'));
                    return;
                }

				$vars['sess_id'] = $sess;

				/* Kiểm tra xem thông tin công ty này đã có trong database hay chưa */
				$test = $DB->query('SELECT count(*) as c FROM `info` WHERE `number` = "' . $vars['number'] . '"')->row();

				if ($test->c == 0)
				{
					$DB->insert('info', $vars);
				}

				$DB->update('session', array(
					'id' => $res->id,
					'page' => $page,
					'idx' => $idx), '`id`=' . $res->id);


				echo json_encode(array(
					'result' => 'process',
					'total' => $res->total,
					'count' => $count));
			}

			if ($count >= $res->total)
			{
				echo json_encode(array('result' => 'finish', 'total' => $res->total));
			}
		}

		break;


    case 'delete':
    
        $DB->delete('session', '`id`=' . $sess);
        $DB->delete('info', '`sess_id`=' . $sess);

        $test = $DB->query('SELECT count(*) as c FROM `session`')->row();
        if ($test->c === 0)
        {
        	$DB->delete('session');
        }

        $test = $DB->query('SELECT count(*) as c FROM `info`')->row();
        if ($test->c === 0)
        {
        	$DB->delete('info');
        }

        $DB->query('OPTIMIZE TABLE `info`, `session`');

        echo json_encode(array('result' => 'ok'));

    break;
    

    case 'export':

    	require_once 'includes/PHPExcel.php';

    	$res = $DB->query('SELECT * FROM `info` WHERE `sess_id` = ' . $sess . ' AND `status` = "ACTIVE"')->rows();

    	if (!isset($res[0]))
    	{
    		echo json_encode(array('result' => 'fail'));
    		return;
    	}

		$excel = new PHPExcel();

		$excel->getProperties()->setCreator("Quoc Hung Nguyen")
		                        ->setLastModifiedBy("Quoc Hung Nguyen")
		                        ->setTitle("PHPExcel")
		                        ->setSubject("PHPExcel")
		                        ->setDescription("Test document for PHPExcel, generated using PHP classes.")
		                        ->setKeywords("office PHPExcel php")
		                        ->setCategory("Result file");

		$i = 0;
		$th = $res[0];
		$cols = range('B', 'Z');
		unset($th->id, $th->sess_id);

		/* Column A (số thứ tự) */
	    $excel->setActiveSheetIndex(0)->setCellValue('A4', 'STT');
	    $excel->getActiveSheet()->getStyle('A4')->getFont()->setBold(true);
	    $excel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);

	    /* Next columns */
		foreach ($th as $k => $v)
		{
		    $excel->setActiveSheetIndex(0)->setCellValue($cols[$i].'4', @$o->info_fields[$k]);
		    $excel->getActiveSheet()->getStyle($cols[$i].'4')->getFont()->setBold(true);
		    if (in_array($k, array('name', 'agent')))
		    {
		    	$excel->getActiveSheet()->getColumnDimension($cols[$i])->setWidth(50);
		    }
		    else
		    {
				$excel->getActiveSheet()->getColumnDimension($cols[$i])->setAutoSize(true);
		    }
		    $i++;
		}

		/* Rows */
		$stt = 1;
		foreach ($res as $i => $r)
		{
		    $l = $i + 5;
		    $j = 0;
		    unset($r->id, $r->sess_id);

		    $excel->setActiveSheetIndex(0)->setCellValue('A'.$l, $stt);

		    foreach ($r as $v)
		    {
		        $excel->setActiveSheetIndex(0)->setCellValue($cols[$j].$l, $v);
		        $j++;
		    }

		    $stt++;
		}


		$excel->getActiveSheet()->setTitle('Business companies');


		$objWriter = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
		$filename = 'list.xlsx';
		$objWriter->save($filename);

		echo json_encode(array('result' => 'ok', 'filename' => $filename));

    break;


	case 'test':

		$t1 = time_float();
		$o->search(1, 'seafood', 2, 1)->parse();

		echo round(time_float() - $t1, 2);

	break;


	case 'sess_list':

		include 'sess_list.php';

	break;
}
