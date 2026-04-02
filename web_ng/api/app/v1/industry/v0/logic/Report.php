<?php

namespace app\v1\industry\v0\logic;

use app\v1\common\logic\Base;
use app\v1\job\v0\logic\JobInfo;
use app\v1\opcode\VerifyOpcode;
use app\v1\system\v0\logic\Notice;
use xphp\Images;

/**
 * note          报告逻辑
 * @author       wanggongxi@vinchin.com
 * @date         2024/8/6 10:32
 * @version      1.0.0
 * @copyright    Copyright 2024 vinchin.com
 */
class Report extends Base
{
    /**
     * @var bool
     */
    private $isEnch;

    /**
     * 构造方法
     */
    public function __construct()
    {
        parent::__construct();
        $enterprise = xphp_get_config('app', 'SYSTEM_INFO')['enterprise'];
        if ($enterprise == 'enterprise_en') {
            // 英文版
            $this->isEnch = false; // 是否中英文对照
        } else {
            $this->isEnch = true; // 是否中英文对照
        }
    }

    /**
     * 报告详情
     * @param string|array $uuid 报告uuid|数组
     * @param bool         $flag 是否下载查询
     * @return array
     */
    public function viewReport($uuid, $flag = false): array
    {

        $field = 'ir.*,bu.user_name';
        $table = "industry_report ir left join bd_user bu on ir.user_uuid=bu.user_uuid";
        if (is_array($uuid)) {
            $sql = "select {$field} from {$table} where ir.task_uuid = ? and ir.agent_uuid = ?";
            $params = [$uuid['job_uuid'], $uuid['agent_uuid']];
        } else {
            $sql = "select {$field} from {$table} where ir.report_uuid = ?";
            $params = [$uuid];
        }

        $data = $this->dbSelect($sql, $params, \PDO::FETCH_ASSOC);
        if (empty($data)) {
            return [
                'code' => 1,
                'msg' => xphp_get_lang('WEB_PLATFORM_INDUSTRY_REPORT_NOT_EXIST')
            ];
        }

        $info = $data[0];
        // 处理下一些结构
        $info['report_water'] = json_decode($info['report_water'], true);
        $info['screen_water'] = json_decode($info['screen_water'], true);
        $info['logo'] = json_decode($info['logo'], true);
        $info['email_notice'] = json_decode($info['email_notice'], true);
        $summary = json_decode($info['summary'], true);
        $description = json_decode($info['description'], true);

        // 需要处理下顺序， 因为模板里面存储的是绝对定位，但是在报告里面的话，是要根据内容填充，高度是可变的，需要改为相对定位
        $array1 = $this->calcArray($summary, 0, 1);
        $baseTop = explode(',', $summary['item_description']['position'])[0];
        $array2 = $this->calcArray($description, $baseTop);
        // 最后排序
        $array = $this->calcTopPosition(array_merge($array1, $array2));
        $info['position'] = $array;
        // 获取审批流
        if (!empty($info['approval_list'])) {
            // 处理下现在的审批流
            $info['approval_list'] = json_decode($info['approval_list'], true);
        } else {
            $info['approval_list'] = $this->makeApproval(
                $info['user_uuid'],
                $info['user_name'],
                $info['approval_uuid'],
                $info['status'] != 9
            );
        }

        // 需要查询出任务相关的一些数据 主机名、ip、最终结果、时间点、模块名称
        // 网络结果、心跳结果、数据完整性结果、文件一致性结果、开机结果、
        // 病毒查杀、文件比对都在web根据需要请求单独的接口
        $info['vm_uuid'] = '';
        $info['vnc_url'] = '';
        // 获取真实数据
        $sql = 'SELECT ssr.item_uuid,ssr.timestamp,ssr.extension_info,ssr.doc_compare_result_json,ssi.doc_list_json,
                        ssr.screen_shot_path,ba.ip,ba.hostname,ssr.item_error_code,ssi.new_uuid,ve.console_url
				from sr_surebackup_report ssr
				inner join industry_report ir on ir.report_uuid = ssr.report_uuid 
				left join sr_surebackup_item ssi on
							     ssr.item_uuid = ssi.item_uuid and ssi.sr_task_uuid = ssr.sr_task_uuid
				left join vm_emd ve on ve.uuid = ssi.new_uuid
							  
                LEFT JOIN bd_agent ba on ba.agent_uuid = ir.agent_uuid
                where ssr.report_uuid = ? limit 1';
        $data = $this->dbSelect($sql, [$info['report_uuid']], \PDO::FETCH_ASSOC);
        if (!empty($data)) {
            $extensionInfo = json_decode($data[0]['extension_info'], true);
            if (empty($extensionInfo['boot_verify_result'])) {
                $openResult = false;
            } else {
                $openResult = intval($extensionInfo['boot_verify_result']) == 5;
            }
            $info['host'] = $data[0]['hostname'] ?? '&nbsp;'; // 主机名
            $info['ip'] = $data[0]['ip'] ?? '&nbsp;'; // ip
            $info['timepoint'] = $data[0]['timestamp'] ?? '&nbsp;'; // 时间点
            $info['module'] = xphp_get_lang('UI_PUBLIC_OS'); // 模块名称
            $info['integrality'] = $openResult; // 开机验证结果
            // 这里来解析下文件比对
            $filesFlag = false;
            $fileMethod = 'MD5';
            $filesInfos = json_decode($data[0]['doc_compare_result_json'], true);

            if (!empty($filesInfos)) {
                $total = $sames = 0;
                // 没有异常的表示全部是成功
                if (is_array($filesInfos)) {
                    foreach ($filesInfos as $filesInfo) {
                        $total += intval($filesInfo['total_compare_count']);
                        $sames += intval($filesInfo['same_file_count']);
                        if (!empty($filesInfo['checkout_mode']) && intval($filesInfo['checkout_mode']) !== 1) {
                            $fileMethod = $filesInfo['checkout_mode'];
                        }
                    }
                }
                $filesFlag = $total == $sames;
            }
            if (empty($data[0]['doc_list_json'])) {
                // 表示没有设置文件比对-创建任务的时候
                $filesFlag = true; // 设置为一致
            }
            $info['files'] = $filesFlag; // 文件对比结果
            // 最终结果
            if ($info['result'] == 1) {
                $info['results'] = true;
            } else {
                $info['results'] = $filesFlag && $openResult;
                if ($info['screen_result'] != 0) {
                    $info['results'] = $info['results'] && $info['screen_result'] == 1;
                }
            }

            $info['file_method'] = $fileMethod; // 文件比对验证方式
            // 复制文件到指定的目录下
            if (!empty($data[0]['screen_shot_path'])) {
                $openUrl = DATA_PATH . '/industry/pdf/' . md5($info['report_uuid']) . '.png';
                if (!file_exists($openUrl)) {
                    $imageData = file_get_contents($data[0]['screen_shot_path']);
                    $base64EncodedData = base64_encode($imageData);

                    // 解码 Base64 字符串并写入新文件
                    $decodedData = base64_decode($base64EncodedData);
                    if (file_put_contents($openUrl, $decodedData) !== false) {
                        //return "File copied successfully using Base64.";
                    } else {
                        //return "Failed to write the destination file.";
                    }
                }
                $openUrl = './web_ng/api/data/industry/pdf/' . md5($info['report_uuid']) . '.png';
            }
            $info['open'] = $openUrl ?? ''; // 开机结果图片
            // 把 0.0.0.0 换成当前的域名
            $newconsoleurl = str_replace(
                '0.0.0.0:6080',
                $_SERVER['HTTP_HOST'] . '/web_console',
                $data[0]['console_url']
            );

            $newconsoleurl = 'https://' . $_SERVER['HTTP_HOST'] . ':6080/jump.html?url=' . urlencode($newconsoleurl);
            $info['vm_uuid'] = $data[0]['new_uuid'];
            $info['vnc_url'] = $newconsoleurl;
        } else {
            // 测试伪造假数据暂时
            $info['host'] = '&nbsp;'; // 主机名
            $info['ip'] = '&nbsp;'; // ip
            $info['timepoint'] = '&nbsp;'; // 时间点
            $info['module'] = '&nbsp;'; // 模块名称
            $info['results'] = false; // 最终结果
            $info['integrality'] = false; // 开机验证结果
            $info['files'] = false; // 文件对比结果
            $info['file_method'] = 'MD5'; // 文件比对验证方式
            $info['open'] = ''; // 开机结果
        }

        // 截屏比对结果
        $info['screen_list'] = json_decode($info['screen_list'], true);
        // 文件比对自定义展示属性
        $info['document_custom'] = json_decode($info['document_custom'], true);

        // 返回二维码 这里可能需要借助下 update.vinchin.cn的域名进行中转处理 暂时就以静态的二维码
        $url = getDomain(true) . '/industry_report.php?data=' . $info['report_uuid'];
        $outfile = md5($url) . '.png';
        $html = xphp_get_lang('WEB_PLATFORM_INDUSTRY_REPORT_TITLE') . '：' .
            $info['name'] . '\n' . xphp_get_lang('WEB_PLATFORM_INDUSTRY_REPORT_MACHINE') . '：'
            . $info['host'] . '\n' . xphp_get_lang('UI_VOL_CDP_TAKEOVER_POINT') . '：' . $info['timepoint'];
        $info['qrcode'] = xphp_qrcode($html, $outfile);

        // auditor_sign_sys
        $info['auditor_sign_sys'] = ''; // 审核员(系统签字)

        $info['operator_sign_sys'] = ''; // 操作员(系统签字)
        if (!empty($info['approval_list'])) {
            $info['operator_sign_sys'] = $info['approval_list'][0]['user_name'];
            if ($info['status'] == 1) {
                // 报告已经归档
                $approvallist = $info['approval_list'];
                array_shift($approvallist); // 移除第一个元素
                array_pop($approvallist);   // 移除最后一个元素
                $auditor = implode(' ', array_column($approvallist, 'user_name')); // 审批过程中的所有的审核人员汇总
                $info['auditor_sign_sys'] = $auditor;
            }
        }

        if ($flag) {
            return $info;
        }

        $user = xphp_get_user_info();
        // 判断下当前的报告是否属于当前登录的用户
        $info['is_now_user'] = $info['user_uuid'] == $user['userUuid'];

        $item = [$info['name'], xphp_get_lang('WEB_PLATFORM_INDUSTRY_REPORT_LOOK')];
        $this->systemLog('PT_INDUSTRY_REPORT_OPERATE', $item);

        // 判断下当前用户是否显示审批的按钮
        $approvalFlag = false;
        $reportStatus = xphp_get_config('industry', 'REPORT_STATUS');
        if (in_array($info['status'], [$reportStatus['PENDING'], $reportStatus['APPROVALING']])) {
            // 状态是待审核/审批中
            // 并且刚好是当前用户或者用户组里面的审核
            if ($info['now_user_uuid'] == $user['userUuid'] && $info['now_user_type'] == 1) {
                // 并且刚好是当前用户
                $approvalFlag = true;
            } elseif ($info['now_user_type'] == 2) {
                $sql = "select id from mt_user_user_group where user_group_uuid = ? and user_uuid = ? limit 1";
                $check = $this->dbSelect($sql, [$info['now_user_uuid'], $user['userUuid']]);
                if (!empty($check)) {
                    // 并且刚好是当前用户所在的用户组
                    $approvalFlag = true;
                }
            }
        }

        $info['approval_flag'] = $approvalFlag;

        return [
            'code' => 0,
            'msg' => $info
        ];
    }

    /**
     * 创建任务生成报告相关信息
     * @param array $param 任务和报告信息
     *                     job_uuid 任务uuid
     *                     job_name 任务名称
     *                     timepoint_uuid 时间点
     *                     module_type 模块类型
     *                     approval_uuid 审批流uuid
     *                     temp_uuid 模板uuid
     *                     template array 模板自定义信息
     *                     -- item 模板输入框自定义信息
     *                     -- item_plan 方案自定义内容
     *                     -- item_result 结论自定义内容
     *                     -- width 宽度
     * @return array
     */
    public function jobReport(array $param): array
    {

        $sql = "select * from industry_temp where temp_uuid = ?";
        $data = $this->dbSelect($sql, [$param['temp_uuid']]);
        if (empty($data)) {
            return [
                'code' => 1,
                'msg' => xphp_get_lang('WEB_PLATFORM_INDUSTRY_TEMPLATE_NOT_EXIST')
            ];
        }
        $template = $data[0];
        $user = xphp_get_user_info();
        // 删除之前和这个任务关联的报告列表
        $this->dbExec('delete from industry_report where task_uuid = ?', [$param['job_uuid']]);

        // 组装下详情和概要的结构
        $item = (new Template())->makeItem($param['template']['item']);
        $status = xphp_get_config('industry', 'REPORT_STATUS');
        $data = [
            $param['temp_uuid'],
            $item['summary']['item_title']['value'] ?? xphp_get_lang('WEB_PLATFORM_INDUSTRY_REPORT_VERIFY'),
            $param['job_uuid'],
            $param['job_name'],
            $user['userUuid'],
            date(xphp_get_config('special', 'dateformat')),
            $status['GENERATED'],
            $template['virus_num'] ?? 1,
            $template['document_num'],
            $template['logo'],
            $template['report_water'],
            $template['screen_water'],
            $template['email_notice'],
            $template['document_custom'],
            json_encode($item['summary']),
            json_encode($item['description']),
            $param['template']['item_plan'],
            $param['template']['item_result'],
            $param['template']['width'],
            $param['module_type'],
            $param['approval_uuid'],
        ];
        // 构建批量插入的SQL语句
        $values = [];
        $allData = [];
        // 判断下有多少个客户端
        foreach ($param['agent'] as $items) {
            $values[] = '(' . str_repeat('?,', count($data) + 2) . '?)';
            $allData = array_merge($allData, [xphp_uuid()], $data, [$items['timepoint_uuid'], $items['agent_uuid']]);
        }

        $sql = 'insert into industry_report
    (report_uuid,temp_uuid,`name`,task_uuid,task_name,user_uuid,create_time,status,
     virus_num,document_num,logo,report_water,screen_water,
     email_notice,document_custom,summary,description,item_plan,item_result,
     item_width,module_type,approval_uuid,timepoint_uuid,agent_uuid)
     values ' . implode(', ', $values);

        $return = $this->dbExec($sql, $allData);
        if (empty($return)) {
            return [
                'code' => 1,
                'msg' => xphp_get_lang('WEB_PUBLIC_FAILURE')
            ];
        }

        return [
            'code' => 0,
            'msg' => xphp_get_lang('WEB_PUBLIC_SUCCESS')
        ];
    }

    /**
     * 生成报告
     * @param array $param 请求参数
     * @return array
     */
    public function makeReport($param = []): array
    {
        $this->checkIsGlobal();

        if (!empty($param['test'])) {
            // 测试生成报告
            if ($param['test'] == 2) {
                return $this->jobReport($param);
            }
            return $this->testMakeReport($param);
        }

        // 先保存报告
        $save = $this->saveReport($param['report_data']);
        if ($save['code'] != 0) {
            return [
                'code' => 1,
                'msg' => xphp_get_lang('WEB_PLATFORM_INDUSTRY_REPORT_SUBMIT_ERROR')
            ];
        }

        $data = $param['data'];

        // 获取报告配置的css内容
        #老框架的主配置目录 /vinchin/
        $dir = dirname(__DIR__, 7) . '/';

        // 正则替换所有的相对位置为绝对路径
        $data = preg_replace_callback(
            '/<img\s+[^>]*?src="([^"]*)"/i',
            function ($matches) use ($dir) {
                $src = $matches[1];
                // 检查src是否已经是绝对路径（以http://或https://开头）
                if (preg_match('/^https?:\/\//i', $src)) {
                    $src = explode('web_ng/', $src);
                    return '<img src="' . $dir . 'web_ng/' . $src[1] . '"'; // 如果是绝对路径，则保持不变
                } else {
                    // 如果是相对路径，则添加基础URL
                    return '<img src="' . $dir . '/' . ltrim($src, './') . '"';
                }
            },
            $data
        );

        $style = '
        <link rel="stylesheet" type="text/css" href="' . $dir . '/assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css"/>
<link rel="stylesheet" type="text/css" href="' . $dir . '/assets/global/plugins/icheck/skins/square/blue.css" />
<link rel="stylesheet" type="text/css" href="' . $dir . '/assets/global/plugins/icheck/skins/all.css" />
<link rel="stylesheet" type="text/css" href="' . $dir . '/css/platform/industry/template.css"/>
<link rel="stylesheet" type="text/css" href="' . $dir . '/css/platform/industry/report.css"/>';
        $html = '
        <!DOCTYPE html>  
        <html>  
        <head>  
            <title>report</title>  
            ' . $style . ' 
        </head>  
        <body>  
           ' . $data . '
        </body>  
        </html>';

        $path = DATA_PATH . '/industry/pdf/';
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }

        if (!file_put_contents($path . md5($param['report_uuid']) . '.txt', $html)) {
            return [
                'code' => 1,
                'msg' => xphp_get_lang('WEB_PLATFORM_INDUSTRY_REPORT_WRITE_ERROR')
            ];
        }

        // 报告缓存文件写入成功，更新报告后续信息
        if ($this->afterReport($param['report_uuid'])) {
            if (!empty($param['source']) && $param['source'] == 2) {
                // 从任务详情生成的报告，需要判断下是否是最后一个设备生成报告。是的话，需要停止任务操作
                $this->afterJob($param['report_uuid']);
            }
            return [
                'code' => 0,
                'msg' => 'success'
            ];
        }

        return [
            'code' => 1,
            'msg' => 'fail'
        ];
    }

    /**
     * 判断任务关联的报告是否全部生成，是的话，停止当前任务,并且启动策略
     * @param string $reportUuid 报告uuid
     * @return bool
     */
    private function afterJob(string $reportUuid)
    {
        $task = $this->dbSelect('SELECT task_uuid FROM industry_report WHERE report_uuid = ? limit 1', [$reportUuid]);
        $taskUuid = $task[0]['task_uuid'];
        if (empty($taskUuid)) {
            return false;
        }
        $sql = 'SELECT count(*) num from industry_report 
                WHERE task_uuid = ? and `status` = ?';
        $total = $this->dbSelect($sql, [$taskUuid, xphp_get_config('industry', 'REPORT_STATUS')['GENERATED']]);
        if (empty($total[0]['num'])) {
            // 发送停止任务消息并启动策略
            $msg = [
                'task_uuid' => $taskUuid
            ];
            // 启动策略
            $opName = 'SR_OP_CODE_SUBMIT_SUREBACKUP_GMP_REPORT';
            return $this->service()->mbSRMsgs($opName, json_encode($msg), 108, false, true);
        }
        return true;
    }

    /**
     * 测试生成报告
     * @param array $param 请求参数
     * @return array
     */
    private function testMakeReport($param = [])
    {

        $sql = "select * from industry_temp where temp_uuid = ?";
        $datas = $this->dbSelect($sql, [$param['temp_uuid']]);
        if (empty($datas)) {
            return [
                'code' => 1,
                'msg' => xphp_get_lang('WEB_PLATFORM_INDUSTRY_TEMPLATE_NOT_EXIST')
            ];
        }
        $template = $datas[0];
        $approvalUuid = $datas[0]['approval_uuid'];
        if (empty($template['approval_uuid'])) {
            // 查询出最新的审批流的uuid
            $sql = "select approval_uuid from approval_list order by id desc limit 1";
            $approval = $this->dbSelect($sql);
            $approvalUuid = $approval[0]['approval_uuid'];
        }

        $user = xphp_get_user_info();
        // 组装下详情和概要的结构
        $status = xphp_get_config('industry', 'REPORT_STATUS');

        $summary = json_decode($datas[0]['summary'], true);
        $data = [
            xphp_uuid(),
            $param['temp_uuid'],
            $summary['item_title']['value'] ?? 'xxx report', // 报告名称，暂定就为 xxx验证报告，后续获取到ip信息后进行替换
            xphp_uuid(),
            'test_report_' . date(xphp_get_config('special', 'dateformat')),
            $user['userUuid'],
            date(xphp_get_config('special', 'dateformat')),
            $status['GENERATED'],
            $template['virus_num'] ?? 1,
            $template['document_num'],
            $template['logo'],
            $template['report_water'],
            $template['screen_water'],
            $template['email_notice'],
            $template['document_custom'],
            ($template['summary']),
            ($template['description']),
            $template['item_plan'],
            $template['item_result'],
            $template['item_width'],
            xphp_uuid(),
            1,
            $approvalUuid,
        ];
        $sql = 'insert into industry_report
    (report_uuid,temp_uuid,`name`,task_uuid,task_name,user_uuid,create_time,status,
     virus_num,document_num,logo,report_water,screen_water,
     email_notice,document_custom,summary,description,item_plan,
     item_result,item_width,timepoint_uuid,module_type,approval_uuid)
     values (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)';

        if ($this->dbExec($sql, $data)) {
            return [
                'code' => 0,
                'msg' => $data[0]
            ];
        }
        return [
            'code' => 1,
            'msg' => xphp_get_lang('WEB_PUBLIC_FAILURE')
        ];
    }

    /**
     * 保存报告
     * @param array $param 请求参数
     * @return array
     */
    public function saveReport($param = []): array
    {
        $this->checkIsGlobal();

        $sql = "select * from industry_report where report_uuid = ?";
        $data = $this->dbSelect($sql, [$param['report_uuid']]);
        if (empty($data)) {
            return [
                'code' => 1,
                'msg' => xphp_get_lang('WEB_PLATFORM_INDUSTRY_REPORT_NOT_EXIST')
            ];
        }
        $datas = [];
        if (!empty($param['screen_list'])) {
            $datas[] = json_encode($param['screen_list']);
        } else {
            $datas[] = $data[0]['screen_list'];
        }
        if (!empty($param['item_plan'])) {
            $datas[] = $param['item_plan'];
        } else {
            $datas[] = $data[0]['item_plan'];
        }
        if (!empty($param['item_result'])) {
            $datas[] = $param['item_result'];
        } else {
            $datas[] = $data[0]['item_result'];
        }
        // 处理下自定义的字段
        $summary = json_decode($data[0]['summary'], true);
        $oldSummary = [];
        foreach ($summary as $key => $items) {
            if (in_array($key, ['item_field', 'item_textarea', 'item_datetime'])) {
                $lists = $items['list'];
                foreach ($lists as $items2) {
                    $oldSummary[$items2['uuid']] = $items2['position'];
                }
            } else {
                $oldSummary[$items['uuid']] = $items['position'];
            }
        }

        $newSummary = [];
        foreach ($param['data'] as $item) {
            if (in_array($item['name'], ['item_field', 'item_textarea', 'item_datetime'])) {
                // 自定义字段、文本和时间控件
                $newSummary[$item['name']]['show'] = true;
                $arr = $newSummary[$item['name']]['list'] ?? [];
                $list = [
                    'name' => $item['val'],
                    'en_name' => $item['en_val'] ?? '',
                    'value' => $item['value'],
                    'en_value' => $item['en_value'] ?? '',
                    'uuid' => $item['uuid'],
                    'position' => $oldSummary[$item['uuid']],
                ];
                $newSummary[$item['name']]['list'] = array_merge($arr, [$list]);
            } else {
                $newSummary[$item['name']] = [
                    'show' => true,
                    'uuid' => $item['uuid'],
                    'position' => $oldSummary[$item['uuid']],
                    'value' => $item['value'],
                    'en_value' => $item['en_value'] ?? '',
                ];
            }
        }
        $summary = array_merge($summary, $newSummary);
        $datas[] = json_encode($summary);
        $datas[] = $param['report_title'];
        $datas[] = $param['screen_result'] ?? 0;
        $datas[] = $param['report_uuid'];

        $sql = "update industry_report
                    set screen_list=?,item_plan=?,item_result=?,summary=?,`name`=?,`screen_result`=?
                    where report_uuid = ?";

        // 写日志
        $item = [$data[0]['name'], xphp_get_lang('WEB_PLATFORM_INDUSTRY_REPORT_UPDATE')];
        $this->systemLog('PT_INDUSTRY_REPORT_OPERATE', $item);

        if ($this->dbExec($sql, $datas)) {
            return [
                'code' => 0,
                'msg' => 'success'
            ];
        } else {
            return [
                'code' => 1,
                'msg' => 'fail'
            ];
        }
    }

    /**
     * 截屏获取信息
     * @param string $reportUuid 报告uuid
     * @param int    $type       截屏类型
     *                           unknow:0,全部截屏1，客户端截屏2，验证机器截屏3
     * @return string|array
     */
    private function makeScreen(string $reportUuid, int $type = 1)
    {
        // 查询任务相关的，比如客户端信息，然后发送给后台完成截图并返回
        // 拿到截图进行判断，模板是否设置了水印
        $sql = "select screen_water,agent_uuid,task_uuid from industry_report where report_uuid = ?";
        $data = $this->dbSelect($sql, [$reportUuid]);
        if (empty($data)) {
            return [
                'code' => 1,
                'msg' => xphp_get_lang('WEB_PLATFORM_INDUSTRY_REPORT_NOT_EXIST')
            ];
        }
        // 去 sr_surebackup_item 表查询出 item_uuid
        $sql = "select item_uuid from sr_surebackup_item where ori_uuid = ? and sr_task_uuid = ?";
        $data2 = $this->dbSelect($sql, [$data[0]['agent_uuid'], $data[0]['task_uuid']]);

        if (empty($data2)) {
            return [
                'code' => 1,
                'msg' => xphp_get_lang('WEB_PLATFORM_INDUSTRY_REPORT_NOT_EXIST')
            ];
        }

        // 进行接口请求
        $msg = [
            'item_uuid' => $data2[0]['item_uuid'],
            'task_uuid' => $data[0]['task_uuid'],
            //'compare_type' => $type, // unknow:0,全部截屏1，客户端截屏2，验证机器截屏3
            'compare_type' => 1, // 因为后台暂时只做了一键截屏
        ];

        $opName = 'SR_OP_CODE_VERIFY_SCREEN_COMPARE';

        $mbResult = $this->service()->mbSRMsgs($opName, json_encode($msg), 108, true, true);

        $result = $mbResult['result'];
        $msg = $mbResult['msg'];
        $operate = (new VerifyOpcode())->getOpcodeDes($opName);
        //返回结果到UI
        if ($result) {
            return [
                'msg' => $msg,
                'water' => json_decode($data[0]['screen_water'], true)
            ];
        } else {
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }

    /**
     * 根据path和水印返回最终的截屏路径
     * @param string $url   绝对路径,图片路径
     * @param array  $water 水印信息
     * @return string
     */
    private function backPathScreen(string $url, array $water): string
    {

        if (!is_file($url)) {
            return '';
        }

        $waters = date(xphp_get_config('special', 'dateformat'));
        if ($water['flag']) {
            // 有自定义水印 那么就是时间戳 + 自定义
            $waters .= $water['value'];
        }
        $url2 = explode('.', $url);
        $length = count($url2) - 1;
        $suffix = $url2[$length];
        unset($url2[$length]);
        mkdirs(API_PATH . '/data/industry/file/');
        $urls = '/data/industry/file/' . md5($url) . '_water.' . $suffix;
        (new Images())->water($url, $waters, API_PATH . $urls, 1, 24, '#666666');
        // 进行水印填充，然后输出结果到前端
        return '/web_ng/api' . $urls;
    }

    /**
     * 一键截屏
     * @param string $reportUuid 报告uuid
     * @return array
     */
    public function allScrren(string $reportUuid): array
    {
        $this->checkIsGlobal();

        $result = $this->makeScreen($reportUuid, 1);
        //返回结果到UI
        $url1 = $this->backPathScreen($result['msg']['ori_screen_path'], $result['water']);
        $url2 = $this->backPathScreen($result['msg']['verify_screen_path'], $result['water']);

        return [
            'code' => 0,
            'msg' => [
                'produce_url' => $url1,
                'verify_url' => $url2,
                'create_time' => $result['msg']['create_time'],
            ]
        ];
    }

    /**
     * 生产截屏
     * @param string $reportUuid 报告uuid
     * @return array
     */
    public function produceScrren(string $reportUuid): array
    {
        $this->checkIsGlobal();
        $result = $this->makeScreen($reportUuid, 2);
        //返回结果到UI

        $url = $this->backPathScreen($result['msg']['ori_screen_path'], $result['water']);
        return [
            'code' => 0,
            'msg' => [
                'url' => $url,
                'create_time' => $result['msg']['create_time'],
            ]
        ];
    }

    /**
     * 验证截屏
     * @param string $reportUuid 报告uuid
     * @return array
     */
    public function verifyScrren(string $reportUuid): array
    {
        $this->checkIsGlobal();
        $result = $this->makeScreen($reportUuid, 3);
        //返回结果到UI

        $url = $this->backPathScreen($result['msg']['verify_screen_path'], $result['water']);

        return [
            'code' => 0,
            'msg' => [
                'url' => $url,
                'create_time' => $result['msg']['create_time'],
            ]
        ];
    }

    /**
     * 病毒查杀列表
     * @param array $param 请求参数
     * @return array
     */
    public function virtusLists(array $param): array
    {
        // 查询任务相关的，比如客户端信息，然后发送给后台完成
        return [
            'total' => 20,
            'normal' => 19,
            'rows' => [
                /*[
                    'num' => 1,
                    'file' => 'E:\workspace\dd.php',
                    'desc' => '--',
                    'status' => 1,
                    'status_value' => '正常',
                ],*/
            ]
        ];
    }

    /**
     * 文件比对列表
     * @param array $param 请求参数
     * @return array
     */
    public function documentLists(array $param): array
    {
        $reportUuid = $param['report_uuid'];
        $return = [
            'totals' => 0,
            'total' => 0,
            'normal' => 0,
            'rows' => [],
        ];
        // 查询任务相关的，比如客户端信息，然后发送给后台完成并返回
        $sql = 'SELECT ssr.doc_compare_result_json,ba.os_type
                FROM  sr_surebackup_report ssr,industry_report ir
	            left join bd_agent ba on ba.agent_uuid = ir.agent_uuid 
                WHERE ssr.report_uuid = ir.report_uuid AND ssr.report_uuid = ? limit 1';
        $data = $this->dbSelect($sql, [$reportUuid]);
        if (empty($data)) {
            return $return;
        }
        $json = json_decode($data[0]['doc_compare_result_json'], true);
        if (empty($json)) {
            return $return;
        }
        $return['totals'] = $return['total'] = $return['normal'] = 0;
        $osType = 1; // 默认windows
        if (!empty($data[0]['os_type']) && strpos($data[0]['os_type'], 'Linux') !== false) {
            $osType = 2; // linux
        }

        // 取出本次需要展示的数据列表
        $fileResultList = [];
        foreach ($json as $items) {
            $return['totals'] += $items['total_compare_count']; // 总的对比数量，约定的这个json最多100个，
            $return['total'] += count($items['file_result_list'] ?? []); // 本次json对比的总数
            $return['normal'] += intval($items['same_file_count'] ?? []); // 本次json对比的一致的
            $fileResultList = array_merge($fileResultList, $items['file_result_list'] ?? []);
        }
        $slicedData = $this->changeTwoArray(
            $fileResultList,
            $param['offset'],
            $param['limit'],
            $param['search']
        );
        $k = 0;
        foreach ($slicedData as $item) {
            $k++;
            $sourceFile = $item['source_file']; // 源-从验证发起的，所以源是验证
            $verifyFile = $item['aim_file']; // 生产
            $rows = [
                'num' => $param['offset'] + $k,
                'verify' => [
                    'name' => $sourceFile['file_pathname'],
                    'encryption' => empty($sourceFile['MD5']) ? '--' : $sourceFile['MD5'],
                    'size' => v1_calsize($sourceFile['file_size'], true),
                    'attribute' => $this->convertOctalToPermissions($sourceFile['file_mode'], $osType)['others'],
                    'create_time' => date('Y/m/d H:i:s', $sourceFile['create_time']),
                    'modify_time' => date('Y/m/d H:i:s', $sourceFile['modify_time']),
                ],
                'produce' => [
                    'name' => $verifyFile['file_pathname'],
                    'encryption' => empty($verifyFile['MD5']) ? '--' : $verifyFile['MD5'],
                    'size' => v1_calsize($verifyFile['file_size'], true),
                    'attribute' => $this->convertOctalToPermissions($verifyFile['file_mode'], $osType)['others'],
                    'create_time' => date('Y/m/d H:i:s', $verifyFile['create_time']),
                    'modify_time' => date('Y/m/d H:i:s', $verifyFile['modify_time']),
                ],
            ];
            if ($sourceFile['MD5'] == $verifyFile['MD5'] && !empty($sourceFile['MD5'])) {
                // 一致
                $rows['status'] = 1;
                $rows['status_value'] = xphp_get_lang('UI_PLATFORM_INDUSTRY_FILE_RESULT_SAME');
            } else {
                $rows['status'] = 2;
                $rows['status_value'] = xphp_get_lang('UI_PLATFORM_INDUSTRY_FILE_RESULT_NOTSAME');
            }
            $return['rows'][] = $rows;
        }
        return $return;
    }

    /**
     * 对二维数组进行处理后返回
     * @param array  $array   源数组
     * @param int    $offset  开始下标
     * @param int    $limit   返回长度
     * @param string $keyword 关键词
     * @return array
     */
    private function changeTwoArray(array $array, $offset = 0, $limit = 10, $keyword = '')
    {
        if (!empty($keyword)) {
            // 只能循环的处理了
            $array2 = [];
            foreach ($array as $item) {
                if (
                    strpos($item['aim_file']['file_pathname'], $keyword) !== false ||
                    strpos($item['source_file']['file_pathname'], $keyword) !== false
                ) {
                    $array2[] = $item;
                }
            }
            $array = $array2;
        }

        return array_slice($array, $offset, $limit, true);
    }

    /**
     * 接收一个八进制权限字符串，并返回一个描述性数组
     * @param string|int $octal  字符
     * @param int        $osType 操作系统类型  1windows 2linux
     * @return array
     */
    private function convertOctalToPermissions($octal, $osType = 1): array
    {
        // 将十进制数转换为二进制字符串
        $binaryString = decbin(intval($octal));
        // 计算需要补充的前导零数量
        $paddingLength = 9 - strlen($binaryString);
        if ($paddingLength > 0) {
            // 补充前导零
            $binaryString = str_pad($binaryString, 9, '0', STR_PAD_LEFT);
        }

        if ($osType == 1) {
            // windows
            // 取最后一位（如果二进制串长度超过九位）
            $binary = substr($binaryString, -1);
            // windows 只看最后一位 0就是读写。1就是只读
            if ($binary == 0) {
                $auth = xphp_get_lang('WEB_PLATFORM_INDUSTRY_REPORT_FILE_ATTR_RW');
            } else {
                $auth = xphp_get_lang('WEB_PLATFORM_INDUSTRY_REPORT_FILE_ATTR_READ');
            }
            return [
                'user' => $auth,
                'group' => $auth,
                'others' => $auth,
            ];
        }
        // 取最后九位（如果二进制串长度超过九位）
        $binary = substr($binaryString, -9);

        // 分割为用户、组和其他人的权限
        $permissions = [
            'user' => substr($binary, 0, 3),
            'group' => substr($binary, 3, 3),
            'others' => substr($binary, 6, 3)
        ];
        // 定义权限映射
        $permissionMap = [
            '100' => xphp_get_lang('WEB_PLATFORM_INDUSTRY_REPORT_FILE_ATTR_READ'), // 读
            '010' => xphp_get_lang('WEB_PLATFORM_INDUSTRY_REPORT_FILE_ATTR_RWRITE'), // 写
            '001' => xphp_get_lang('WEB_PLATFORM_INDUSTRY_REPORT_FILE_ATTR_X'), // 执行
            '110' => xphp_get_lang('WEB_PLATFORM_INDUSTRY_REPORT_FILE_ATTR_RW'), // 读写
            '101' => xphp_get_lang('WEB_PLATFORM_INDUSTRY_REPORT_FILE_ATTR_RX'), // 读执行
            '011' => xphp_get_lang('WEB_PLATFORM_INDUSTRY_REPORT_FILE_ATTR_WX'), // 写执行
            '111' => xphp_get_lang('WEB_PLATFORM_INDUSTRY_REPORT_FILE_ATTR_RWX'), // 读写执行
            '000' => xphp_get_lang('WEB_PLATFORM_INDUSTRY_REPORT_FILE_ATTR_NO')   // 无权限
        ];

        // 先把返回的mode（10进制转为2进制）
        // linux 然后匹配位数（只取后九位），有1的就显示对应的标识  'rwxrwxrwx'=> '111111111';


        // 构建结果数组
        $result = [];
        foreach ($permissions as $key => $value) {
            $result[$key] = $permissionMap[$value] ?? '';
        }

        return $result;
    }

    /**
     * 报告审批时获取所有的用户或者用户组
     * @param array $params 请求参数
     * @return array
     */
    public function getShareUsers(array $params): array
    {
        if ($params['type'] == 'user') {
            $sql = 'select distinct bu.user_uuid, bu.user_name from bd_user bu ';
            $user = xphp_get_user_info();
            $userUuid = $user['userUuid'];
            $where = " where bu.user_uuid != '" . $userUuid . "' and lock_flag = 1 ";
            if (!xphp_three_powers()) {
                // 不是三权 那么不显示默认的三权用户
                $where .= " and bu.user_level not in (2,3,4,5) and bu.user_uuid != '" . $userUuid . "' ";
            } else {
                // 是三权 那么根据用户的level来显示对应的用户
                if ($user['userLevel'] == xphp_get_config('three_powers', 'THREE_POWERS_USER')['sysadmin']) {
                    // 系统管理员 只能查看level 为 2,4,5的用户
                    $where .= ' and bu.user_level in (2,4,5) ';
                } elseif ($user['userLevel'] == xphp_get_config('three_powers', 'THREE_POWERS_USER')['safeadmin']) {
                    // 安全管理员 可以查看 2,3,4,5的用户
                    $where .= ' and bu.user_level in (2,3,4,5) ';
                } else {
                    // 审计员和操作员只能查看自身的用户列表
                    $where .= ' and bu.user_level = ' . $user['userLevel'];
                }
            }
        } else {
            // 获取用户组
            $sql = 'select user_group_uuid,user_group_name from bd_user_group ';
            $where = ' where user_group_type = 2 and lock_flag = 1';
        }

        $data = $this->dbSelect($sql . $where);
        if (empty($data)) {
            return [];
        }
        return $data;
    }

    /**
     * 获取比对文件下载url
     * @param string $reportUuid 报告uuid
     * @return array
     */
    public function downLoadFileUrl(string $reportUuid)
    {
        $sql = 'select `timestamp`,doc_compare_result_json
                    from sr_surebackup_report
                    where report_uuid = ? limit 1';
        $data = $this->dbSelect($sql, [$reportUuid]);
        if (empty($data)) {
            return [
                'code' => 1,
                'msg' => xphp_get_lang('WEB_PLATFORM_INDUSTRY_REPORT_NOT_EXIST')
            ];
        }
        // 1，拿到所有的文件比对目录列表
        // 2.发送cmd打包命令压缩到web_ng下的data缓存目录下
        // 3.返回下载链接
        // 4.参照细粒度任务详情下载文件方式处理
        $doc = json_decode($data[0]['doc_compare_result_json'], true);

        $downUrl = 'industry/' . $reportUuid . '-' . strtotime($data[0]['timestamp']) . '-' . time() . '.tar.gz';
        $fullTarPath = DATA_PATH . $downUrl;
        // 构建 tar 命令
        $cmd = 'tar -czv -f ' . escapeshellarg($fullTarPath) . ' ';
        $check = false;
        foreach ($doc as $item) {
            if (!empty($item['result_save_place'])) {
                $absPath = $item['result_save_place']; // 比如：/backup_storage/xxx/yyy/zzz
                $parentDir = dirname($absPath);        // 上级目录
                $folderName = basename($absPath);      // 最后一级目录名

                // 添加 -C 参数结构到命令中
                $cmd .= '-C ' . escapeshellarg($parentDir) . ' ' . escapeshellarg($folderName) . ' ';
                $check = true;
            }
        }
        $cmd .= ';';

        if (empty($check)) {
            return [
                'code' => 1,
                'msg' => xphp_get_lang('UI_REPORT_NO_INFO')
            ];
        }
        $opName = 'NODE_SYS_OP_DO_CMD';
        $msg = array('command' => $cmd);

        $mbResult = $this->service()->mbNodeMsgs($opName, $msg, true, true);
        if ($mbResult['result']) {
            return [
                'code' => 0,
                'msg' => '/web_ng/api/data/' . $downUrl
            ];
        } else {
            return $this->muOpResult(
                $mbResult['result'],
                xphp_get_lang('UI_PLATFORM_INDUSTRY_TEMPLATE_ITEM_FILES_COMPARE'),
                $mbResult['msg'],
                '',
                $mbResult['errorCode']
            );
        }
    }

    /**
     * 下载报告校验并返回下载url
     * @param string|array $reportUuid 报告uuid
     * @return array
     */
    public function downLoadUrl($reportUuid)
    {
        if (!is_array($reportUuid)) {
            $report = $this->dbSelect(
                "select `name` from industry_report where report_uuid = ?",
                [$reportUuid]
            );
        } else {
            // 批量下载
            $params = "'" . implode("','", $reportUuid) . "'";
            $report = $this->dbSelect(
                "select `name` from industry_report where report_uuid in ({$params})",
                []
            );
        }

        if (empty($report)) {
            return [
                'code' => 1,
                'msg' => xphp_get_lang('WEB_PLATFORM_INDUSTRY_REPORT_NOT_EXIST')
            ];
        }
        xphp_set_cache('download_report_url', $reportUuid);
        $token = token();
        // 输出下载的URL
        xphp_set_cache($token, true, true);
        $url = '/api/v1/industry/report/download?token=' . $token . '&x-api-version=1.0-rev0';

        $item = [
            implode(',', array_column($report, 'name')),
            (count($report) > 1 ? xphp_get_lang('WEB_PLATFORM_INDUSTRY_REPORT_BATCH') : '')
            . xphp_get_lang('UI_VERIFY_REPORT_DOWNLOAD')
        ];

        $this->systemLog('PT_INDUSTRY_REPORT_OPERATE', $item);

        return [
            'code' => 0,
            'msg' => [
                'url' => $url
            ]
        ];
    }

    /**
     * 下载报告
     * @param array $param 参数
     * @return array|stream
     */
    public function downLoad(array $param)
    {
        $reportUuid = xphp_get_cache('download_report_url');
        if (empty($param['token']) || empty(xphp_get_cache($param['token'], true)) || empty($reportUuid)) {
            return [
                'code' => 1,
                'msg' => xphp_get_lang('WEB_SETTINGS_UPDATE_PARAMS_ERROR')
            ];
        }
        xphp_set_cache($param['token'], false, true);
        xphp_set_cache('download_report_url', false);

        if (is_array($reportUuid)) {
            // 批量下载
            $report = [];
            foreach ($reportUuid as $items) {
                $data = $this->getReportHtml($items);
                if ($data['code'] != 0) {
                    return $data;
                }
                $data['msg']['reportUuid'] = $items;
                $report[] = $data['msg'];
            }
            $files = [];
            $baseName = DATA_PATH . 'industry/pdf/';
            foreach ($report as $items1) {
                $downname = $baseName . $items1['name'] . TIMESTAMP . md5($items1['reportUuid']) . '.pdf';
                $file = (new ReportPdf())->makePdf(
                    $items1['html'],
                    1,
                    md5($items1['reportUuid']),
                    $downname,
                    $items1['water'],
                    1,
                    $items1['name']
                );
                if ($file['code'] == 0) {
                    $files[] = $file['msg'];
                }
            }
            return $this->zipBatch($files, 'batch_download_' . TIMESTAMP . '.zip');
        }
        $data = $this->getReportHtml($reportUuid);
        if ($data['code'] != 0) {
            return $data;
        }

        $msg = $data['msg'];
        $downname = $msg['name'] . '-' . TIMESTAMP . '.pdf';
        return (new ReportPdf())->makePdf($msg['html'], 0, md5($reportUuid), $downname, $msg['water'], 1, $msg['name']);
    }

    /**
     * 压缩多个文件到一个压缩包并下载
     * @param array  $files   件路径
     * @param string $zipName 下载名称
     * @param bool   $del     是否删除压缩包的里面的文件
     * @return stream
     */
    private function zipBatch(array $files, string $zipName, bool $del = true)
    {

        if (empty($files)) {
            die('Could not empty files');
        }
        $baseName = DATA_PATH . 'industry/pdf/';
        $zipNames = $baseName . $zipName;
        $zip = new \ZipArchive();
        if ($zip->open($zipNames, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            die('Could not open archive');
        }

        foreach ($files as $file) {
            $zip->addFile($file, basename($file));
        }

        $zip->close();

        header('Content-Type: application/zip');
        header('Content-disposition: attachment; filename=' . $zipName);
        header('Content-Length: ' . filesize($zipNames));
        readfile($zipNames);
        unlink($zipNames);  // 删除临时ZIP文件
        if ($del) {
            // 删除
            foreach ($files as $item) {
                unlink($item);  // 删除临时文件
            }
        }
    }

    /**
     * 发送报告到邮箱
     * @param string $reportUuid 报告uuid
     * @param array  $email      接受邮箱
     * @return bool
     */
    public function sendEmail(string $reportUuid, $email = [])
    {

        // 生成临时的pdf文件，
        $name = DATA_PATH . 'industry/pdf/' . md5($reportUuid) . '.pdf';
        $data = $this->getReportHtml($reportUuid);
        if ($data['code'] != 0) {
            return $data;
        }

        $msg = $data['msg'];
        $return = (new ReportPdf())->makePdf($msg['html'], 1, md5($reportUuid), $name, $msg['water'], 1, $msg['name']);
        if ($return['code'] != 0) {
            return $return;
        }
        if (empty($email)) {
            // 获取当前用户的邮箱
            $user = xphp_get_user_info();
            if (empty($user['email'])) {
                $info = $this->dbSelect('select email from bd_user where user_uuid = ?', [$user['userUuid']]);
                $user['email'] = $info[0]['email'];
                if (empty($user['email'])) {
                    return [
                        'code' => 1,
                        'msg' => xphp_get_lang('WEB_PLATFORM_INDUSTRY_REPORT_EMAIL_EMPTY')
                    ];
                }
            }
            $email = [$user['email']];
        }

        $emails = [
            'title' => xphp_get_lang('UI_PLATFORM_VERIFY_REPORT'),
            'email' => $email,
            'info' => xphp_get_lang('UI_PLATFORM_VERIFY_REPORT'),
            'attachment' => [$name],
        ];
        $return = (new Notice())->sendEmail($emails);

        // 写日志
        $report = $this->dbSelect("select `name` from industry_report where report_uuid = ?", [$reportUuid]);
        $item = [$report[0]['name'], xphp_get_lang('WEB_PLATFORM_INDUSTRY_REPORT_EMAIL_SEND') . implode(',', $email)];
        $this->systemLog('PT_INDUSTRY_REPORT_OPERATE', $item);

        // 删除报告的临时文件
        if ($return['code'] == 0) {
            unlink($name);
        }
        return $return;
    }

    /**
     * 获取转换后的html
     * @param string $reportUuid 报告uuid
     * @return string
     */
    private function getReportHtml(string $reportUuid)
    {
        $report = $this->dbSelect(
            "select ir.report_water,ir.`name`,bu.user_name
                from industry_report ir left join bd_user bu on ir.user_uuid = bu.user_uuid
                where ir.report_uuid = ? ",
            [$reportUuid]
        );
        if (empty($report)) {
            return [
                'code' => 1,
                'msg' => xphp_get_lang('WEB_PLATFORM_INDUSTRY_REPORT_NOT_EXIST')
            ];
        }

        // 获取报告的所有元素以及对应的位置
        $html = $this->getPrePosition($reportUuid);

        $reportwater = json_decode($report[0]['report_water'], true);
        if ($reportwater['flag'] == true) {
            $waters =  date(xphp_get_config('special', 'dateformat'));
            if ($reportwater['type'] == 1) {
                $waters = $reportwater['value'];
            } elseif ($reportwater['type'] == 2) {
                $waters .= $reportwater['value'];
            } elseif ($reportwater['type'] == 3) {
                // 还要加上报告的创建者的名称
                $waters .= $reportwater['value'] . $report[0]['user_name'];
            }
        }

        return [
            'code' => 0,
            'msg' => [
                'html' => $html,
                'water' => $waters ?? '',
                'name' => $report[0]['name']
            ]
        ];
    }

    /**
     * 生成报告之后的后续处理
     * 更改状态和审批流信息
     * @param string $reportUuid 报告uuid
     * @param unknow $isPlan     是否方案
     * @return bool
     */
    private function afterReport(string $reportUuid, $isPlan = '')
    {

        $table = 'industry_report';
        $uuid = 'report_uuid';
        $desKey = 'PT_INDUSTRY_REPORT_OPERATE';
        $lang1 = 'UI_PLATFORM_VERIFY_REPORT';
        $lang2 = 'WEB_PLATFORM_INDUSTRY_REPORT_APPROVAL';
        $lang3 = 'WEB_PLATFORM_INDUSTRY_REPORT_APPROVAL_NEED';
        if (!empty($isPlan)) {
            // 如果传了这个参数，那么表示的方案的操作
            $table = 'industry_plan';
            $uuid = 'plan_uuid';
            $desKey = 'PT_INDUSTRY_PLAN_OPERATE';
            $lang1 = 'UI_PUBLIC_PLAN';
            $lang2 = 'WEB_PLATFORM_INDUSTRY_PLAN_APPROVAL';
            $lang3 = 'WEB_PLATFORM_INDUSTRY_PLAN_APPROVAL_NEED';
        }

        // 获取报告的一些信息
        $sql = "select a.approval_uuid,a.user_uuid,bu.user_name,a.name,a.screen_result,
                    srr.extension_info,srr.doc_compare_result_json
                from {$table} a
                    left join sr_surebackup_report srr on srr.report_uuid = a.report_uuid
                    left join bd_user bu on bu.user_uuid = a.user_uuid 
                where a.{$uuid} = ?";
        if (!empty($isPlan)) {
            $sql = "select a.approval_uuid,a.user_uuid,bu.user_name,a.name
                from {$table} a
                    left join bd_user bu on bu.user_uuid = a.user_uuid 
                where a.{$uuid} = ?";
        }

        $report = $this->dbSelect($sql, [$reportUuid]);
        if (empty($report)) {
            return false;
        }
        // 获取审批流信息
        $sql = "select content from approval_list where approval_uuid = ?";
        $approval = $this->dbSelect($sql, [$report[0]['approval_uuid']]);
        if (empty($approval)) {
            return false;
        }
        $content = json_decode($approval[0]['content'], true);
        $nowUserUuid = $content[0]['approval_user_uuid']; // 当前该审批的用户uuid
        $nowUserType = $content[0]['type']; // 当前该审批的用户类型
        foreach ($content as $key => $item) {
            //抄送人员插入industry_user
            $this->insertCopySendUsers(
                $item['users']['user'],
                $item['users']['user_group'],
                $report[0]['approval_uuid'],
                $key,
                $reportUuid,
                1
            );
        }
        // 审批流
        $approvalList = $this->makeApproval(
            $report[0]['user_uuid'],
            $report[0]['user_name'],
            $report[0]['approval_uuid'],
            $isPlan
        );

        // 审核流关联的用户uuid集合，英文逗号隔开
        $userUuid = implode(',', array_column($approvalList, 'user_uuid'));

        if (empty($isPlan)) {
            // 计算此时的报告的最终状态
            $screenResult = $report[0]['screen_result'] != 2; // 截屏比对结果
            $openResult = $filesResult = true;
            if (!empty($report[0]['extension_info']) || !empty($report[0]['doc_compare_result_json'])) {
                if (!empty($report[0]['extension_info'])) {
                    $extensionInfo = json_decode($report[0]['extension_info'], true);
                    if (empty($extensionInfo['boot_verify_result'])) {
                        $openResult = false;
                    } else {
                        $openResult = intval($extensionInfo['boot_verify_result']) == 5;
                    }
                }
                if (!empty($report[0]['doc_compare_result_json'])) {
                    $filesInfo = json_decode($report[0]['doc_compare_result_json'], true);
                    if (!empty($filesInfo)) {
                        // 没有异常的表示全部是成功
                        $filesResult = intval($filesInfo['abnormal_file_count']) == 0;
                    }
                }
            }
            $result = v1_parse_bool_to_flag($screenResult && $openResult && $filesResult);

            $sql = "update {$table} set
                status=?,approval_user_uuid=?,approval_list=?,now_user_uuid=?,now_user_type=?,report_time=?,`result`=?
                        where {$uuid} = ?";
            $reportStatus = xphp_get_config('industry', 'REPORT_STATUS');
            $params = [
                $reportStatus['PENDING'],
                $userUuid,
                json_encode($approvalList),
                $nowUserUuid,
                $nowUserType,
                date(xphp_get_config('special', 'dateformat')),
                $result,
                $reportUuid
            ];
            $return = $this->dbExec($sql, $params);
        } else {
            $sql = "update {$table} set
                status=?,approval_user_uuid=?,approval_list=?,now_user_uuid=?,now_user_type=?,approval_time=?
                        where {$uuid} = ?";
            $reportStatus = xphp_get_config('industry', 'PLAN_STATUS');
            $params = [
                $reportStatus['PENDING'],
                $userUuid,
                json_encode($approvalList),
                $nowUserUuid,
                $nowUserType,
                date(xphp_get_config('special', 'dateformat')),
                $reportUuid
            ];
            $return = $this->dbExec($sql, $params);
        }

        // 并且通知下一个人审批
        if ($nowUserType == 2) {
            // 是选择的用户组 那么需要查询出这个用户组的所有用户的邮箱地址
            $sqls = "select bu.email from bd_user bu,mt_user_user_group muug
                    where muug.user_group_uuid=? and bu.user_uuid = muug.user_uuid group by bu.user_uuid";
            $user = $this->dbSelect($sqls, [$nowUserUuid]);
        } else {
            $user = $this->dbSelect("select email from bd_user where user_uuid = ?", [$nowUserUuid]);
        }

        if (!empty($user)) {
            $emails = [
                'title' => xphp_get_lang($lang1),
                'email' => array_column($user, 'email'),
                'info' => xphp_get_lang('WEB_PLATFORM_INDUSTRY_REPORT_LOGIN_SYSTEM') . '【' . $report[0]['name'] . '】'
                    . xphp_get_lang($lang2)
            ];
            (new Notice())->sendEmail($emails);
        }
        // 写日志
        $item = [$report[0]['name'], xphp_get_lang($lang3)];
        $this->systemLog($desKey, $item);

        return $return;
    }

    /**
     * 根据审批流uuid获取审批流的组合
     * @param string  $userUuid     报告创建人uuid
     * @param string  $userName     报告生成员
     * @param string  $approvalUuid 审批流uuid
     * @param bool    $isMake       报告是否生成
     * @param unknown $isPlan       是否是方案
     * @return array
     */
    public function makeApproval(
        string $userUuid,
        string $userName,
        string $approvalUuid,
        bool $isMake = true,
        $isPlan = ''
    ) {
        // 获取审批流信息
        $sql = "select content from approval_list where approval_uuid = ?";
        $approval = $this->dbSelect($sql, [$approvalUuid]);
        if (empty($approval)) {
            return [];
        }
        $content = json_decode($approval[0]['content'], true);
        $lang1 = 'UI_PLATFORM_INDUSTRY_REPORT_MAKE';
        $lang2 = 'WEB_PLATFORM_INDUSTRY_REPORT_ING';
        if (!empty($isPlan)) {
            $lang1 = 'WEB_PLATFORM_INDUSTRY_REPORT_START_APPROVAL';
            $lang2 = 'UI_GMP_REPORT_PENDING';
        }
        // 组装审批流的构造
        $approvalList = [
            [
                'user_uuid' => $userUuid,
                'user_name' => $userName,
                'status' => 1,
                'desc' => $isMake ? xphp_get_lang($lang1)
                    : xphp_get_lang($lang2),
                'remark' => $isMake ? xphp_get_lang('WEB_PLATFORM_INDUSTRY_REPORT_START_APPROVAL') : '',
                'type' => 1,
                'position' => xphp_get_lang('WEB_UTILS_USERTYPE_OPERATOR'),
                'users' => [
                    'user' => [],
                    'user_group' => [],
                    'notice' => false
                ],
                'share' => [
                    'users' => [],
                    'user_groups' => [],
                    'notice' => false
                ],
                'create_time' => $isMake ? date(xphp_get_config('special', 'dateformat')) : ''
            ]
        ];
        foreach ($content as $item) {
            $approvalList[] = [
                'user_uuid' => $item['approval_user_uuid'],
                'user_name' => $item['approval_user_name'],
                'status' => 0,
                'desc' => $item['description'],
                'remark' => '',
                'type' => $item['type'],
                'position' => $item['position'],
                'users' => $item['users'],
                'share' => [
                    'users' => [],
                    'user_groups' => [],
                    'notice' => false
                ],
                'create_time' => ''
            ];
        }
        // 最后ping上归档的
        $approvalList[] = [
            'user_uuid' => '',
            'user_name' => 'system',
            'status' => 0,
            'desc' => xphp_get_lang('WEB_PLATFORM_INDUSTRY_REPORT_STATUS_FILE'),
            'remark' => '',
            'type' => 1,
            'position' => xphp_get_lang('UI_PLATFORM_SYSTEM'),
            'users' => [
                'user' => [],
                'user_group' => [],
                'notice' => false
            ],
            'share' => [
                'users' => [],
                'user_groups' => [],
                'notice' => false
            ],
            'create_time' => ''
        ];
        return $approvalList;
    }

    /**
     * 根据传入的二维数组组装
     * 如果是详情的，那么需要计算 item_description 的高度加上 description下面的所有的高度才对
     * @param array $array   参数
     * @param int   $baseTop 基础高度
     * @param int   $type    表明是哪个类型 1是通用 2是验证
     * @return array
     */
    private function calcArray(array $array, $baseTop = 0, int $type = 2)
    {
        $return = [];
        foreach ($array as $key => $item) {
            if (in_array($key, ['item_textarea', 'item_field', 'item_datetime'])) {
                // 需要先处理多重的拆开
                $arr = $item['list'];
                foreach ($arr as $keys => $items) {
                    $position = explode(',', $items['position']);
                    if (($type == 1 && $position[0] > 100) || $type == 2) {
                        $marginTop = 75;
                    } else {
                        $marginTop = 0;
                    }
                    $return[] = [
                        'type' => $key,
                        'id' => $key . $keys,
                        'name' => $items['name'],
                        'en_name' => $items['en_name'] ?? '',
                        'value' => $items['value'],
                        'en_value' => $items['en_value'] ?? '',
                        'uuid' => $items['uuid'],
                        'top' => $baseTop + floatval($position[0]) - floatval($marginTop),
                        'left' => $position[1],
                    ];
                }
            } else {
                $position = explode(',', $item['position']);
                if (($type == 1 && $position[0] > 100) || $type == 2) {
                    $marginTop = 75;
                } else {
                    $marginTop = 0;
                }
                $return[] = [
                    'type' => $key,
                    'id' => $key,
                    'name' => xphp_get_lang('WEB_INDUSTRY_' . strtoupper($key)),
                    'en_name' => '',
                    'value' => $item['value'] ?? '', // 内容得根据任务的结果展示，暂时为空
                    'en_value' => $item['en_value'] ?? '',
                    'uuid' => $item['uuid'],
                    'top' => $baseTop + floatval($position[0]) - floatval($marginTop),
                    'left' => $position[1],
                ];
            }
        }
        return $return;
    }

    /**
     * 根据传入的二维数组根据某个key进行排序
     * @param array  $array   参数
     * @param string $baseKey 排序的key
     * @return array
     */
    private function calcTopPosition(array $array, $baseKey = 'top')
    {

        // 自定义排序函数，根据（'$baseKey'键）升序排序
        // 使用usort函数和自定义的排序函数对数组进行排序
        usort($array, function ($a, $b) use ($baseKey) {
            return $a[$baseKey] - $b[$baseKey];
        });

        return $array;
    }

    /**
     * 根据传递的图片绝对路径返回base64
     * @param string $url 图片路径
     * @return string
     */
    private function getBase64ByUrl(string $url): string
    {

        // 检查文件是否存在
        if (!file_exists($url)) {
            return 'Image not found.';
        }

        // 获取文件的 MIME 类型
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $url);
        finfo_close($finfo);

        // 读取文件内容并进行 Base64 编码
        $imageData = file_get_contents($url);
        return 'data:' . $mimeType . ';base64,' . base64_encode($imageData);
    }

    /**
     * 获取报告列表
     * @param array $params 参数
     * @return array
     */
    public function getReportList(array $params): array
    {
        $offset = $params['offset'];
        $limit = $params['limit'];
        $sort = $params['sort'];
        $order = $params['order'];
        $search = $params['search'];
        $userInfo = xphp_get_user_info();
        $user = $userInfo['userUuid'];
        $reportStatus = xphp_get_config('industry', 'REPORT_STATUS');
        $table = 'industry_report ir';
        // 这里需要查询出用户所在的用户组，可能是一个数组，需要用 in
        $userGroup = $this->dbSelect(
            "select user_group_uuid from mt_user_user_group where user_uuid = ?",
            [$user]
        );
        $userGroupArr = array_column($userGroup, 'user_group_uuid');
        $left = '';
        if ($params['type'] == 2) {
            // 分享或抄送
            $left = " LEFT JOIN industry_user iu ON iu.report_uuid = ir.report_uuid";
            $where = " where iu.user_uuid = ?";
            $filed = ', iu.content';
            $sqlParams = [$user];
        } else {
            // 如果不是全局观察者或者超级管理员，只能查看对应的
            if (v1_auth_need_check_look()) {
                $whereor = '';
                foreach ($userGroupArr as $item) {
                    $whereor .= " or ir.approval_user_uuid like  '%,{$item},%' ";
                }
                $where = " where ( ir.user_uuid = ? or ir.approval_user_uuid like ? {$whereor})";
                $sqlParams = [$user, '%,' . $user . ',%'];
            } else {
                $where = ' where 1 = 1 ';
                $sqlParams = [];
            }
        }

        if ($params['status'] !== null && $params['status'] >= 0) {
            if ($params['status'] == 99) {
                // 此时返回所有的不包括待生成的
                $where .= " and ir.status != {$reportStatus['GENERATED']}";
            } else {
                $where .= " and ir.status = {$params['status']}";
            }
        } else {
            if ($params['type'] == 1) {
                // 已归档
                $where .= " and ir.status = {$reportStatus['ARCHIVED']}";
            } elseif ($params['type'] == 3) {
                // 这是用于待审批
                $where .= " and ir.status not in ({$reportStatus['REVOKE']},{$reportStatus['GENERATED']})";
                $allUser = array_merge($userGroupArr, [$user]);
                $where .= " and ir.now_user_uuid in ('" . implode("','", $allUser) . "')";
            } elseif ($params['type'] == 4) {
                // 如果是待发起 要关联bd_task联查且任务是运行中
                $where .= " and ir.status = ({$reportStatus['GENERATED']})";
                $taskStatus = xphp_get_config('task', 'TASKSTATUS');
                $lefttask = " left join bd_task bt on bt.task_uuid = ir.task_uuid
                                    and bt.task_status = {$taskStatus['RUNNING']}";
                $where .= " and ir.user_uuid = '{$user}' and bt.task_uuid is not null ";
            } elseif ($params['type'] == 2) {
                // 分享或抄送
            } else {
                // 待审批
                $where .= " and ir.status not in ({$reportStatus['ARCHIVED']},{$reportStatus['GENERATED']})";
            }
        }

        if (!empty($params['report_agent'])) {
            $left .= ' left join bd_agent ba on ba.agent_uuid = ir.agent_uuid';
        }

        $sql = "select ir.report_uuid, ir.name, ir.report_time, ir.task_name, ir.status, ir.timepoint_uuid,
               ir.now_user_depth, ir.module_type,ir.create_time, ir.approval_user_uuid, ir.approval_list,
               ir.approval_uuid, ir.now_user_uuid, ir.user_uuid, ir.now_user_type, ir.task_uuid, ssr.timestamp,
               bu.user_name, bu2.user_name as now_user_name, bug.user_group_name {$filed} from {$table} 
        inner join sr_surebackup_report ssr on ir.report_uuid = ssr.report_uuid    
        left join bd_user bu on bu.user_uuid = ir.user_uuid
        left join bd_user bu2 on bu2.user_uuid = ir.now_user_uuid
        left join bd_user_group bug on bug.user_group_uuid = ir.now_user_uuid
        {$left}{$lefttask}";

        $sqlCount = "select count(*) as total from {$table} {$left} {$lefttask}";

        if (!empty($search)) {
            $where .= ' and ir.name like ?';
            $sqlParams = array_merge($sqlParams, ["%{$search}%"]);
        }
        if (!empty($params['report_title'])) {
            $where .= ' and ir.name like ?';
            $sqlParams = array_merge($sqlParams, ["%{$params['report_title']}%"]);
        }
        if (!empty($params['report_task'])) {
            $where .= ' and ir.task_name like ?';
            $sqlParams = array_merge($sqlParams, ["%{$params['report_task']}%"]);
        }
        if (!empty($params['report_template'])) {
            $where .= ' and ir.temp_uuid = ?';
            $sqlParams = array_merge($sqlParams, [$params['report_template']]);
        }
        if (!empty($params['report_agent'])) {
            $where .= ' and ( ba.hostname like ? or ba.ip like ?)';
            $sqlParams = array_merge($sqlParams, ["%{$params['report_agent']}%", "%{$params['report_agent']}%"]);
        }

        $sql .= $where . ' group by ir.report_uuid ';
        $sqlCount .= $where;

        $count = $this->dbSelect($sqlCount, $sqlParams);

        if (!empty($sort) && !empty($order)) {
            $sortArr = [
                'user_name' => 'bu.user_name',
                'create_time' => 'ir.create_time',
                'status' => 'ir.status',
                'report_time' => 'ir.report_time'
            ];
            $sort = $sortArr[$sort] ?? 'ir.id';
            $sql .= " order by {$sort} {$order}";
        } else {
            //默认按照时间倒序进行排序
            $sql .= " order by ir.create_time desc";
        }

        if (!empty($limit)) {
            $sql .= " limit {$offset}, {$limit}";
        }

        $data = $this->dbSelect($sql, $sqlParams);

        $jobInfo = new JobInfo();
        $records = [];
        $nullSpace = xphp_get_config('app', 'NULL_SPACE');
        foreach ($data as $d) {
            $records[] = array(
                'num' => ++$offset,
                'uuid' => $d['report_uuid'],
                'approval_uuid' => $d['approval_uuid'],
                'name' => $d['name'],
                'report_time' => $d['report_time'],
                'job_uuid' => $d['task_uuid'],
                'job_name' => $d['task_name'],
                'status' => intval($d['status']),
                'status_value' => $this->getApprovalStatusDes($d['status'], $d['now_user_name']),
                'now_user_uuid' => $d['now_user_uuid'] ?? '--',
                'now_user_depth' => $d['now_user_depth'],
                'timepoint' => $d['timestamp'] ?? $nullSpace,
                //如果当前用户不等于now_user_uuid，就看now_user_uuid是否在当前用户所在的用户组arr里面
                'approval_flag' => $user == $d['now_user_uuid'] || in_array($d['now_user_uuid'], $userGroupArr),
                'user_flag' => $user == $d['user_uuid'],
                'master_flag' => $this->getMasterFlag(), //是否是管理员
                'module' => $jobInfo->getModuleName($d),
                'user_name' => $d['user_name'],
                'user_uuid' => $d['user_uuid'],
                'create_time' => $d['create_time'],
                'now_user_name' => $d['now_user_name'] ?? $d['user_group_name'],
                'approval_list' => $d['approval_list'],
                'content' => $d['content'] ?? '',
            );
        }

        return [
            'rows' => $records,
            'total' => $count[0]['total']
        ];
    }

    /**
     * 获取管理员flag
     * @return bool
     */
    public function getMasterFlag()
    {
        return xphp_get_user_info()['userLevel'] == 1;
    }

    /**
     * 更改审批人员
     * @param string $params 当前待审批用户名
     * @return string
     */
    public function changeApproveUser($params)
    {
        $this->checkIsGlobal();
        $changeUuid = $params['user_uuid']; //更改的审批用户uuid
        $reportUuid = $params['report_uuid'];

        $table = 'industry_report';
        $uuid = 'report_uuid';
        $desKey = 'PT_INDUSTRY_REPORT_CHANGE_APPROVAL';
        if (!empty($params['is_plan'])) {
            // 如果传了这个参数，那么表示的方案的操作
            $table = 'industry_plan';
            $uuid = 'plan_uuid';
            $desKey = 'PT_INDUSTRY_PLAN_CHANGE_APPROVAL';
        }

        // 查询改动报告的相关信息
        $sql = "select approval_user_uuid, now_user_depth, approval_list,`name`
                from {$table} where {$uuid} = '{$reportUuid}'";
        $reportInfo = $this->dbSelect($sql);
        $info = $reportInfo[0];
        $allApprovalUser = explode(',', $info['approval_user_uuid']);
        $allApprovalUser[$info['now_user_depth'] + 1] = $changeUuid; // 当前的审批用户uuid变为更改的审批人uuid
        $allApprovalUser = implode(',', $allApprovalUser);
        $list = json_decode($info['approval_list'], true);

        // 处理描述信息
        $des = xphp_get_lang('WEB_PLATFORM_INDUSTRY_REPORT_LOG_CHANGE_STATUS');
        $oldUserName = $list[$info['now_user_depth'] + 1]['user_name'];
        $userSql = "select user_name from bd_user where user_uuid = '{$changeUuid}'";
        $newUserName = $this->dbSelect($userSql)[0]['user_name'];
        $des = sprintf($des, $oldUserName, $newUserName);

        // 组合本次更改的item
        $changeItem = ['time' => date(xphp_get_config('special', 'dateformat')), 'des' => $des];
        // 先判断这个change是不是数组
        if (is_array($list[$info['now_user_depth'] + 1]['change'])) {
            array_push($list[$info['now_user_depth'] + 1]['change'], $changeItem);
        } else {
            $list[$info['now_user_depth'] + 1]['change'] = [];
            array_push($list[$info['now_user_depth'] + 1]['change'], $changeItem);
        }
        $list[$info['now_user_depth'] + 1]['user_uuid'] = $changeUuid;
        $list[$info['now_user_depth'] + 1]['user_name'] = $newUserName;
        $list[$info['now_user_depth'] + 1]['type'] = 1;
        $list = json_encode($list);
        // 更新表的相关字段
        $sql = "update {$table} set approval_user_uuid = '{$allApprovalUser}', now_user_uuid = '{$changeUuid}',
                now_user_type = 1, approval_list = ? where {$uuid} = '{$reportUuid}'";
        $sqlParams = [$list];
        $result = $this->dbExec($sql, $sqlParams);

        $item = [xphp_get_user_info()['userName'], $info['name'], $newUserName];
        $this->systemLog($desKey, $item);

        return $result;
    }

    /**
     * 获取报告列表状态描述
     * @param int    $status      状态参数
     * @param string $nowUserName 当前待审批用户名
     * @return string
     */
    public function getApprovalStatusDes($status, $nowUserName = '')
    {

        $allStatus = xphp_get_config('industry', 'REPORT_STATUS');
        $status = intval($status);
        switch ($status) {
            case $allStatus['ARCHIVED']:
                $statusDes = xphp_get_lang('WEB_PLATFORM_INDUSTRY_REPORT_STATUS_FILES');
                break;
            case $allStatus['APPROVALING']:
                $statusDes = xphp_get_lang('WEB_PLATFORM_INDUSTRY_REPORT_STATUS_ING');
                break;
            case $allStatus['REJECTED']:
                $statusDes = xphp_get_lang('WEB_PLATFORM_INDUSTRY_REPORT_STATUS_REJECTED');
                break;
            case $allStatus['REVOKE']:
                $statusDes = xphp_get_lang('WEB_PLATFORM_INDUSTRY_REPORT_BACK');
                break;
            case $allStatus['GENERATED']:
                $statusDes = xphp_get_lang('WEB_PLATFORM_INDUSTRY_REPORT_ING');
                break;
            default:
                $statusDes = xphp_get_lang('WEB_PLATFORM_INDUSTRY_REPORT_INGS') . "{$nowUserName}"
                    . xphp_get_lang('WEB_INDUSTRY_APPROVE');
                break;
        }

        return $statusDes;
    }

    /**
     * 审批报告
     * @param array $params 参数
     * @return bool
     */
    public function approveReport(array $params)
    {
        $this->checkIsGlobal();
        $reportUuid = $params['report_uuid'];
        $approveResult = intval($params['approve_result']);
        $advice = v1_escape_wildcard($params['approve_advice']);
        $userUuids = $params['user'] == '' ? [] : explode(',', $params['user']);
        $userGroupUuids = $params['user_group'] == [] ? '' : explode(',', $params['user_group']);
        $time = date(xphp_get_config('special', 'dateformat'));

        $table = 'industry_report';
        $uuid = 'report_uuid';
        $lang1 = 'UI_PUBLIC_REPORT';
        $lang2 = 'WEB_PLATFORM_INDUSTRY_REPORT_COPY';
        $statusKey = 'REPORT_STATUS';
        if (!empty($params['is_plan'])) {
            // 如果传了这个参数，那么表示的方案的操作
            $table = 'industry_plan';
            $uuid = 'plan_uuid';
            $lang1 = 'UI_PUBLIC_PLAN';
            $lang2 = 'WEB_PLATFORM_INDUSTRY_PLAN_COPY';
            $statusKey = 'PLAN_STATUS';
        }

        //查询当前报告的审批记录
        $sql = "select `name`,approval_user_uuid, now_user_uuid,now_user_type,
                        now_user_depth,approval_list,approval_uuid 
                    from {$table} where {$uuid} = ?";
        $sqlParams = [$reportUuid];
        $data = $this->dbSelect($sql, $sqlParams);
        //当前审批过程记录以及当前审批人
        $approveList = json_decode($data[0]['approval_list'], true);
        $nowUserUuid = $data[0]['now_user_uuid'];
        $userDepth = $data[0]['now_user_depth'];
        $userList = $approveList[$userDepth + 1]['users']; // 当前审批流程的抄送人
        $allStatus = xphp_get_config('industry', $statusKey);
        $nowStatus = $allStatus['APPROVALING']; //全局状态默认现在是审批中
        $description = xphp_get_lang('WEB_PLATFORM_INDUSTRY_REPORT_STATUS_NORMAL');
        if ($approveResult == 2) {
            // 如果驳回，那么全局的状态也是驳回
            $nowStatus = $allStatus['REJECTED'];
            $description = xphp_get_lang('WEB_PLATFORM_INDUSTRY_REPORT_STATUS_REJECTED');
        }

        //更新字段：now_user_uuid, approval_list, status更新为2审批中或是已驳回，或者是最后一个审批人审批完成，就是已归档,还需要插入在审批时选择的抄送人,以及当前审批人的职位
        //更新抄送人并且插入数据库前要先删除之前的当前层级抄送industry_user的记录
        $deleteSql = "delete from industry_user
                        where report_uuid = '{$reportUuid}' and depth = '{$userDepth}' and user_type = 1";
        $this->dbExec($deleteSql);

        $nextUserUuid = '';
        $nextUserType = 1;
        if ($userDepth + 3 == count($approveList)) {
            // 这是最后一个用户
            if ($approveResult != 2) {
                $nowStatus = $allStatus['ARCHIVED'];
            }
        } else {
            // 还有下一个用户
            $userDepth++;
            $nextUserUuid = $approveList[$userDepth + 1]['user_uuid'];
            $nextUserType = $approveList[$userDepth + 1]['type'];
        }

        //循环操作审批记录数组
        $user = xphp_get_user_info();
        //获取当前审批人的职位
        $userSql = "select position from bd_user where user_uuid = '{$user['userUuid']}'";
        $position = $this->dbSelect($userSql)[0]['position'];

        // 不是最后一人，当前审批的层级的值需要修改
        $approveList[$data[0]['now_user_depth'] + 1]['remark'] = $advice;
        $approveList[$data[0]['now_user_depth'] + 1]['status'] = $approveResult;
        $approveList[$data[0]['now_user_depth'] + 1]['create_time'] = $time;
        $approveList[$data[0]['now_user_depth'] + 1]['desc'] = $description;
        $approveList[$data[0]['now_user_depth'] + 1]['user_uuid'] = $user['userUuid'];
        $approveList[$data[0]['now_user_depth'] + 1]['user_name'] = $user['userName'];
        $approveList[$data[0]['now_user_depth'] + 1]['users']['user'] =
            array_unique(array_merge($userUuids, $approveList[$data[0]['now_user_depth'] + 1]['users']['user']));
        $approveList[$data[0]['now_user_depth'] + 1]['users']['user_group'] =
            array_unique(
                array_merge($userGroupUuids, $approveList[$data[0]['now_user_depth'] + 1]['users']['user_group'])
            );
        $approveList[$data[0]['now_user_depth'] + 1]['position'] = $position ?? '';
        if ($nextUserUuid == '' && $approveResult == 1) {
            // 最后一人，已归档时的当前层级修改
            $approveList[$data[0]['now_user_depth'] + 2]['status'] = 1;
            $approveList[$data[0]['now_user_depth'] + 2]['desc'] =
                xphp_get_lang('WEB_PLATFORM_INDUSTRY_REPORT_STATUS_FILES');
            $approveList[$data[0]['now_user_depth'] + 2]['create_time'] = $time;
        }

        // 抄送用户更新
        if (!empty($approveList[$data[0]['now_user_depth'] + 1]['users']['user'])) {
            foreach ($approveList[$data[0]['now_user_depth'] + 1]['users']['user'] as $key => $users) {
                $uuids = xphp_uuid();
                $sql = "insert into industry_user 
                (uuid, user_type, user_uuid, report_uuid, approval_uuid, depth, create_time)
                values (?,?,?,?,?,?,?)";
                $sqlParams = [
                    $uuids, 1, $users, $reportUuid, $data[0]['approval_uuid'], $data[0]['now_user_depth'],
                    date(xphp_get_config('special', 'dateformat'))
                ];
                $this->dbExec($sql, $sqlParams);
            }
        }
        if (!empty($approveList[$data[0]['now_user_depth'] + 1]['users']['user_group'])) {
            foreach ($approveList[$data[0]['now_user_depth'] + 1]['users']['user_group'] as $key => $groups) {
                // 找出每个user组的所有user
                $userSql = "select user_uuid from mt_user_user_group where user_group_uuid = ?";
                $userSqlParams = [$groups];
                $users = $this->dbSelect($userSql, $userSqlParams);
                $users = array_column($users, 'user_uuid');
                if (empty($users)) {
                    continue;
                }

                foreach ($users as $key => $u) {
                    $uuids = xphp_uuid();
                    $sql = "insert into industry_user 
                    (uuid, user_type, user_uuid, report_uuid, approval_uuid, depth, create_time)
                    values (?,?,?,?,?,?,?)";
                    $sqlParams = [$uuids, 1, $u, $reportUuid, $data[0]['approval_uuid'], $data[0]['now_user_depth'],
                        date(xphp_get_config('special', 'dateformat'))];
                    $this->dbExec($sql, $sqlParams);
                }
            }
        }
        $updateSql = "update {$table} set
                           now_user_uuid = ?,now_user_type = ?,now_user_depth = ?, approval_list = ?, status = ?
                        where {$uuid} = ?";
        $updateSqlParams = [$nextUserUuid, $nextUserType, $userDepth,
            json_encode($approveList), $nowStatus, $reportUuid];

        $result = $this->dbExec($updateSql, $updateSqlParams);

        // 邮件通知下一个人,如果没得下一个人就通知报告创建人，并且每步操作都要记录日志
        $this->afterApproval($reportUuid, $nowStatus, $nextUserUuid, $nextUserType, $params['is_plan']);

        // 还需要判断是否存在抄送，存在需要把审批结果抄送过去 还要考虑本次审批临时加的抄送人，这时候还需要向industry_user表插入记录和更新当前的报告的审批流里面的记录
        if (
            !empty($approveList[$data[0]['now_user_depth'] + 1]['users']['user']) ||
            !empty($approveList[$data[0]['now_user_depth'] + 1]['users']['user_group'])
        ) {
            $this->sendResultToUser(
                $userList['user'],
                $userList['user_group'],
                xphp_get_lang($lang1) . '【' . $data[0]['name'] . '】,' .
                xphp_get_lang('WEB_PLATFORM_INDUSTRY_REPORT_USERS') . $user['userName'] . $description,
                xphp_get_lang($lang2)
            );
        }
        return $result;
    }

    /**
     * 抄送结果发送
     * @param array  $userList      抄送人
     * @param array  $userGroupList 抄送用户组
     * @param string $des           描述信息
     * @param string $title         邮件标题
     * @return void
     */
    public function sendResultToUser($userList, $userGroupList, $des, $title)
    {
        $allUserList = $userList;

        foreach ($userGroupList as $key => $group) {
            $sql = "select user_uuid from mt_user_user_group where user_group_uuid = '{$group}'";
            $user = $this->dbSelect($sql);
            $user = array_column($user, 'user_uuid');
            $allUserList = array_merge($user, $userList);
        }
        $allUserList = array_unique($allUserList);
        $allUserList = "('" . implode("','", $allUserList) . "')";
        $emailSql = "select email from bd_user where user_uuid in {$allUserList}";
        $data = $this->dbSelect($emailSql);

        $emails = [
            'title' => $title,
            'email' => array_unique(array_column($data, 'email')),
            'info' => $des
        ];
        (new Notice())->sendEmail($emails);
    }

    /**
     * 更改审批流
     * @param array $params 参数
     * @return bool
     */
    public function changeApproval($params)
    {
        $this->checkIsGlobal();
        $reportUuid = $params['report_uuid'];
        $newApprovalUuid = $params['new_approval_uuid'];

        $table = 'industry_report';
        $uuid = 'report_uuid';
        $desKey = 'PT_INDUSTRY_REPORT_APPROVAL';
        if (!empty($params['is_plan'])) {
            // 如果传了这个参数，那么表示的方案的操作
            $table = 'industry_plan';
            $uuid = 'plan_uuid';
            $desKey = 'PT_INDUSTRY_PLAN_APPROVAL';
        }

        $sql = "update {$table} set approval_uuid = ? where {$uuid} = ?";
        $sqlParams = [$newApprovalUuid, $reportUuid];
        $this->dbExec($sql, $sqlParams);

        // 更改先清除原来报告的抄送分享记录
        $deleteSql = "delete from industry_user where report_uuid = '{$reportUuid}'";
        $this->dbExec($deleteSql);
        $result = $this->afterReport($reportUuid, $params['is_plan']);

        // 写日志
        $approval = $this->dbSelect("select `name` from approval_list where approval_uuid = ?", [$newApprovalUuid]);
        $report = $this->dbSelect("select `name` from {$table} where {$uuid} = ?", [$reportUuid]);
        $item = [$report[0]['name'],
            xphp_get_lang('WEB_PLATFORM_INDUSTRY_REPORT_LOG_MODIFY_APPROVAL') . $approval[0]['name']];
        $this->systemLog($desKey, $item);

        return $result;
    }

    /**
     * 撤销审批流
     * @param array $params 参数
     * @return bool
     */
    public function cancelApproval($params)
    {
        $this->checkIsGlobal();
        $reportUuid = $params['report_uuid'];
        $table = 'industry_report';
        $uuid = 'report_uuid';
        $desKey = 'PT_INDUSTRY_REPORT_APPROVAL';
        $statusKey = 'REPORT_STATUS';
        if (!empty($params['is_plan'])) {
            // 如果传了这个参数，那么表示的方案的操作
            $table = 'industry_plan';
            $uuid = 'plan_uuid';
            $desKey = 'PT_INDUSTRY_PLAN_APPROVAL';
            $statusKey = 'PLAN_STATUS';
        }
        $allStatus = xphp_get_config('industry', $statusKey);
        $sql = "update {$table} set status = " . $allStatus['REVOKE'] . " where {$uuid} = ?";
        $sqlParams = [$reportUuid];

        $result = $this->dbExec($sql, $sqlParams);
        // 写日志
        $report = $this->dbSelect("select `name` from {$table} where {$uuid} = ?", [$reportUuid]);
        $item = [$report[0]['name'], xphp_get_lang('WEB_PLATFORM_INDUSTRY_REPORT_LOG_BACK_APPROVAL')];
        $this->systemLog($desKey, $item);
        return $result;
    }

    /**
     * 删除审批报告
     * @param array $params 参数
     * @return bool
     */
    public function delReport($params)
    {
        $this->checkIsGlobal();
        $uuids = $params['uuids'];
        $uuidList = "('" . implode("','", $uuids) . "')";
        $sql = "delete from industry_report where report_uuid in {$uuidList}";
        return $this->dbExec($sql);
    }

    /**
     * 报告审批操作后续操作 写日志，发邮件
     * @param string     $reportUuid   报告uuid
     * @param integer    $status       状态1归档（表示最后一个人审批）、2审批中（表示审批通过）、3已驳回（表示不通过）
     * @param string     $userUuid     用户uuid
     * @param int        $nextUserType 用户类型 1用户2用户组
     * @param string|int $isPlan       是否是方案
     * @return boolean
     */
    private function afterApproval(
        string $reportUuid,
        int $status,
        string $userUuid = '',
        int $nextUserType = 1,
        $isPlan = ''
    ) {

        $table = 'industry_report';
        $uuid = 'report_uuid';
        $desKey = 'WEB_PLATFORM_INDUSTRY_REPORT_LOG_FILE';
        $statusKey = 'REPORT_STATUS';
        $field = 'ir.email_notice,ir.user_uuid,bu.email,ir.`name`';
        $lang1 = 'UI_PLATFORM_VERIFY_REPORT';
        $lang2 = 'WEB_PLATFORM_INDUSTRY_REPORT_APPROVAL';
        $lang3 = 'WEB_PLATFORM_INDUSTRY_REPORT_YOUR_ADD';
        $desk = 'PT_INDUSTRY_REPORT_APPROVAL';
        if (!empty($isPlan)) {
            // 如果传了这个参数，那么表示的方案的操作
            $table = 'industry_plan';
            $uuid = 'plan_uuid';
            $desKey = 'WEB_PLATFORM_INDUSTRY_PLAN_LOG_FILE';
            $statusKey = 'PLAN_STATUS';
            $field = 'ir.user_uuid,bu.email,ir.`name`';
            $lang1 = 'UI_PUBLIC_PLAN';
            $lang2 = 'WEB_PLATFORM_INDUSTRY_PLAN_APPROVAL';
            $lang3 = 'WEB_PLATFORM_INDUSTRY_PLAN_YOUR_ADD';
            $desk = 'PT_INDUSTRY_PLAN_APPROVAL';
        }
        $sql = "select {$field}
                    from {$table} ir
                    left join bd_user bu on bu.user_uuid = ir.user_uuid
                    where ir.{$uuid} = ?";
        $report = $this->dbSelect($sql, [$reportUuid]);
        if (empty($report)) {
            return false;
        }
        $param = [];
        $allStatus = xphp_get_config('industry', $statusKey);
        if ($status == $allStatus['ARCHIVED']) {
            // 已归档 通知操作员和所有的报告设置的邮件接收人,发送报告到这些人的邮箱去
            $email = [$report[0]['email']] ?? [];
            if (empty($isPlan)) {
                $emailNotice = json_decode($report[0]['email_notice'], true);
                if ($emailNotice['flag']) {
                    // 开启了报告完成发送邮件
                    $email = array_filter(array_merge($email, explode("\n", $emailNotice['value'])));
                }
                // 发送报告生成邮件
                $this->sendEmail($reportUuid, $email);
            } else {
                // 发送方案归档邮件
                $emails = [
                    'title' => xphp_get_lang('UI_PUBLIC_PLAN'),
                    'email' => $email,
                    'info' => '【' . $report[0]['name'] . '】' .
                        xphp_get_lang('WEB_PLATFORM_INDUSTRY_REPORT_LOG_APPROVAL_SUCCESS'),
                ];
                (new Notice())->sendEmail($emails);
            }

            $param[] = [$report[0]['name'], xphp_get_lang('WEB_PLATFORM_INDUSTRY_REPORT_LOG_APPROVAL_SUCCESS')];
            $param[] = [$report[0]['name'], xphp_get_lang($desKey)];
        } elseif ($status == $allStatus['APPROVALING']) {
            // 审批通过 通知下一个人登录备份系统去审批
            if ($nextUserType == 2) {
                // 用户组
                $sqls = "select bu.email from bd_user bu,mt_user_user_group muug
                    where muug.user_group_uuid=? and bu.user_uuid = muug.user_uuid group by bu.user_uuid";
                $user = $this->dbSelect($sqls, [$userUuid]);
            } else {
                $user = $this->dbSelect("select email from bd_user where user_uuid = ?", [$userUuid]);
            }
            if (!empty($user)) {
                $emails = [
                    'title' => xphp_get_lang($lang1),
                    'email' => array_column($user, 'email'),
                    'info' => xphp_get_lang('WEB_PLATFORM_INDUSTRY_REPORT_LOGIN_SYSTEM') .
                        '【' . $report[0]['name'] . '】' .
                        xphp_get_lang($lang2)
                ];
                (new Notice())->sendEmail($emails);
            }
            $param[] = [$report[0]['name'], xphp_get_lang('WEB_PLATFORM_INDUSTRY_REPORT_LOG_APPROVAL_SUCCESS')];
        } elseif ($status == $allStatus['REJECTED']) {
            // 驳回 通知操作员去审批
            $email = [$report[0]['email']] ?? [];
            if (!empty($email)) {
                $emails = [
                    'title' => xphp_get_lang($lang1),
                    'email' => $email,
                    'info' => xphp_get_lang($lang3) . '【' . $report[0]['name'] . '】' .
                        xphp_get_lang('WEB_PLATFORM_INDUSTRY_REPORT_BE_REJECTED')
                ];
                (new Notice())->sendEmail($emails);
            }
            $param[] = [$report[0]['name'], xphp_get_lang('WEB_PLATFORM_INDUSTRY_REPORT_LOG_APPROVAL_REJECTED')];
        }
        // 写系统操作日志
        foreach ($param as $item) {
            $this->systemLog($desk, $item);
        }
    }

    /**
     * 分享报告
     * @param array $params 参数
     * @return bool
     */
    public function shareReport($params)
    {
        $noticeFlag = $params['notice_flag'];
        $userUuids = $params['users'] == '' ? '' : explode(',', $params['users']);
        $userGroupUuids = $params['user_groups'] == '' ? '' : explode(',', $params['user_groups']);
        $reportUuid = $params['report_uuid'];
        $approvalUuid = $params['approval_uuid'];
        $currentUser = xphp_get_user_info()['userUuid'];

        $table = 'industry_report';
        $uuid = 'report_uuid';
        $desKey = 'PT_INDUSTRY_REPORT_SHARE';
        $lang1 = 'WEB_PLATFORM_INDUSTRY_REPORT_LOG_SHARE_SUCCESS';
        $lang2 = 'UI_PUBLIC_REPORT';
        $lang3 = 'WEB_PLATFORM_INDUSTRY_REPORT_SHARE';
        if (!empty($params['is_plan'])) {
            // 如果传了这个参数，那么表示的方案的操作
            $table = 'industry_plan';
            $uuid = 'plan_uuid';
            $desKey = 'PT_INDUSTRY_PLAN_SHARE';
            $lang1 = 'WEB_PLATFORM_INDUSTRY_PLAN_LOG_SHARE_SUCCESS';
            $lang2 = 'UI_PUBLIC_PLAN';
            $lang3 = 'WEB_PLATFORM_INDUSTRY_PLAN_SHARE';
        }

        // 查询审批层级
        $sql = "select now_user_depth, approval_list, name from {$table} where {$uuid} = ?";
        $sqlParams = [$reportUuid];
        $data = $this->dbSelect($sql, $sqlParams);
        $nowDepth = $data[0]['now_user_depth'];
        $approvalList = json_decode($data[0]['approval_list'], true);

        // 查找当前层级，如果当前用户在多个审批层级，只需要找一个层级插入即可
        // 这里需要查询出用户所在的用户组,层级可能是用户组uuid
        $userGroup = $this->dbSelect(
            "select user_group_uuid from mt_user_user_group where user_uuid = ?",
            [$currentUser]
        );
        $userGroupArr = array_column($userGroup, 'user_group_uuid');
        $nowUserDepthArr = [];
        foreach ($approvalList as $key => $dep) {
            if (($dep['user_uuid'] == $currentUser || in_array($dep['user_uuid'], $userGroupArr)) && $key != 0) {
                // 如果是当前用户的层级，存入数组，层级从$depth的1开始，所以$key要减一
                array_push($nowUserDepthArr, $key - 1);
            }
        }
        $this->insertCopySendUsers(
            $userUuids,
            $userGroupUuids,
            $approvalUuid,
            $nowUserDepthArr[0] ?? 0,
            $reportUuid,
            2
        );

        foreach ($approvalList as $key => &$list) {
            if ($key == $nowDepth + 1) {
                $list['share'] = [];
                $list['share']['users'] = $userUuids;
                $list['share']['user_groups'] = $userGroupUuids;
                $list['share']['notice'] = $noticeFlag;
            }
        }

        $updateSql = "update {$table} set approval_list = ? where {$uuid} = ?";
        $updateSqlParams = [json_encode($approvalList), $reportUuid];
        $result = $this->dbExec($updateSql, $updateSqlParams);
        if ($result['code'] == 0) {
            // 写成功日志
            $this->systemLog(
                $desKey,
                [xphp_get_lang($lang1)]
            );
        }
        // 这时候还需要向industry_user表插入记录和更新当前的报告的审批流里面的记录
        if (
            !empty($approvalList[$nowDepth + 1]['share']['users']) ||
            !empty($approvalList[$nowDepth + 1]['share']['user_groups'])
        ) {
            $this->sendResultToUser(
                $approvalList[$nowDepth + 1]['share']['users'],
                $approvalList[$nowDepth + 1]['share']['user_groups'],
                xphp_get_lang($lang2) . '【' . $data[0]['name'] . '】,' .
                xphp_get_lang('WEB_PLATFORM_INDUSTRY_REPORT_USERS') . xphp_get_user_info()['userName'] .
                xphp_get_lang('WEB_PLATFORM_INDUSTRY_REPORT_SHARE_TO_YOU'),
                xphp_get_lang($lang3)
            );
        }
        return $result;
    }

    /**
     * 抄送/分享人插入数据库
     * @param array  $userUuids      用户uuid数组
     * @param array  $userGroupUuids 用户组uuid数组
     * @param string $approvalUuid   审批流uuid
     * @param int    $depth          层级
     * @param string $reportUuid     报告uuid
     * @param int    $mode           1抄送，2分享
     * @return array
     */
    public function insertCopySendUsers(
        $userUuids,
        $userGroupUuids,
        $approvalUuid,
        $depth = 0,
        $reportUuid = '',
        $mode = 1
    ) {
        if (!empty($userUuids)) {
            foreach ($userUuids as $key => $users) {
                $uuid = xphp_uuid();
                $sql = "insert into industry_user 
            (uuid, user_type, user_uuid, report_uuid, approval_uuid, depth, create_time)
            values (?,?,?,?,?,?,?)";
                $sqlParams = [
                    $uuid, $mode, $userUuids[$key], $reportUuid, $approvalUuid,
                    $depth, date(xphp_get_config('special', 'dateformat'))
                ];
                $this->dbExec($sql, $sqlParams);
            }
        }
        if (!empty($userGroupUuids)) {
            foreach ($userGroupUuids as $key => $groups) {
                // 找出每个user组的所有user
                $userSql = "select user_uuid from mt_user_user_group where user_group_uuid = ?";
                $userSqlParams = [$userGroupUuids[$key]];
                $users = $this->dbSelect($userSql, $userSqlParams);
                $users = array_column($users, 'user_uuid');
                if (empty($users)) {
                    continue;
                }

                foreach ($users as $key => $u) {
                    $uuid = xphp_uuid();
                    $sql = "insert into industry_user 
                (uuid, user_type, user_uuid, report_uuid, approval_uuid, depth, create_time)
                values (?,?,?,?,?,?,?)";
                    $sqlParams = [
                        $uuid, $mode, $u, $reportUuid, $approvalUuid, $depth,
                        date(xphp_get_config('special', 'dateformat'))
                    ];
                    $this->dbExec($sql, $sqlParams);
                }
            }
        }
    }

    /**
     * 评论报告
     * @param array $params 参数
     * @return bool
     */
    public function remarkReport($params)
    {
        $comment = $params['comment'];
        $reportUuid = $params['report_uuid'];
        $userUuid = xphp_get_user_info()['userUuid'];
        $time = date(xphp_get_config('special', 'dateformat'));
        $updateSql = "update industry_user set content = ?, update_time = ? where report_uuid = ? and user_uuid = ?";
        $updateSqlParams = [$comment, $time, $reportUuid, $userUuid];
        return $this->dbExec($updateSql, $updateSqlParams);
    }

    /**
     * 获取用户所在某个报告的所有层级
     * @param string $user       用户uuid
     * @param string $reportUuid 报告uuid
     * @return array
     */
    public function getUserDepth($user, $reportUuid)
    {
        $depthSql = "select approval_list from industry_report where report_uuid = '{$reportUuid}'";
        $depth = $this->dbSelect($depthSql)[0]['approval_list'];
        $depth = json_decode($depth, true);
        $nowUserDepthArr = [];
        // 这里需要查询出用户所在的用户组,层级可能是用户组uuid
        $userGroup = $this->dbSelect("select user_group_uuid from mt_user_user_group where user_uuid = ?", [$user]);
        $userGroupArr = array_column($userGroup, 'user_group_uuid');

        foreach ($depth as $key => $dep) {
            if (($dep['user_uuid'] == $user || in_array($dep['user_uuid'], $userGroupArr)) && $key != 0) {
                // 如果是当前用户的层级，存入数组，层级从$depth的1开始，所以$key要减一
                array_push($nowUserDepthArr, $key - 1);
            }
        }
        return $nowUserDepthArr;
    }

    /**
     * 获取报告评论列表
     * @param array $params 参数
     * @return array
     */
    public function getReportCommentList($params)
    {
        $type = $params['type']; //1抄送，2分享
        $offset = $params['offset'];
        $limit = $params['limit'];
        $sort = $params['sort'];
        $order = $params['order'];
        $user = xphp_get_user_info()['userUuid'];
        $reportUuid = $params['report_uuid'];

        $nowUserDepthArr = $this->getUserDepth($user, $reportUuid);
        $nowUserDepthArr = "('" . implode("','", $nowUserDepthArr) . "')";
        $sql = "select distinct iu.content, iu.update_time, iu.user_type, bu.user_name
                from industry_user iu  left join bd_user bu on iu.user_uuid = bu.user_uuid
         where iu.report_uuid = '{$reportUuid}'";
        // $left = " left join bd_user bu on iu.uuid = bu.user_uuid";
        $sqlCount = "select count(distinct iu.user_uuid, iu.user_type) as total
                        from industry_user iu where report_uuid = '{$reportUuid}'";

        // if ($user != 'a508b813-19c7-eb4e-d6fa-bb61b25a4de9') {
        //     // admin可查看所有
        //     $sql .= " and iu.depth in {$nowUserDepthArr}";
        //     $sqlCount .= " and iu.depth in {$nowUserDepthArr}";
        // }

        $count = $this->dbSelect($sqlCount);
        $total = $count[0]['total'];

        // if (!empty($sort) && !empty($order)) {
        //     $sortArr = [
        //         'user_name' => 'bu.user_name',
        //         'create_time' => 'ir.create_time',
        //         'status' => 'ir.status',
        //     ];
        //     $sort = $sortArr[$sort] ?? 'ir.id';
        //     $sql .= " order by {$sort} {$order}";
        // }

        $limit = " limit {$offset}, {$limit}";
        // dump($sql . $limit);
        $data = $this->dbSelect($sql . $limit);

        $records = [];
        foreach ($data as $d) {
            $records[] = array(
                'content' => $d['content'] ?? xphp_get_lang('WEB_PLATFORM_INDUSTRY_REPORT_NO_RESULT'),
                'user_name' => $d['user_name'],
                'time' => $d['update_time'] ?? '--',
                'user_type' => $d['user_type']
            );
        }

        return [
            'rows' => $records,
            'total' => $total
        ];
    }

    /**
     * 获取当前用户独立密码
     * @return array
     */
    public function getCustomPwd()
    {
        $user = xphp_get_user_info()['userUuid'];
        $sql = "select custome_password from bd_user where user_uuid = '{$user}'";
        $return = $this->dbSelect($sql);
        return $return[0];
    }

    /**
     * 判断是否是全局观察者-操作或超级管理员-内部调用
     * @return void|bool
     */
    public function checkIsGlobal()
    {
        $user = xphp_get_user_info();
        if (
            !in_array('global_write', $user['permissionArr']) && in_array('global_read', $user['permissionArr'])
            && $user['userLevel'] != 1
        ) {
            // 没有全局观察者操作权限,有全局观察者查看权限,并且不是超级管理员，直接返回不能操作 包括自己创建的任务也不能操作，反正啥都不能干
            exit($this->muOpResult(
                false,
                xphp_get_lang('UI_PUBLIC_TIPS'),
                xphp_get_lang('WEB_ERROR_BD_PRIVILEGE_ERROR')
            ));
        }

        return true;
    }

    /**
     * 获取报告下载的属性
     * @param string $uuid 报告uuid
     * @return array
     */
    public function getPrePosition(string $uuid)
    {
        $items = $this->viewReport($uuid, true);
        $position = $items['position'];
        $dir = dirname(__DIR__, 7) . '/';
        foreach ($position as &$item) {
            $item['name'] = xphp_get_lang($item['name']);
            switch ($item['type']) {
                case 'item_logo':
                    $logo = $dir . '/img/platform/logo.png';
                    if ($items['logo']['flag']) {
                        $logo = $dir . $items['logo']['value'];
                    }
                    $item['value'] = $logo;
                    break;
                case 'item_qrcode':
                    $qrcode = $dir . (trim($items['qrcode'], '.'));
                    $item['value'] = $qrcode;
                    break;
                case 'item_sys_code':
                    $item['en_name'] = 'System Code';
                    break;
                case 'item_sys_name':
                    $item['en_name'] = 'System Name';
                    break;
                case 'item_host':
                    $item['en_name'] = 'Host Name';
                    break;
                case 'item_ip':
                    $item['en_name'] = 'IP Address';
                    break;
                case 'item_operator':
                    $item['en_name'] = 'Operator';
                    break;
                case 'item_results':
                    $item['en_name'] = 'Final Result';
                    $item['value'] = $items['results'] ? '正常' : '异常';
                    break;
                case 'item_plan':
                    $item['en_name'] = 'VERFICATION PLAN';
                    $item['value'] = $items['item_plan'];
                    break;
                case 'item_description':
                    $item['en_name'] = 'VERIFIVATION INFORMATION';
                    break;
                case 'item_module':
                    $item['en_name'] = 'Device Name';
                    $item['value'] = $items['host'];
                    break;
                case 'item_point':
                    $item['en_name'] = 'Backup Time Point';
                    $item['value'] = $items['timepoint'];
                    break;
                case 'item_integrality':
                    $item['en_name'] = 'Boot Verification Result';
                    $item['value'] = $items['integrality'] ? '正常' : '异常';
                    break;
                case 'item_screen_verify':
                    $item['en_name'] = 'Screenshot Comparison Results';
                    $item['value'] = $items['screen_result'] == 1 ? '一致' : '不一致';
                    break;
                case 'item_files':
                    $item['en_name'] = 'File Comparison Result';
                    $item['value'] = $items['files'] ? '一致' : '不一致';
                    break;
                case 'item_open':
                    $item['en_name'] = 'BOOT VERIFICATION';
                    $item['value'] = $dir . (trim($items['open'], '.'));
                    break;
                case 'item_document':
                    $item['en_name'] = 'FILE COMPARSION';
                    $lists = $this->documentLists(
                        [
                            'report_uuid' => $uuid,
                            'offset' => 0,
                            // 报告展示文件比对数量
                            'limit' => $items['document_num'] ?: 20,
                        ]
                    );
                    $enValue = vsprintf(
                        xphp_get_lang('WEB_INDUSTRY_ITEM_DOCUMENT_TOTAL'),
                        [$lists['totals'], $lists['normal'], $lists['totals'] - $lists['normal']]
                    );
                    if ($this->isEnch) {
                        $enValue .= "(Total comparison：{$lists['totals']}，individual consistent：{$lists['normal']}，individual inconsistent：" . $lists['totals'] - $lists['normal'] . ')';
                    }
                    $item['en_value'] = $enValue;
                    $document = $lists['rows'];
                    // 文件对比结果列表需要查询，还要判断下是否显示哪些元素
                    // 确定有哪些
                    $documentCustom = $items['document_custom'];
                    foreach ($document as &$itemf) {
                        if (empty($documentCustom['file_size'])) {
                            unset($itemf['verify']['size']);
                            unset($itemf['produce']['size']);
                        }
                        if (empty($documentCustom['file_position'])) {
                            unset($itemf['verify']['attribute']);
                            unset($itemf['produce']['attribute']);
                        }
                        if (empty($documentCustom['file_create'])) {
                            unset($itemf['verify']['create_time']);
                            unset($itemf['produce']['create_time']);
                        }
                        if (empty($documentCustom['file_update'])) {
                            unset($itemf['verify']['modify_time']);
                            unset($itemf['produce']['modify_time']);
                        }
                    }
                    $item['value'] = $document;
                    break;
                case 'item_screen':
                    $item['en_name'] = 'SCREENSHOT COMPARSION';
                    // 文件对比结果列表需要查询，还要判断下是否显示哪些元素
                    $screen = $items['screen_list'];
                    foreach ($screen as $k => $v) {
                        $screen[$k]['product'] = $dir . $v['product'];
                        $screen[$k]['verify'] = $dir . $v['verify'];
                    }
                    $item['value'] = $screen;
                    break;
                case 'item_result':
                    $item['en_name'] = 'VERFICATION CONCLUSION';
                    $item['value'] = $items['item_result'];
                    break;
                case 'item_auditor_sign_sys':
                    $item['en_name'] = 'Auditor(Sys_signature)';
                    $item['value'] = $items['auditor_sign_sys'];
                    break;
                case 'item_operator_sign_sys':
                    $item['en_name'] = 'Operator(Sys_signature)';
                    $item['value'] = $items['operator_sign_sys'];
                    break;
                case 'item_make_time':
                    $item['en_name'] = 'Report Generation Time';
                    $item['value'] = $items['report_time'];
                    break;
                case 'item_download_time':
                    $item['en_name'] = 'Report Download Time';
                    $item['value'] = date(xphp_get_config('special', 'dateformat'));
                    break;
                case 'item_operator_sign':
                    $item['en_name'] = 'Auditor(Signature)';
                    break;
                case 'item_auditor_sign':
                    $item['en_name'] = 'Operator(Signature)';
                    break;
                case 'item_approval':
                    $item['en_name'] = 'APPROVAL PROCESS';
                    $approval = [];
                    foreach ($items['approval_list'] as $item2) {
                        $approval[] = [
                            'create_time' => $item2['create_time'] ?: '--',
                            'position' => $item2['position'],
                            'user_name' => $item2['user_name'],
                            'desc' => $item2['desc'],
                            'remark' => $item2['remark'],
                            'change' => $item2['change'] ?: [],
                        ];
                    }
                    $item['value'] = $approval;
                    break;
            }
        }

        if (!$this->isEnch) {
            // 去除 en_name 的值
            $position = array_map(function ($item) {
                $item['en_value'] = '';
                return $item;
            }, $position);
        }

        return $position;
    }
}
