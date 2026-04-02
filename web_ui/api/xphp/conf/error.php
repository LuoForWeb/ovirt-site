<?php
/**
 * 错误定义文件
 */
return array(
    'errorCode' => array(
        //******平台错误定义******//
        'BD_GENERIC_SUCCESS',				//generic success
        'BD_GENERIC_ERROR',					//generic error
        'BD_NOT_INIT_ERROR',				//object not init error
        'BD_REPEAT_INIT_ERROR',				//object repeat init error
        'BD_INVALID_PARAM_ERROR',			//parameter error
        'BD_QUEUE_EMPTY_ERROR',				//queue empty						
        'BD_MEM_FAILED_ERROR',				//memory failed

        'BD_DB_INIT_ERROR',					//db init error
        'BD_DB_NOT_INIT_ERROR',				//db not init error
        'BD_DB_POOL_INIT_ERROR',			//db pool init error
        'BD_DB_POOL_NOT_INIT_ERROR',		//db pool not init error
        'BD_DB_SENTENCE_INVALID_ERROR',		//sql sentence invalid
        'BD_DB_DO_QUERY_ERROR',				//do query error
        'BD_DB_RESULT_EMPTY_ERROR',			//query result is empty
        'BD_DATABASE_CONNECT_ERROR',		//connect database error

        'BD_NET_SEARCH_CONN_ERROR',			//can't find the target conection
        'BD_NET_SEARCH_FD_ERROR',			//can't find the target socket fd
        'BD_NET_TIME_OUT_ERROR',			//socket time out
        'BD_NET_BIND_ERROR',				//socket bind error
        'BD_NET_LISTEN_ERROR',				//socket listen error
        'BD_NET_CONNECT_ERROR',				//socket connect error
        'BD_NET_SET_NONBOLCK_ERROR',		//set fd to nonblocking error
        'BD_NET_PERR_CLOSE_CONN_ERROR',		//peer close connection
        'BD_NET_HEADER_MAGIC_ERROR',		//header magic invalid
        'BD_NET_HEADER_NOT_COMPLETE_ERROR',	//header not read complete
        'BD_NET_PACKET_INVALID_ERROR',		//packet invalid
        'BD_NET_INVALID_WRITE_ITEM_ERROR',	//connection write item error
        'BD_NET_PT_SERVER_NOT_READY',		//remote server platform not ready
        'BD_NET_JSON_KEY_NOT_FOUND_ERROR',	//json key not found error
        'BD_WOULDBLOCK_ERROR',				//resource temporarily unavailable
        'BD_INTR_ERROR',					//interrupted function call
        'BD_NET_RECV_TIMEOUT_ERROR',			//receive data timeout
        'BD_NET_SOCKET_CLOSED_BY_PEER_ERROR',	//socket closed by peer
        'BD_NET_RECV_DATA_ERROR',               //Recv data error
        'BD_NET_SEND_DATA_ERROR',               //Send data error

        'BD_WORK_THREAD_NOT_EXIST_ERROR',		//work thread not exist error
        'BD_WORK_THREADUUID_NOT_EXIST_ERROR',	//work thread uuid not exist error
        'BD_WORK_THRED_EXIST_ERROR',			//work thread exist error
        'BD_WORK_THREADUUID_EXIST_ERROR',		//work thread uuid exist error

        'BD_TASK_EXIST_ERROR',				    //task exist error
        'BD_TASK_ALREADY_RUNNING_ERROR',		//task already running
        'BD_TASK_NOT_EXIST_ERROR',			    //task not exist
        'BD_TASK_NOT_STOPPED_ERROR',			//task not stoped error
        'BD_TASK_BACKUP_LIST_IS_EMPTY_ERROR',	//task backup list is empty error
        'BD_TASK_RECOVERY_LIST_IS_EMPTY_ERROR',	//task recovery list is empty error
        'BD_TASK_BE_CANCELLED_ERROR',			//task be cancelled error
        'BD_TASK_FAIL_ERROR',                   //task error
        'BD_TASK_ANBNORMAL_ERROR',              //task anbnormal error

        'BD_HAS_BACKUP_EXISTED_AND_NOT_STOPPED_ERROR',		//has backup task existed and not stopped error
        'BD_HAS_RECOVERY_EXISTED_AND_NOT_STOPPED_ERROR',	//has backup task existed and not stopped error

        'BD_TIMEPOINT_NOT_EXIST_ERROR',		    //timepoint not exist error
        'BD_TIMEPOINT_NOT_UNIQUE_ERROR',		//timepoint not unique error
        'BD_DELETE_TIMEPOINT_DIR_ERROR',		//delete timepoint dir error
        'BD_SET_TIMEPOINT_DELETE_ERROR',		//set timepoint delete flag error
        'BD_TIMEPOINT_IN_USE_BY_TASK_ERROR',	//timepoint is in using by task
        'BD_USER_NOT_EXIST_ERROR',			    //user not exist error
        'BD_NETWORK_RECONNECT_ERROR',			//network reconnect error
        'BD_AGENT_NOT_EXIST_ERROR',			    //agent not exist

        'BD_STORAGE_SPACE_NOT_ENOUGH_ERROR',	//storage space is not enough error
        'BD_SAVE_SELF_EXPLAN_FILE_ERROR',		//save self explan file error

        'BD_ENCRYPT_ERROR',					    // encrypt error
        'BD_DECRYPT_ERROR',					    // decrypt error
        'BD_ENCRYPT_SET_KEY_ERROR',			    // encrypt error on set key
        'BD_ENCRYPT_READ_PUBLIC_KEY_ERROR',		// read public key error
        'BD_ENCRYPT_READ_PRIVATE_KEY_ERROR',	// read private key error

        'BD_PRIVILEGE_ERROR',					// don't have privilege to access object
        'BD_CONDITION_VAR_TIMEOUT_ERROR',		// condition var timeout error

        'BD_SNAPSHOT_ERROR',					// snapshot generic error
        'BD_SNAPSHOT_BACKUP_COMPONENTS_INIT_ERROR',	  // snapshot backup components init error
        'BD_SNAPSHOT_ADD_TO_SET_ERROR',		          // snapshot add to snapshot set error
        'BD_SNAPSHOT_CREATE_ERROR',			          // create snapshot error
        'BD_SNAPSHOT_NOT_EXIST',				      // snapshot not exist
        'BD_SNAPSHOT_SET_NOT_EXIST',			      // snapshot set not exist
        'BD_SNAPSHOT_DELETE_ERROR',			          // snapshot delete error
        'BD_SNAPSHOT_BAD_STATE_ERROR',		          // snapshot object not init, or be called withinin invalid sequence
        'BD_SNAPSHOT_MAX_VOL_REACHED_ERROR',	      // add to snapshot set reached max volumes
        'BD_SNAPSHOT_MAX_SNAPSHOT_REACHED_ERROR',     // reached max snapshot
        'BD_SNAPSHOT_INVALID_XML_ERROR',		      // xml document invalid

        'BD_RPC_FILE_NOT_EXIST',				//request file not exist
        'BD_RPC_FILE_ALREADY_EXIST',			//file already exist
        'BD_RPC_HANDLE_NOT_FOUND',			    //file handle not found
        'BD_RPC_NETWORK_ERROR',				    //data transport connection error
        'BD_RPC_MSG_ERROR',					    //invalid message
        'BD_RPC_NOT_SAME_CONNECTION_ERROR',	    //not the same connection
        'BD_RPC_ADD_DATA_CONN_ERROR',			//add data connction error
        'BD_RPC_DATA_CONN_NOT_FOUND_IN_HASH',	//not found data conntion in hashmap

        'BD_FILE_OPEN_ERROR',					//open file error
        'BD_FILE_CREATE_ERROR',				    //create file error
        'BD_FILE_WRITE_ERROR',				    //write file error
        'BD_FILE_READ_ERROR',					//read file error
        'BD_FILE_LSEEK_ERROR',				    //lseek file error
        'BD_FILE_ACCESS_DENIED',				//access denied
        'BD_FILE_STAT_ERROR',					//stat file error
        'BD_FILE_DELETE_ERROR',				    //delete file error
        'BD_FILE_ALREADY_EXIST_ERROR',		    //file already exist
        'BD_FILE_NOT_EXIST_ERROR',			    //file not exist
        'BD_FILE_RENAME_ERROR',				    //file rename error

        'BD_AGENT_SPACE_STORAGE_IS_NOT_ENOUGH_ERROR',	//agent storage space is not enough
        'BD_AGENT_IS_OFFLINE_ERROR',					//agent not online error
        'BD_AGENT_IS_NOT_AUTH_ERROR',					//agent module is not authed

        'BD_TIMEPOINT_IS_NOT_FULL_ERROR',				// timepoint is not full mode error
        'BD_TIMEPOINT_IS_DEPEND_BY_OTHERS_ERROR',		// timepoint is depended by other timepoints
        'BD_TIMEPOINT_IS_BROKEN_ERROR',				    // timepoint is invalid, some importance data miss
        'BD_COMPRESS_COMMON_ERROR',					    // compress common error
        'BD_COMPRESS_READ_EOF_ERROR',					// compress read eof error
        'BD_COMPRESS_INIT_LIB_ERROR',					// init lib error
        'BD_COMPRESS_INVALID_DATA_ERROR',				// invalid compressed data
        'BD_COMPRESS_DO_COMPRESS_ERROR',				// do compress error
        'BD_COMPRESS_DO_UNCOMPRESS_ERROR',			    // do uncompress error

        'BD_VHD_ALREADY_OPEN_ERROR',					// vhd already open
        'BD_VHD_NOT_OPEN_ERROR',						// vhd not open
        'BD_VHD_NOT_SUPPORT_TYPE_ERROR',				// not support vhd type
        'BD_VHD_INVALID_FILE_SIZE_ERROR',				// invalid file size
        'BD_VHD_FOOTER_INVALID_ERROR',				    // found invalid footer info
        'BD_VHD_SPARSE_HEADER_INVALID_ERROR',			// found invalid sparse header
        'BD_VHD_BATMAP_HEADER_INVALID_ERROR',			// found invalid batmap header
        'BD_VHD_BATMAP_NOT_EXIST_ERROR',				// batmap not exist error
        'BD_VHD_BAT_INVALID_ERROR',					    // invalid bat
        'BD_VHD_FIND_CONTEXT_BY_PARENT_UUID_ERROR',	    // can't found the target parent vhd context

        'BD_PARSE_JSON_STRING_ERROR',					// Parse json string error
        'BD_TASK_RECOVERY_POSITION_SPACE_IS_NOT_ENOUGH_ERROR',	//recovery storage space is not enough
        'BD_POWER_LOSS_OR_PROGRAM_CRASH_ERROR',					//power loss or program crash error

        'BD_SCAN_RAW_STORAGE_ERROR',						//scan raw storage error
        'BD_FILESYSTEM_BUSY_ERROR',						    //filesystem is busy
        'BD_MOUNT_FILESYSTEM_ERROR',						//mount file system error
        'BD_UMOUNT_FILESYSTEM_ERROR',						//umount file system error
        'BD_UNKNOW_STORAGE_TYPE_ERROR',					    //known storage type error
        'BD_STORAGE_IS_TOO_SMALL_ERROR',					//storage is too small
        'BD_CREATE_PARTITION_FOR_DISK_ERROR',				//create partition error
        'BD_CREATE_FILESYSTEM_ERROR',						//create file system error
        'BD_STORAGE_IS_NOT_EXIST_ERROR',					//storage is no exist error
        'BD_STORAGE_NOT_UNIQUE_ERROR',					    //storage is not unqiue
        'BD_STORAGE_NEW_PART_NOT_FOUND_ERROR',			    //not found the new create partion
        'BD_PARSE_STORAGE_INFO_ERROR',					    //parse storage info error
        'BD_STORAGE_SELF_EXPLAN_FILE_NOT_EXIST_ERROR',	    //storage self explan file is not exist error
        'BD_STORAGE_PARSE_SELF_EXPLAN_FILE_ERROR',		    //parse self explan file error
        'BD_STORAGE_SAVE_SELF_EXPLAN_FILE_ERROR',			//save storage self explan file error
        'BD_STORAGE_CREATE_MOUNT_DIR_ERROR',				//create mount directory error
        'BD_STORAGE_PRIMARY_KEY_IS_EMPRY_ERROR',			//storage primary key is empty
        'BD_STORAGE_IS_NOT_AVAILABLE_ERROR',				//storage is not available
        'BD_STORAGE_IS_ALREADY_ADDED_ERROR',				//storage is already  added
        'BD_STORAGE_GET_RAW_STORAGE_INFO_ERROR',			//get raw storge info error
        'BD_STORAGE_GET_FC_WWN_ERROR',					    //get fc wwn error
        'BD_STORAGE_SCAN_SCSI_DEVICE_ERROR',				//scan scsi device error
        'BD_STORAGE_NOT_FOUND_FC_DEVICE_ERROR',			    //not found fc device error
        'BD_STORAGE_SHARE_NAME_FORMAT_ERROR',				//storage share name format error
        'BD_STORAGE_HOST_UNREACH_ERROR',					//host unreach error
        'BD_STORAGE_NFS_STALE_HANDLE_ERROR',				//nfs handle is stale error
        'BD_STORAGE_NOT_EXIST_ERROR',						// storage not exist in database
        'BD_STORAGE_NOT_BELONG_THIS_NODE_ERROR',			// storage not belong this node
        'BD_STORAGE_HAVE_NO_SR_IN_NODE_ERROR', 			    // there is not exist available storage
        'BD_STORAGE_IS_CHANGED_ERROR',                      // backup storage is changed
        'BD_STORAGE_SCAN_BACKUP_TIMEPOINT_ERROR',			// scan backup timepoints from storage error
        'BD_STORAGE_NOT_AVAILABLE_ERROR',					// storage is not available
        'BD_STORAGE_ISCSI_INITIATOR_NAME_FILE_NOT_EXIST_ERROR',		// not iscsi initiator name file not exist
        'BD_STORAGE_GET_ISCSI_INITIATOR_NAME_ERROR',				// get iscsi initiator name error
        'BD_STORAGE_SCAN_TARGET_IQN_ERROR',				    // scan target iqn error
        'BD_STORAGE_LOGIN_ISCSI_TARGET_ERROR',			    // login target host error
        'BD_STORAGE_GET_DEV_REAL_PATH_ERROR',				// get device real path error
        'BD_STORAGE_GET_DEV_SOURCE_ERROR',				    // get device source error
        'BD_STORAGE_MOUNT_POINT_IS_CHANGED_ERROR',		    // storage mount point is changed error
        'BD_NODE_IS_NOT_EXIST_ERROR',						// node is not exist
        'BD_CURL_GLOBAL_INIT_ERROR',						// lib curl global init error
        'BD_CURL_INIT_ERROR',								// curl object init error
        'BD_CURL_PERFORM_HTTP_POST_ERROR',				    // perform http post error
        'BD_CURL_PERFORM_HTTP_GET_ERROR',					// perform http get error

        'BD_LICENSE_EMPTY_ERROR',                           //license is not add error
        'BD_VM_HOST_NOT_EXIST_ERROR',                       //host is not exist error
        'BD_SYSTEM_NOT_AUTH_ERROR',                         //system is not auth error
        'BD_LICENSE_EXHAUST_ERROR',                         //license exhuast error
        'BD_MODULE_SERVER_NOT_EXIST_ERROR',                 //module server process not exist
        'BD_TIMEPOINT_IS_USED_BY_RECOVERY_OR_INSTANT_RECOVERY_TASK_ERROR',  //timepoint is used by recovery task or instant recovery task
        'BD_SYSTEM_REBOOT_ABNORMAL',                    //service or system abnormal reboot

        'BD_DISK_GET_PED_DEVICE_ERROR',					// get ped deviceerror
        'BD_DISK_GET_PED_DISK_ERROR',					// get ped disk error

        'BD_LVM_GENERIC_ERROR',							// common lvm error
        'BD_LVM_INIT_ERROR',							// initialzie lvm lib error
        'BD_LVM_RELOAD_ERROR',							// reload lvm lib error
        'BD_LVM_SCAN_ERROR',							// lvm scan error
        'BD_LVM_VG_OPEN_ERROR',							// open vg error
        'BD_LVM_PV_IS_NOT_USED_ERROR',					// lvm pv is not used error

        'BD_GPT_LBA0_MBR_INVALID_ERROR',                // gpt reserved mbr is invliad
        'BD_GPT_LBA1_HEADER_INVALID_ERROR',             // gpt header is invliad 
        'BD_GPT_NOT_EXIST_VSS_PARTITION_ERROR',         // current disk has not vss partition

        'BD_BACKUP_NODE_IS_ABNORMAL_ERROR',             // service in backup node is stopped


        'BD_LIBVIRT_ERROR',                             //libvirt common error
        'BD_XML_PARSE_ERROR',                           //parse xml error
        'BD_NOT_SUPPORT_SUB_BACKUP_TYPE_ERROR',         //not support sub backup type
        'BD_NOT_SUPPORT_SUB_RECOVERY_TYPE_ERROR',       //not support sub recovery type
        'BD_RPC_CONNECTION_NOT_FOUND_ERROR',            //rpc connection not found error
        'BD_RPC_CONNECTION_ALREADY_EXIST_ERROR',        //rpc connection is already exist error
        'BD_BACKUP_TASK_LEVEL_DOWN_ERROR',              //backup task level down
        'BD_TIMEPOINT_REACH_MAX_ERROR',                 //backup timepoint reach max error
        'BD_PARAM_TOO_LONG_ERROR',                      // parameter value length too long

        'BD_CURL_ERROR',									// common curl error
        'BD_CURL_UNSUPPORTED_PROTOCOL_ERROR',				// Curl Error: unsupported protocol error
        'BD_CURL_FAILED_INIT_ERROR',						// Curl Error: initialzie failed
        'BD_CURL_URL_MALFORMAT_ERROR',					// Curl Error: URL malformat error
        'BD_CURL_NOT_BUILT_IN_ERROR',						// Curl Error: not build in error"
        'BD_CURL_COULDNT_RESOLVE_PROXY_ERROR',			// Curl Error: could not resolve proxy
        'BD_CURL_COULDNT_RESOLVE_HOST_ERROR',				// Curl Error: could not resolve host
        'BD_CURL_COULDNT_CONNECT_ERROR',					// Curl Error: could not connect
        'BD_CURL_FTP_WEIRD_SERVER_REPLY_ERROR',			// Curl Error: FTP weird server reply error
        'BD_CURL_REMOTE_ACCESS_DENIED_ERROR',				// Curl Error: a service was deny by the server due to lack of access
        'BD_CURL_FTP_ACCEPT_FAILED_ERROR',				// Curl Error: FTP accept failed
        'BD_CURL_FTP_WEIRD_PASS_REPLY_ERROR',				// Curl Error: FTP weird pass reply error
        'BD_CURL_FTP_ACCEPT_TIMEOUT_ERROR',				// Curl Error: tiemout occurred ACCEPT FTP server
        'BD_CURL_FTP_WEIRD_PASV_REPLY_ERROR',				// Curl Error: weird PASV reply error
        'BD_CURL_FTP_WEIRD_227_FORMAT_ERROR',				// Curl Error: weird 227 format error
        'BD_CURL_FTP_CANT_GET_HOST_ERROR',				// Curl Error: FTP can't get host error
        'BD_CURL_HTTP2_ERROR',							// Curl Error: a problem in the HTTP2 framing layer
        'BD_CURL_FTP_COULDNT_SET_TYPE_ERROR',				// Curl Error: FTP could not set type
        'BD_CURL_PARTIAL_FILE_ERROR',						// Curl Error: patial file error
        'BD_CURL_FTP_COULDNT_RETR_FILE_ERROR',			// Curl Error: FTP could not RETR file error
        'BD_CURL_QUOTE_ERROR',							// Curl Error: QUOTE command failed
        'BD_CURL_HTTP_RETURNED_ERROR',					// Curl Error: HTTP returned error
        'BD_CURL_WRITE_ERROR',							// Curl Error: write error
        'BD_CURL_UPLOAD_FAILED_ERROR',					// Curl Error: upload failed
        'BD_CURL_READ_ERROR',								// Curl Error: open/read from file error
        'BD_CURL_OUT_OF_MEMORY_ERROR',					// Curl Error: out of memory error
        'BD_CURL_OPERATION_TIMEDOUT_ERROR',				// Curl Error: operationg timeout
        'BD_CURL_FTP_PORT_FAILED_ERROR',					// Curl Error: FTP PORT operation failed
        'BD_CURL_FTP_COULDNT_USE_REST_ERROR',				// Curl Error: REST command failed
        'BD_CURL_RANGE_ERROR',							// Curl Error: RANGE command error
        'BD_CURL_HTTP_POST_ERROR',						// Curl Error: POST command error
        'BD_CURL_SSL_CONNECT_ERROR',						// Curl Error: error occurred when connecting with SSL
        'BD_CURL_BAD_DOWNLOAD_RESUME_ERROR',				// Curl Error: could not resume download
        'BD_CURL_FILE_COULDNT_READ_FILE_ERROR',			// Curl Error: could not read file error
        'BD_CURL_LDAP_CANNOT_BIND_ERROR',					// Curl Error: LDAP could not bind error
        'BD_CURL_LDAP_SEARCH_FAILED_ERROR',				// Curl Error: LDAP search failed
        'BD_CURL_FUNCTION_NOT_FOUND_ERROR',				// Curl Error: function not found error
        'BD_CURL_ABORTED_BY_CALLBACK_ERROR',				// Curl Error: aborted by callback error
        'BD_CURL_BAD_FUNCTION_ARGUMENT_ERROR',			// Curl Error: bad function argument error
        'BD_CURL_INTERFACE_FAILED_ERROR',					// Curl Error: interface failed error
        'BD_CURL_TOO_MANY_REDIRECTS_ERROR',				// Curl Error: too many redirects error
        'BD_CURL_UNKNOWN_OPTION_ERROR',					// Curl Error: user specified an unknown option error
        'BD_CURL_TELNET_OPTION_SYNTAX_ERROR',				// Curl Error: Malformed telnet option error
        'BD_CURL_PEER_FAILED_VERIFICATION_ERROR',			// Curl Error: peer's certificate or fingerprint wasn't verified fine
        'BD_CURL_GOT_NOTHING_ERROR',						// Curl Error: got nothing error
        'BD_CURL_SSL_ENGINE_NOTFOUND_ERROR',				// Curl Error: SSL crypto engine not found error
        'BD_CURL_SSL_ENGINE_SETFAILED_ERROR',				// Curl Error: can not set SSL crypto engine as default
        'BD_CURL_SEND_ERROR',								// Curl Error: failed to sending network data
        'BD_CURL_RECV_ERROR',								// Curl Error: failure in receiving network data
        'BD_CURL_SSL_CERTPROBLEM_ERROR',					// Curl Error: problem with the local certificate
        'BD_CURL_SSL_CIPHER_ERROR',						// Curl Error: couldn't use specified cipher
        'BD_CURL_SSL_CACERT_ERROR',						// Curl Error: problem with the CA cert
        'BD_CURL_BAD_CONTENT_ENCODING_ERROR',				// Curl Error: Unrecognized/bad encoding content
        'BD_CURL_LDAP_INVALID_URL_ERROR',					// Curl Error: invalid LDAP URL
        'BD_CURL_FILESIZE_EXCEEDED_ERROR',				// Curl Error: maximum file size exceeded error
        'BD_CURL_USE_SSL_FAILED_ERROR',					// Curl Error: requested FTP SSL level failed
        'BD_CURL_SEND_FAIL_REWIND_ERROR',					// Curl Error: sending the data requires a rewind that failed
        'BD_CURL_SSL_ENGINE_INITFAILED_ERROR',			// Curl Error: failed to initialise ENGINE
        'BD_CURL_LOGIN_DENIED_ERROR',						// Curl Error: user, password or similar was not accepted and we failed to login
        'BD_CURL_TFTP_NOTFOUND_ERROR',					// Curl Error: file not found on server
        'BD_CURL_TFTP_PERM_ERROR',						// Curl Error: permission problem on server
        'BD_CURL_REMOTE_DISK_FULL_ERROR',					// Curl Error: out of disk space on server
        'BD_CURL_TFTP_ILLEGAL_ERROR',						// Curl Error: illegal TFTP operation
        'BD_CURL_TFTP_UNKNOWNID_ERROR',					// Curl Error: unknown transfer ID
        'BD_CURL_REMOTE_FILE_EXISTS_ERROR',				// Curl Error: file already exists on remote server
        'BD_CURL_TFTP_NOSUCHUSER_ERROR',					// Curl Error: TFTP no such user
        'BD_CURL_CONV_FAILED_ERROR',						// Curl Error: conversion failed
        'BD_CURL_CONV_REQD_ERROR',						// Curl Error: caller must register conversion,
        //  callbacks using curl_easy_setopt
        //  options CURLOPT_CONV_FROM_NETWORK_FUNCTION,
        //  CURLOPT_CONV_TO_NETWORK_FUNCTION, and CURLOPT_CONV_FROM_UTF8_FUNCTION
        'BD_CURL_SSL_CACERT_BADFILE_ERROR',				// Curl Error: could not load CACERT file, missing or wrong format
        'BD_CURL_REMOTE_FILE_NOT_FOUND_ERROR',			// Curl Error: remote file not found
        'BD_CURL_SSH_ERROR',								// Curl Error: error from the SSH layer,
        //  somewhat generic so the error message will be of interest when this has happened
        'BD_CURL_SSL_SHUTDOWN_FAILED_ERROR',				// Curl Error: failed to shut down the SSL connection
        'BD_CURL_AGAIN_ERROR',							// Curl error: socket is not ready for send/recv, wait till it's ready and try again
        'BD_CURL_SSL_CRL_BADFILE_ERROR',					// Curl Error: could not load CRL file, missing or wrong format
        'BD_CURL_SSL_ISSUER_ERROR',						// Curl Error: Issuer check failed
        'BD_CURL_FTP_PRET_FAILED_ERROR',					// Curl Error: a PRET command failed
        'BD_CURL_RTSP_CSEQ_ERROR',						// Curl Error: mismatch of RTSP CSeq numbers
        'BD_CURL_RTSP_SESSION_ERROR',						// Curl Error: mismatch of RTSP Session Ids
        'BD_CURL_FTP_BAD_FILE_LIST_ERROR',				// Curl Error: unable to parse FTP file list
        'BD_CURL_CHUNK_FAILED_ERROR',						// Curl Error: chunk callback reported error
        'BD_CURL_NO_CONNECTION_AVAILABLE_ERROR',			// Curl Error: No connection available, the session will be queued
        'BD_CURL_SSL_PINNEDPUBKEYNOTMATCH_ERROR',			// Curl Error: specified pinned public key did not match
        'BD_CURL_SSL_INVALIDCERTSTATUS_ERROR',			// Curl Error: invalid certificate status
        'BD_CURL_HTTP2_STREAM_ERROR',						// Curl Error: HTTP2 stream error


        'BD_XML_ATTRIBUTE_NOT_EXIST_ERROR',				// attribute not exist error
        'BD_XML_ELE_NOT_EXIST_ERROR',						// element not exist error

        'BD_HTTP_REQUEST_ERROR',							// unknown http request error
        'BD_HTTP_HEADER_EMPTY_ERROR',						// http response header is empty

        // http 4xx error
        'BD_HTTP_HEADER_400_ERROR',						// http response 400 error, bad request
        'BD_HTTP_HEADER_401_ERROR',						// http response 401 error, unauthorized
        'BD_HTTP_HEADER_402_ERROR',						// http response 402 error, payment required
        'BD_HTTP_HEADER_403_ERROR',						// http response 403 error, forbbiden
        'BD_HTTP_HEADER_404_ERROR',						// http response 404 error, page not found
        'BD_HTTP_HEADER_405_ERROR',						// http response 405 error, menthod not allowed
        'BD_HTTP_HEADER_406_ERROR',						// http response 406 error, not accepted
        'BD_HTTP_HEADER_407_ERROR',						// http response 407 error, proxy authentication required
        'BD_HTTP_HEADER_408_ERROR',						// http response 408 error, proxy authentication required
        'BD_HTTP_HEADER_409_ERROR',						// http response 409 error, conflict
        'BD_HTTP_HEADER_410_ERROR',						// http response 410 error, gone
        'BD_HTTP_HEADER_411_ERROR',						// http response 411 error, without length in request header
        'BD_HTTP_HEADER_412_ERROR',						// http response 412 error, precondition failed
        'BD_HTTP_HEADER_413_ERROR',						// http response 413 error, request entity too large
        'BD_HTTP_HEADER_414_ERROR',						// http response 414 error, request-URI too long
        'BD_HTTP_HEADER_415_ERROR',						// http response 415 error, unsupported media type
        'BD_HTTP_HEADER_416_ERROR',						// http response 416 error, request range not satisfialbe
        'BD_HTTP_HEADER_417_ERROR',						// http response 417 error, expectation failed

        // http 5xx error
        'BD_HTTP_HEADER_500_ERROR',						// http response 500 error, server internal error
        'BD_HTTP_HEADER_501_ERROR',						// http response 501 error, not implemented
        'BD_HTTP_HEADER_502_ERROR',						// http response 502 error, bad gateway
        'BD_HTTP_HEADER_503_ERROR',						// http response 503 error, service unavailable
        'BD_HTTP_HEADER_504_ERROR',						// http response 504 error, gateway timeout
        'BD_HTTP_HEADER_505_ERROR',						// http response 504 error, http version not supporte
        'BD_USER_QUOTA_REACH_ERROR',					// user's space is not enough, please cleanup some data or contact with the administrator

        //ceph error
        'BD_CEPH_INIT_ERROR',								// BdCephController error: ceph init error
        'BD_CEPH_CREATE_IO_CTX_ERROR',					// BdCephController error: ceph create io ctx error
        'BD_CEPH_CREATE_OBJ_SNAPSHOT_ERROR',				// BdCephController error: ceph create object snapshot error
        'BD_CEPH_DELETE_IO_CTX_ERROR',					// BdCephController error: ceph delete io ctx error
        'BD_CEPH_OPEN_RBD_OBJ_ERROR',						// BdCephController error: ceph open rbd object error
        'BD_CEPH_SHUTDOWN_CONNECT_ERROR',					// BdCephController error: ceph shutdown connect error
        'BD_CEPH_CLOSE_RBD_OBJ_ERROR',					// BdCephController error: ceph close rbd object error
        'BD_CEPH_GET_RBD_OBJ_SIZE_ERROR',					// BdCephController error: ceph get rbd object error
        'BD_CEPH_READ_RBD_DATA_ERROR',					// BdCephController error: ceph read rbd data error
        'BD_CEPH_RBD_DIFF_ITERATOR_ERROR',				// BdCephController error: ceph rbd diff iterator error
        'BD_CEPH_WRITE_RBD_DATA_ERROR',					// BdCephController error: ceph write rbd data error
        'BD_CEPH_DELETE_OBJ_SNAPSHOT_ERROR',				// BdCephController error: ceph delete object snapshot error
        'BD_CEPH_CONTROLLER_ALREADY_EXIST_ERROR',			// BdCephController error: ceph controller allready exist
        'BD_CEPH_CONTROLLER_NOT_EXIST_ERROR',				// BdCephController error: ceph controller not exist
        'BD_CEPH_CONTROLLER_NOT_FOUND_ERROR',				// BdCephController error: ceph controller not found

        //----------------------------for disk valid data------------------------------------------------
        'BD_INVALID_DISK_SIZE_ERROR',                     //the disk size can't be aliqouted by setor size(512)
        'BD_INVALID_MBR_ERROR',							//invalid MBR, the MBR's ending flag isn't "0x55AA" 
        'BD_MULTI_FILE_RECORD_ERROR',                     //the file include more than one file record, can't parse the file 
        'BD_NO_NTFS_FILE_SYSTEM_PARTITION',               //the disk dosen't has any NTFS file system partition,can't parse the disk
        'BD_NOT_INDEX_BY_FILE_NAME_ERROR',                //the file don't index by the file name
        'BD_NONRESIDENT_90H_ATTRIBUTE_ERROR',             //the 90H attribute is nonresident,can't parse.

        'BD_NBD_RECV_INVALID_IN_INIT_TLS_ERROR', 			//unexception recv in start nbd TLS connection
        'BD_NBD_RECV_INVALID_IN_HANDSHAKE_ERROR', 		//unexception recv in handshake procedure
        'BD_NBD_RECV_INVALID_REPLY_HEADER_ERROR', 		//recv unexception reply header error
        'BD_NBD_RECV_INVALID_HANDLE_ERROR', 				//recv unexception handle error
        'BD_NBD_OPERATION_ERROR', 						//nbd server return error code, operation failed

        'BD_TRANSPORT_MODE_ERROR', 							//transport mode error
        'BD_STORAGE_CONNECTOR_NOT_SUPPORT_OP_ERROR', 			//storage connector not support the current operation
        'BD_SNAPSHOT_NOT_SUPPORT_OPEN_FOR_WRITE_ERROR', 		//can't open snapshot with write flag
        'BD_CEPH_GET_RBD_OBJ_SNAPSHOT_LIST_ERROR', 			//get ceph rbd obj snapshot list error

        //2018.8.10 new error_log
        'BD_HTTP_RESULT_EMPTY',						//"http request result is empty" 
        'BD_HTTP_RESPONSE_HEADER_INVALID_ERROR', 	//"http response header content invalid" 
        'BD_JSON_STRING_INVALID',     				//"json string invalid"
        'BD_NOT_LOGIN_ERROR', 						//"not login error"

        'BD_ERRNO_EPERM', 							//"Operation not permitted" 
        'BD_ERRNO_ENOENT', 							//"No such file or directory" 
        'BD_ERRNO_ESRCH', 							//"No such process"
        'BD_ERRNO_EINTR', 							//"Interrupted system call"
        'BD_ERRNO_EIO', 							//"I/O error" 
        'BD_ERRNO_ENXIO', 							//"No such device or address"
        'BD_ERRNO_E2BIG', 							//"Arg list too long"
        'BD_ERRNO_ENOEXEC', 						//"Exec format error" 
        'BD_ERRNO_EBADF', 							//"Bad file number"
        'BD_ERRNO_ECHILD', 							//"No child processes"
        'BD_ERRNO_EAGAIN', 							//"Try again"
        'BD_ERRNO_ENOMEM', 							//"Out of memory"
        'BD_ERRNO_EACCES', 							//"Permission denied"
        'BD_ERRNO_EFAULT', 							//"Bad address"
        'BD_ERRNO_ENOTBLK', 						//"Block device required"
        'BD_ERRNO_EBUSY', 							//"Device or resource busy"
        'BD_ERRNO_EEXIST', 							//"File exists"
        'BD_ERRNO_EXDEV', 							//"Cross-device link" 
        'BD_ERRNO_ENODEV', 							//"No such device"
        'BD_ERRNO_ENOTDIR', 						//"Not a directory" 
        'BD_ERRNO_EISDIR', 							//"Is a directory"
        'BD_ERRNO_EINVAL', 							//"Invalid argument"
        'BD_ERRNO_ENFILE', 							//"File table overflow" 
        'BD_ERRNO_EMFILE', 							//"Too many open files"
        'BD_ERRNO_ENOTTY', 							//"Not a typewriter"
        'BD_ERRNO_ETXTBSY', 						//"Text file busy"
        'BD_ERRNO_EFBIG', 							//"File too large"
        'BD_ERRNO_ENOSPC', 							//"No space left on device"
        'BD_ERRNO_ESPIPE', 							//"Illegal seek"
        'BD_ERRNO_EROFS', 							//"Read-only file system" 
        'BD_ERRNO_EMLINK', 							//"Too many links"
        'BD_ERRNO_EPIPE', 							//"Broken pipe"
        'BD_ERRNO_EDOM', 							//"Math argument out of domain of func"
        'BD_ERRNO_ERANGE', 							//"Math result not representable"
        'BD_ERRNO_EDEADLK', 						//"Resource deadlock would occur"
        'BD_ERRNO_ENAMETOOLONG', 					//"File name too long" 
        'BD_ERRNO_ENOLCK', 							//"No record locks available"  
        'BD_ERRNO_ENOSYS', 							//"Function not implemented"
        'BD_ERRNO_ENOTEMPTY', 						//"Directory not empty"
        'BD_ERRNO_ELOOP', 							//"Too many symbolic links encountered"
        'BD_ERRNO_EWOULDBLOCK', 					//"Operation would block"
        'BD_ERRNO_ENOMSG', 							//"No message of desired type"
        'BD_ERRNO_EIDRM', 							//"Identifier removed"
        'BD_ERRNO_ECHRNG', 							//"Channel number out of range" 
        'BD_ERRNO_EL2NSYNC', 						//"Level 2 not synchronized" 
        'BD_ERRNO_EL3HLT', 							//"Level 3 halted" 
        'BD_ERRNO_EL3RST', 							//"Level 3 reset"
        'BD_ERRNO_ELNRNG', 							//"Link number out of range"
        'BD_ERRNO_EUNATCH', 						//"Protocol driver not attached"
        'BD_ERRNO_ENOCSI', 							//"No CSI structure available"
        'BD_ERRNO_EL2HLT', 							//"Level 2 halted"
        'BD_ERRNO_EBADE', 							//"Invalid exchange"
        'BD_ERRNO_EBADR', 							//"Invalid request descriptor"
        'BD_ERRNO_EXFULL', 							//"Exchange full"
        'BD_ERRNO_ENOANO', 							//"No anode"
        'BD_ERRNO_EBADRQC', 						//"Invalid request code" 
        'BD_ERRNO_EBADSLT', 						//"Invalid slot"
        'BD_ERRNO_EDEADLOCK', 						//"DEADLK Resource deadlock would occur" 
        'BD_ERRNO_EBFONT', 							//"Bad font file format" 
        'BD_ERRNO_ENOSTR', 							//"Device not a stream"
        'BD_ERRNO_ENODATA', 						//"No data available"
        'BD_ERRNO_ETIME', 							//"Timer expired" 
        'BD_ERRNO_ENOSR', 							//"Out of streams resources"
        'BD_ERRNO_ENONET', 							//"Machine is not on the network"
        'BD_ERRNO_ENOPKG', 							//"Package not installed"
        'BD_ERRNO_EREMOTE', 						//"Object is remote"
        'BD_ERRNO_ENOLINK', 						//"Link has been severed"
        'BD_ERRNO_EADV', 							//"Advertise error"
        'BD_ERRNO_ESRMNT', 							//"Srmount error"
        'BD_ERRNO_ECOMM', 							//"Communication error on send"
        'BD_ERRNO_EPROTO', 							//"Protocol error" 
        'BD_ERRNO_EMULTIHOP', 						//"Multihop attempted"
        'BD_ERRNO_EDOTDOT', 						//"RFS specific error" 
        'BD_ERRNO_EBADMSG', 						//"Not a data message"
        'BD_ERRNO_EOVERFLOW', 						//"Value too large for defined data type"
        'BD_ERRNO_ENOTUNIQ', 						//"Name not unique on network" 
        'BD_ERRNO_EBADFD', 							//"File descriptor in bad state"
        'BD_ERRNO_EREMCHG', 						//"Remote address changed"
        'BD_ERRNO_ELIBACC', 						//"Can not access a needed shared library"
        'BD_ERRNO_ELIBBAD', 						//"Accessing a corrupted shared library"
        'BD_ERRNO_ELIBSCN', 						//".lib section in a.out corrupted" 
        'BD_ERRNO_ELIBMAX', 						//"Attempting to link in too many shared libraries"
        'BD_ERRNO_ELIBEXEC', 						//"Cannot exec a shared library directly"
        'BD_ERRNO_EILSEQ', 							//"Illegal byte sequence"
        'BD_ERRNO_ERESTART', 						//"Interrupted system call should be restarted"
        'BD_ERRNO_ESTRPIPE', 						//"Streams pipe error"
        'BD_ERRNO_EUSERS', 							//"Too many users"
        'BD_ERRNO_ENOTSOCK', 						//"Socket operation on non-socket"
        'BD_ERRNO_EDESTADDRREQ', 					//"Destination address required"
        'BD_ERRNO_EMSGSIZE', 						//"Message too long"
        'BD_ERRNO_EPROTOTYPE', 						//"Protocol wrong type for socket"
        'BD_ERRNO_ENOPROTOOPT', 					//"Protocol not available"
        'BD_ERRNO_EPROTONOSUPPORT', 				//"Protocol not supported" 
        'BD_ERRNO_ESOCKTNOSUPPORT', 				//"Socket type not supported"
        'BD_ERRNO_EOPNOTSUPP', 						//"Operation not supported on transport endpoint" 
        'BD_ERRNO_EPFNOSUPPORT', 					//"Protocol family not supported"
        'BD_ERRNO_EAFNOSUPPORT', 					//"Address family not supported by protocol"
        'BD_ERRNO_EADDRINUSE', 						//"Address already in use"
        'BD_ERRNO_EADDRNOTAVAIL', 					//"Cannot assign requested address"
        'BD_ERRNO_ENETDOWN', 						//"Network is down"
        'BD_ERRNO_ENETUNREACH', 					//"Network is unreachable"
        'BD_ERRNO_ENETRESET', 						//"Network dropped connection because of reset" 
        'BD_ERRNO_ECONNABORTED', 					//"Software caused connection abort"
        'BD_ERRNO_ECONNRESET', 						//"Connection reset by peer"
        'BD_ERRNO_ENOBUFS', 						//"No buffer space available"
        'BD_ERRNO_EISCONN', 						//"Transport endpoint is already connected" 
        'BD_ERRNO_ENOTCONN', 						//"Transport endpoint is not connected"
        'BD_ERRNO_ESHUTDOWN', 						//"Cannot send after transport endpoint shutdown" 
        'BD_ERRNO_ETOOMANYREFS', 					//"Too many references: cannot splice"
        'BD_ERRNO_ETIMEDOUT', 						//"Connection timed out" 
        'BD_ERRNO_ECONNREFUSED', 					//"Connection refused" 
        'BD_ERRNO_EHOSTDOWN', 						//"Host is down"
        'BD_ERRNO_EHOSTUNREACH', 					//"No route to host"
        'BD_ERRNO_EALREADY', 						//"Operation already in progress"
        'BD_ERRNO_EINPROGRESS', 					//"Operation now in progress"
        'BD_ERRNO_ESTALE', 							//"Stale NFS file handle" 
        'BD_ERRNO_EUCLEAN', 						//"Structure needs cleaning" 
        'BD_ERRNO_ENOTNAM', 						//"Not a XENIX named type file" 
        'BD_ERRNO_ENAVAIL', 						//"No XENIX semaphores available"
        'BD_ERRNO_EISNAM', 							//"Is a named type file"
        'BD_ERRNO_EREMOTEIO',               		//"Remote I/O error" 
        'BD_ERRNO_EDQUOT', 							//"Quota exceeded" 
        'BD_ERRNO_ENOMEDIUM',	 					//"No medium found" 
        'BD_ERRNO_EMEDIUMTYPE', 					//"Wrong medium type"
        'BD_USERNAME_NOT_INCLUDE_DOMAIN_NAME_ERROR',
        'BD_FILE_MD5_NOT_MATCH',
        'BD_PATCH_FILE_IS_NOT_EXIST',
        // sqlite error
        'BD_SQLITE_UNKNOWN_ERROR',						/* sqlite error */
        'BD_SQLITE_ERROR',								/* SQL error or missing database */
        'BD_SQLITE_INTERNAL',							/* Internal logic error in SQLite */
        'BD_SQLITE_PERM',								/* Access permission denied */
        'BD_SQLITE_ABORT',								/* Callback routine requested an abort */
        'BD_SQLITE_BUSY',								/* The database file is locked */
        'BD_SQLITE_LOCKED',								/* A table in the database is locked */
        'BD_SQLITE_NOMEM',								/* A malloc() failed */
        'BD_SQLITE_READONLY',							/* Attempt to write a readonly database */
        'BD_SQLITE_INTERRUPT',							/* Operation terminated by sqlite3_interrupt()*/
        'BD_SQLITE_IOERR',								/* Some kind of disk I/O error occurred */
        'BD_SQLITE_CORRUPT',							/* The database disk image is malformed */
        'BD_SQLITE_NOTFOUND',							/* Unknown opcode in sqlite3_file_control() */
        'BD_SQLITE_FULL',								/* Insertion failed because database is full */
        'BD_SQLITE_CANTOPEN',							/* Unable to open the database file */
        'BD_SQLITE_PROTOCOL',							/* Database lock protocol error */
        'BD_SQLITE_EMPTY',								/* Database is empty */
        'BD_SQLITE_SCHEMA',								/* The database schema changed */
        'BD_SQLITE_TOOBIG',								/* String or BLOB exceeds size limit */
        'BD_SQLITE_CONSTRAINT',							/* Abort due to constraint violation */
        'BD_SQLITE_MISMATCH',							/* Data type mismatch */
        'BD_SQLITE_MISUSE',								/* Library used incorrectly */
        'BD_SQLITE_NOLFS',								/* Uses OS features not supported on host */
        'BD_SQLITE_AUTH',								/* Authorization denied */
        'BD_SQLITE_FORMAT',								/* Auxiliary database format error */
        'BD_SQLITE_RANGE',								/* 2nd parameter to sqlite3_bind out of range */
        'BD_SQLITE_NOTADB',								/* File opened that is not a database file */
        'BD_SQLITE_NOTICE',								/* Notifications from sqlite3_log() */
        'BD_SQLITE_WARNING',							/* Warnings from sqlite3_log() */

        'BD_SQLITE_OBJECT_NOT_EXIST',					/* Can't find the target object with key */

        'BD_SOURCE_BITMAP_SIZE_NOT_COMPITBLE_TARGET_SIZE', /* source/target bitmap size is not divided \
with no remainder by target/source bitmap size*/
        'BD_PATCH_ALREADY_INSTALLED_ERROR',				/* patch already installed in the node */
        'BD_DOWNLOAD_ERROR',								/* download file error */

        'BD_GUEST_UNSUPPORT_ERROR',						/* unsupported guest operating system */
        'BD_GUEST_FILESYSTEM_ROOT_NOT_FOUND_ERROR',		/* root disk not found error */
        'BD_GUEST_VM_CANT_GET_OPERATING_SYSTEM_ERROR',	/* get operating system error */
        'BD_GUEST_VM_OPERATING_SYSTEM_UNKNOWN_ERROR',		/* operating system unknown error */
        'BD_GUEST_WINDOWS_LETTER_MAP_NOT_FOUND_ERROR',	/* guest windows letter map not found error */
        'BD_GUEST_WINDOWS_LETTER_MAP_NOT_PART_MISSING',	/* guest windows letter map device path missing */
        'BD_GUEST_FILESYSTEM_INFO_MISSING_ERROR',			/* guest filesystem info missing error */
        'BD_GUEST_WORK_THREAD_EXIST_ERROR',				/* guest work thread exist error */
        'BD_GUEST_WORK_THREAD_NOT_EXIST_ERROR',			/* guest work thread is not exist error */
        'BD_GUEST_HANDLER_NOT_EXIST_ERROR',				/* guest handler is not exist error */
        'BD_GUEST_HANDLER_EXIST_ERROR',					/* guest handler is already exist error */
        'BD_GUEST_HANDLER_BUSY_ERROR',					/* guest handler is busy, some thread is still holding it */
        'BD_GUEST_HANDLER_IS_DELETING_ERROR',				/* guest handler is deleting, don't try to active the delete operation */
        'BD_GUEST_FSTAB_MISSING_ERROR',					/* guest fstab info missing error */
        'BD_GUEST_MOUNTPOINT_INFO_MISSING_ERROR',			/* guest mountpoint info missing error */
        'BD_GUEST_INTERNAL_MOUNT_ERROR',					/* guest mount filesystem internal error */
        'BD_GUEST_WINDOWS_DRIVER_LETTER_NOT_EXIST_ERROR',	/* the windows driver letter is not exist */
        'BD_GUEST_DRIVER_IS_BUSY_ERROR',					/* driver is busy, please try operation again after a couple of time */
        'BD_GUEST_LIST_DIR_ERROR',						/* error in list guest dir */
        'BD_GUEST_LIST_RESULT_NOT_MATCH',					/* list result not match the list stat result */
        'BD_GUEST_GET_ITEM_STAT_ERROR',					/* can't get stat info of file item */
        'BD_GUEST_ROOT_IS_NOT_MOUNT_ERROR',				/* the root not mounted error */

        'BD_TASK_IS_STOPPING_ERROR',						/* task is stopping error */
        'BD_TASK_IS_STOPPED_ERROR',						/* task is already stoped */

        // new add guest error code
        'BD_GUEST_PATH_INVALID_ERROR',					/* guest path is invalid */
        'BD_TASK_BE_PAUSED_ERROR',						/* task is paused*/
        'BD_GUEST_READ_BEYOND_THE_FILE_ERROR',			/* read request is beyond the file limit */

        // file handle pool
        'BD_FILE_NOT_OPEN_ERROR',						/* file not open, can't find in the handle pool */
        'BD_FILE_HANDLE_POOL_REACH_MAX_NUM_ERROR',		/* file handle pool is reach the max */

        // task work thread 
        'BD_TASK_WORK_THREAD_OTHER_THREAD_ERROR',		/* other thread abnormal, thread stopped */

        //for netapp
        'BD_NETAPP_OPEN_CONNECTION_ERROR',				/* open netapp connection error */
        'BD_NETAPP_SET_SERVER_TYPE_ERROR',				/* set netapp server type error */
        'BD_NETAPP_SET_TRANSPORT_TYPE_ERROR',				/* set netapp transport type error */
        'BD_NETAPP_SET_PORT_ERROR',						/* set netapp port error */
        'BD_NETAPP_SET_USERNAME_AND_PASSWORD_ERROR',		/* set netapp username and password error */
        'BD_NETAPP_GET_API_VERSION_ERROR',				/* get api version error */
        'BD_NETAPP_GET_LUN_ITER_ERROR',					/* get lun iteration error */
        'BD_NETAPP_GET_VOLUME_LUN_PATH_ERROR',			/* get volume lun path error */
        'BD_NETAPP_LUN_MAP_ERROR',						/* netapp lun map error */
        'BD_NETAPP_LUN_UNMAP_ERROR',						/* netapp lun unmap error */
        'BD_NETAPP_GET_IGROUP_ITER_ERROR',				/* get netapp igroup iter error */
        'BD_NETAPP_GET_LUN_MAP_INFO_ERROR',				/* get netapp lun map info error */
        'BD_NETAPP_SET_VSERVER_ERROR',					/* set vserver error */

        // multiple thread 
        'BD_TASK_INFO_CHANGE_ERROR',						/* task configuration change from last job */

        //for progress
        'BD_PROGRESS_IS_NOT_EXIST_ERROR',					/* progress is not exist error */
        'BD_PROGRESS_IS_ALREADY_EXIST_ERROR',				/* progress is already exist error */
        'BD_PROGRESS_START_PROGRESS_ERROR',				/* start progress error */
        'BD_PROGRESS_HAS_NO_UNUSED_PORT_ERROR',			/* has no unused port to start progress error */
        'BD_PROGRESS_GET_ALL_USED_PORTS_ERROR',			/* get all used port list error */
        'BD_DO_SYSTEM_CMD_ERROR',							/* do system command error */

        'BD_APPLIANCE_IS_NOT_EXIST_ERROR',				/* appliance is not exist error */

        /* new version grain recovery */
        'BD_GUEST_DEVICE_NOT_EXIST_ERROR',				/* device is not exist in guest */

        //for libssh
        'BD_LIBSSH2_GENERIC_ERROR',                     //Ssh2 generic error
        'BD_LIBSSH2_SESSION_INIT_ERROR',                //Ssh2 initialize session error


        'BD_LIBSSH2_SOCKET_NONE_ERROR',                 //Ssh2 socket is none error
        'BD_LIBSSH2_BANNER_RECV_ERROR',                 //Ssh2 receive banner error
        'BD_LIBSSH2_BANNER_SEND_ERROR',                 //Ssh2 send banner error
        'BD_LIBSSH2_INVALID_MAC_ERROR',                 //Ssh2 invalid mac error
        'BD_LIBSSH2_KEX_FAILURE_ERROR',                 //Ssh2 kex failure error
        'BD_LIBSSH2_ALLOC_ERROR',                       //Ssh2 allocate error
        'BD_LIBSSH2_SOCKET_SEND_ERROR',                 //Ssh2 socket send error
        'BD_LIBSSH2_KEY_EXCHANGE_FAILURE_ERROR',        //Ssh2 key exchange error
        'BD_LIBSSH2_TIMEOUT_ERROR',                     //Ssh2 timeout error
        'BD_LIBSSH2_HOSTKEY_INIT_ERROR',                //Ssh2 host key initialize error
        'BD_LIBSSH2_HOSTKEY_SIGN_ERROR',                //Ssh2 host key sign error
        'BD_LIBSSH2_DECRYPT_ERROR',                     //Ssh2 decrypt error
        'BD_LIBSSH2_SOCKET_DISCONNECT_ERROR',           //Ssh2 socket disconnect error
        'BD_LIBSSH2_PROTO_ERROR',                       //Ssh2 proto error
        'BD_LIBSSH2_PASSWORD_EXPIRED_ERROR',            //Ssh2 passowrd expired error
        'BD_LIBSSH2_FILE_ERROR',                        //Ssh2 file error
        'BD_LIBSSH2_METHOD_NONE_ERROR',                 //Ssh2 method node error
        'BD_LIBSSH2_AUTHENTICATION_FAILED_ERROR',       //Ssh2 authentication error
        'BD_LIBSSH2_PUBLICKEY_UNRECOGNIZED_ERROR',      //Ssh2 public key unrecognized error
        'BD_LIBSSH2_PUBLICKEY_UNVERIFIED_ERROR',        //Ssh2 public key unverified error
        'BD_LIBSSH2_CHANNEL_OUTOFORDER_ERROR',          //Ssh2 channel out of order error
        'BD_LIBSSH2_CHANNEL_FAILURE_ERROR',             //Ssh2 channel error
        'BD_LIBSSH2_CHANNEL_REQUEST_DENIED_ERROR',      //Ssh2 channel request denied error
        'BD_LIBSSH2_CHANNEL_UNKNOWN_ERROR',             //Ssh2 channel unknown error
        'BD_LIBSSH2_CHANNEL_WINDOW_EXCEEDED_ERROR',     //Ssh2 channel window exceeded error
        'BD_LIBSSH2_CHANNEL_PACKET_EXCEEDED_ERROR',     //Ssh2 channel packet exceeded error
        'BD_LIBSSH2_CHANNEL_CLOSED_ERROR',              //Ssh2 channel closed error
        'BD_LIBSSH2_CHANNEL_EOF_SENT_ERROR',            //Ssh2 channel eof sent error
        'BD_LIBSSH2_SCP_PROTOCOL_ERROR',                //Ssh2 scp protocal error
        'BD_LIBSSH2_ZLIB_ERROR',                        //Ssh2 zlib unknown error
        'BD_LIBSSH2_SOCKET_TIMEOUT_ERROR',              //Ssh2 socket timeout error
        'BD_LIBSSH2_SFTP_PROTOCOL_ERROR',               //Ssh2 sftp protocal error
        'BD_LIBSSH2_REQUEST_DENIED_ERROR',              //Ssh2 request denied error
        'BD_LIBSSH2_METHOD_NOT_SUPPORTED_ERROR',        //Ssh2 method not supported error
        'BD_LIBSSH2_INVAL_ERROR',                       //Ssh2  inval error
        'BD_LIBSSH2_INVALID_POLL_TYPE_ERROR',           //Ssh2 invalid poll type error
        'BD_LIBSSH2_PUBLICKEY_PROTOCOL_ERROR',          //Ssh2 public key protocol error
        'BD_LIBSSH2_EAGAIN_ERROR',                      //Ssh2 error again error
        'BD_LIBSSH2_BUFFER_TOO_SMALL_ERROR',            //Ssh2 buffer too small error
        'BD_LIBSSH2_BAD_USE_ERROR',                     //Ssh2 bad use error
        'BD_LIBSSH2_COMPRESS_ERROR',                    //Ssh2 compress error
        'BD_LIBSSH2_OUT_OF_BOUNDARY_ERROR',             //Ssh2 out of boundary error
        'BD_LIBSSH2_AGENT_PROTOCOL_ERROR',              //Ssh2 agent protocol error
        'BD_LIBSSH2_SOCKET_RECV_ERROR',                 //Ssh2 socket recv error
        'BD_LIBSSH2_ENCRYPT_ERROR',                     //Ssh2 encrypt error
        'BD_LIBSSH2_BAD_SOCKET_ERROR',                  //Ssh2 bad socket error
        'BD_LIBSSH2_KNOWN_HOSTS_ERROR',                 //Ssh2 known hosts error

        'BD_JSON_MEMBER_MISSING_ERROR',                 //json member missing error
        'BD_PROMISE_LOGIN_ERROR',                       //promise login error
        'BD_CHECK_MOUNTPOINT_TIMEOUT_ERROR',            //check mountpoint timeout error


        ///// 6.0 new error code /////
        'BD_SINGLE_DISK_NOT_SUPPORT_OP_ERROR',          //single disk object not support operation error
        'BD_REMOTE_FILE_ALREADY_OPEN_ERROR',            //remote file already open error
        'BD_REMOTE_FILE_NOT_OPEN_ERROR',                //remote file is not open error"
        'BD_REMOTE_FILE_HANDLE_INVALID_ERROR',	        //remote file handle is invalid error
        'BD_LICENSE_CURRENT_VM_IS_MORE_THAN_LIC_FILE',	//current backup vm number is more than the upload the license file, please delete backup vm task and retry upload
        'BD_STORAGE_IS_USING_BY_TASK_ERROR',	//storage is using by task error
        'BD_BACKUP_NODE_IS_EXIST_ERROR',	   //backup node is exist error

        ////// curl download/upload error code //////////
        'BD_CURL_TRANSFER_BUFFER_IS_NOT_ENOUGH_ERROR',	/* curl download/upload buffer is not enough error */
        'BD_IP_SEGMENT_FORMAT_IS_INVALID_ERROR',			/* ip segment format is invalid */
        'BD_INC_AND_DIFF_TIMEPOINT_MIX_IN_ONE_CHAINS',    /* exist backup chains which contains incremental timepoint and different timepoint at the same time*/

        'BD_GFS_ALREADY_EXIST_MARKED_FULL_BACKUP_TIMEPOINT_AT_THE_SAME_WEEK',		/* unable to mark the timepoint, already exist gfs timepoint at the same week*/
        'BD_GFS_ALREADY_EXIST_MARKED_FULL_BACKUP_TIMEPOINT_AT_THE_SAME_MONTH',	/* unable to mark the timepoint, already exist gfs timepoint at the same month*/
        'BD_GFS_ALREADY_EXIST_MARKED_FULL_BACKUP_TIMEPOINT_AT_THE_SAME_YEAR',		/* unable to mark the timepoint, already exist gfs timepoint at the same year*/

        'BD_VERSION_ERROR', 							//version error, maybe different between offsite and onsite system

        'BD_GET_IPSAN_FILE_LOCK_TIMEOUT',			//unmap lun or map lun, get ipsan file lock timeout
        'BD_UNLOCK_IPSAN_FILE_LOCK_ERROR',			//unmap lun or map lun, unlock ipsan file lock error

        'BD_ISCSI_DISCOVERY_TARGET_ERROR',                    //iscsi扫描存储目标失败
        'BD_ISCSI_LOGIN_TARGET_ERROR',                            //iscsi登录存储目标失败
        'BD_ISCSI_LOGOUT_TARGET_ERROR',                         //iscsi注销存储目标失败
        'BD_ISCSI_MOUNT_LUN_ERROR',                               //iscsi挂载存储单元失败
        'BD_ISCSI_UNMOUNT_LUN_ERROR',                          //iscsi解挂存储单元失败
        'BD_ISCSI_FIND_LUN_ERROR',                                    //iscsi查找存储单元失败
        'BD_ISCSI_CREATE_TARGET_ERROR',                           //iscsi创建存储目标失败
        'BD_ISCSI_DELETE_TARGET_ERROR',                            //iscsi删除存储目标失败
        'BD_ISCSI_TARGET_ADD_LUN_ERROR',                        //iscsi向存储目标添加存储单元失败
        'BD_ISCSI_TARGET_BIND_INITIATOR_IP_ERROR',         //iscsi存储目标绑定客户端IP失败
        'BD_ISCSI_TARGET_UNBIND_INITIATOR_IP_ERROR',    //iscsi存储目标解绑客户端IP失败

        'BD_AGENT_APP_NOT_EXIST_ERROR',   	//agent app not exist
        'BD_AGENT_DISK_NOT_EXIST_ERROR',   //agent disk not exist
        'BD_AGENT_VOL_NOT_EXIST_ERROR',   //agent vol not exist
        'BD_STORAGE_ENCRYPT_CHNAGED_ERROR',  //storage encrypt changed error
        'BD_NODE_NETWORK_NOT_EXIST_ERROR',  //node network not exist

        'BD_NOT_MASTER_NODE_ERROR',				//is not master node, used for backup copy add remote system
        'BD_TIMEPOINT_NOT_COPY_OR_ARCHIVE_ERROR',	//timepoint is not copied or archived by backup copy task or archive task
        'BD_DANGEROUS_COMMAND_ERROR',    //dangerous command
        'BD_AGENT_ALREADY_EXIST_ERROR',    //agent already exist error
        'BD_AGENT_CUR_LSN_SMALLER_THAN_BACKUPSET_END_LSN_ERROR', //the current lsn is smaller than the backupset lsn
        'BD_STORAGE_USE_MODE_NOT_MATCH_ERROR', 		//storage use mode not match error 
        'BD_AGENT_APP_IN_USE_BY_TASK_ERROR',  //agent app in use by task
        'BD_DYNAMIC_PARTITION_EXISTS',   				//dynamic partition exists

        //XFS valid data error code
        'BD_INVALID_FS_TYPE',     				//invalid fs type
        'BD_NTFS_INVALID_RECORD_HEAD_ERROR',  	//invalid ntfs record head
        'BD_XFS_SB_MAGIC_ERROR',    			//get sb magic error
        'BD_XFS_AGF_MAGIC_ERROR',     			//get agf magic error
        'BD_XFS_AGFL_MAGIC_ERROR',    			//get agfl magic error
        'BD_XFS_MAKE_FS_IS_IN_PROGRESSING',  	//make fs in proressing error
        'BD_XFS_ALLOC_SPACE_TOO_SMALL',   		//alloc space too small error
        'BD_XFS_UNSUPPORTED_VERSION',          //unsupported xfs version
        //volume cbt operation error code
        'BD_VOL_CBT_CONTROL_DEVICE_OPEN_ERROR',
        'BD_VOL_CBT_DEVICE_NOT_MONITORING',
        'BD_VOL_CBT_CHECK_PARAM_INVALID',
        'BD_VOL_CBT_OPEN_BITMAP_FILE_ERROR',
        'BD_VOL_CBT_WRITE_BITMAP_FILE_ERROR',

        //Reg error code
        'BD_REG_KEY_NOT_FOUND',					//reg key not fund 
        'BD_REG_SPACE_NOT_ENOUGH',				//Insufficient buffer space

        'BD_USER_NAME_AND_PASSWD_NOT_MATCH',



        'BD_S3_CLIENT_MISSING_ACCESS_KEY_ID',
        'BD_S3_CLIENT_MISSING_ACCESS_SECRET_KEY',
        'BD_S3_CLIENT_UNSURPORTED_PLATFORM',
        'BD_S3_CLIENT_NULL_AGENT',

        'BD_S3_PARAM_MISSING_OBJECT_KEY',
        'BD_S3_PARAM_MISSING_BUCKET_NAME',
        'BD_S3_PARAM_MISSING_UPLOAD_ID',
        'BD_S3_PARAM_INVALID_PART_NUMBER',
        'BD_S3_PARAM_INVALID_BUFFER_SIZE',
        'BD_S3_PARAM_NULL_POINTER',
        'BD_S3_IN_BUFFER_OUT_OF_MEMORY',
        'BD_S3_OUT_BUFFER_OUT_OF_MEMORY',

        'BD_S3_UTILS_GET_GMT_TIME_ERROR',
        'BD_S3_UTILS_AUTH_INVALID_CANONICAL_STRING',

        'BD_S3_UNKNOWN_ACTION',

        'BD_S3_RESPONSE_PARSE_ERROR',
        'BD_S3_RESPONSE_INCOMPLETE_SIGNATURE',
        'BD_S3_RESPONSE_INTERNAL_FAILURE',
        'BD_S3_RESPONSE_INVALID_ACTION',
        'BD_S3_RESPONSE_INVALID_CLIENT_TOKEN_ID',
        'BD_S3_RESPONSE_INVALID_PARAMETER_COMBINATION',
        'BD_S3_RESPONSE_INVALID_QUERY_PARAMETER',
        'BD_S3_RESPONSE_INVALID_PARAMETER_VALUE',
        'BD_S3_RESPONSE_MISSING_ACTION',
        'BD_S3_RESPONSE_MISSING_AUTHENTICATION_TOKEN',
        'BD_S3_RESPONSE_MISSING_PARAMETER',
        'BD_S3_RESPONSE_OPT_IN_REQUIRED',
        'BD_S3_RESPONSE_REQUEST_EXPIRED',
        'BD_S3_RESPONSE_SERVICE_UNAVAILABLE',
        'BD_S3_RESPONSE_THROTTLING',
        'BD_S3_RESPONSE_VALIDATION',
        'BD_S3_RESPONSE_ACCESS_DENIED',
        'BD_S3_RESPONSE_RESOURCE_NOT_FOUND',
        'BD_S3_RESPONSE_UNRECOGNIZED_CLIENT',
        'BD_S3_RESPONSE_MALFORMED_QUERY_STRING',
        'BD_S3_RESPONSE_SLOW_DOWN',
        'BD_S3_RESPONSE_REQUEST_TIME_TOO_SKEWED',
        'BD_S3_RESPONSE_INVALID_SIGNATURE',
        'BD_S3_RESPONSE_SIGNATURE_DOES_NOT_MATCH',
        'BD_S3_RESPONSE_INVALID_ACCESS_KEY_ID',
        'BD_S3_RESPONSE_REQUEST_TIMEOUT',
        'BD_S3_RESPONSE_NETWORK_CONNECTION',
        'BD_S3_RESPONSE_UNKNOWN',
        'BD_S3_RESPONSE_CLIENT_SIGNING_FAILURE',
        'BD_S3_RESPONSE_USER_CANCELLED',
        'BD_S3_RESPONSE_ENDPOINT_RESOLUTION_FAILURE',
        'BD_S3_RESPONSE_SERVICE_EXTENSION_START_RANGE',
        'BD_S3_RESPONSE_OK',

        'BD_S3_UNKNOWN_ERROR',

        //check block_size zero error code
        'BD_TASK_BLOCK_SIZE_ZERO_ERROR',			//task block_size zero error

        'BD_UNSUPPORTED_FILESYSTEM_TYPE',

        //HAUWEI cbr
        'BD_HUAWEI_CBR_GET_REGION_ERROR',
        'BD_HUAWEI_CBR_GET_PROJECT_ERROR',
        'BD_HUAWEI_CBR_GET_VAULT_ERROR',
        'BD_HUAWEI_CBR_GET_BACKUP_ERROR',
        'BD_HUAWEI_CBR_SYNC_TIMEPOINT_TO_CLOUD_ERROR',
        'BD_HUAWEI_CBR_GET_BACKUP_METADATA_ERROR',
        'BD_HUAWEI_CBR_SYNC_GRAIN_INVAILD_ERROR',
        'BD_HUAWEI_CBR_ADD_BUCKET_AUTH_ERROR',
        'BD_HUAWEI_CBR_INIT_TRAFFIC_REPORT_ERROR',
        'BD_HUAWEI_CBR_DO_TRAFFIC_REPORT_ERROR',
        'BD_HUAWEI_CBR_GET_AK_SK_ERROR',
        'BD_HUAWEI_CBR_TASK_TIMEOUT_ERROR',
        'BD_HUAWEI_CBR_GET_TASK_ERROR',
        'BD_HUAWEI_CBR_GET_RESOURCE_ERROR',
        'BD_HUAWEI_CBR_BACKUP_POINT_SYNCHRONIZED_ALREADY_ERROR',


        'BD_SHM_READ_ERROR',
        'BD_SHM_UNMAP_ERROR',
        'BD_SHM_DES_ERROR',
        'BD_SHM_INIT_ERROR',
        'BD_SHM_MAP_ERROR',


        'BD_NAMEDSEM_TIMEWAIT_ERROR',
        'BD_NAMEDSEM_POST_ERROR',
        'BD_NAMEDSEM_CLOSE_ERROR',
        'BD_NAMEDSEM_UNLINK_ERROR',
        'BD_NAMEDSEM_OPEN_ERROR',
        'BD_COMPRESS_BUFFER_ERROR',
        'BD_COMPRESS_ALLOCATE_MEMORY_ERROR',
        'BD_OBS_FS_PASSWORD_FILE_BUILD_ERROR',
        'BD_OBS_FS_PASSWORD_FILE_ADD_RIGHTS_ERROR',
        'BD_OBS_FS_MOUNT_ERROR',
        'BD_STORAGE_COMPRESS_CHNAGED_ERROR',
        'BD_STORAGE_NOT_USE_SPECIAL_PATH',
        'BD_SYSTEM_CACHE_NOT_CONFIG',
        'BD_SYSTEM_CACHE_CONFIG_SPACE_IS_FULL',
        'BD_SYSTEM_CACHE_NO_AVAILABLE_CONFIG_SPACE',
        747 => 'BD_SYSTEM_CACHE_NO_AVAILABLE_SWITCHING_SPACE',
        'BD_TIMEPOINT_UNSUPPORT_DO_MERGE_ERROR',
        'BD_USER_LEVEL_WITHOUT_PERMISSION',
        750 => 'BD_STORAGE_TAPE_NOT_FOUND_AVAILABLE_DRIVE',
        'BD_STORAGE_LOGIN_ERROR',
        'BD_STORAGE_GET_LUN_INFO_ERROR',
        'BD_STORAGE_GET_SNAPSHOT_INFO_ERROR',
        'BD_STORAGE_CREATE_SNAPSHOT_ERROR',
        'BD_STORAGE_DELETE_SNAPSHOT_ERROR',
        'BD_STORAGE_ACTIVE_SNAPSHOT_ERROR',
        'BD_STORAGE_INACTIVE_SNAPSHOT_ERROR',
        'BD_STORAGE_GET_HOST_ERROR',
        'BD_STORAGE_GET_HOST_GROUP_ERROR',
        760 => 'BD_STORAGE_GET_LUN_GROUP_ERROR',
        'BD_STORAGE_GET_MAP_INFO_ERROR',
        'BD_STORAGE_CREATE_INITIATOR_ERROR',
        'BD_STORAGE_ADD_INITIATOR_TO_HOST_ERROR',
        'BD_STORAGE_REMOVE_INITIATOR_FROM_HOST_ERROR',
        'BD_STORAGE_GET_INITIATOR_INFO_ERROR',
        'BD_STORAGE_CREATE_HOST_ERROR',
        'BD_STORAGE_CREATE_LUN_GROUP_ERROR',
        'BD_STORAGE_CREATE_HOST_GROUP_ERROR',
        'BD_STORAGE_CREATE_MAP_ERROR',
        770 => 'BD_S3_ACCESS_CONTROL_LIST_NOT_SUPPORTED',
        'BD_S3_ACCESS_DENIED',
        'BD_S3_ACCESS_POINT_ALREADY_OWNED_BY_YOU',
        'BD_S3_ACCOUNT_PROBLEM',
        'BD_S3_ALL_ACCESS_DISABLED',
        'BD_S3_AMBIGUOUS_GRANT_BY_EMAIL_ADDRESS',
        'BD_S3_AUTHORIZATION_HEADER_MALFORMED',
        'BD_S3_BAD_DIGEST',
        'BD_S3_BUCKET_ALREADY_EXISTS',
        'BD_S3_BUCKET_ALREADY_OWNED_BY_YOU',
        780 => 'BD_S3_BUCKET_NOT_EMPTY',
        'BD_S3_CLIENT_TOKEN_CONFLICT',
        'BD_S3_CREDENTIALS_NOT_SUPPORTED',
        'BD_S3_CROSS_LOCATION_LOGGING_PROHIBITED',
        'BD_S3_ENTITY_TOO_SMALL',
        'BD_S3_ENTITY_TOO_LARGE',
        'BD_S3_EXPIRED_TOKEN',
        'BD_S3_ILLEGAL_LOCATION_CONSTRAINT_EXCEPTION',
        'BD_S3_ILLEGAL_VERSIONING_CONFIGURATION_EXCEPTION',
        'BD_S3_INCOMPLETE_BODY',
        790 => 'BD_S3_INCORRECT_NUMBER_OF_FILES_IN_POST_REQUEST',
        'BD_S3_INLINE_DATA_TOO_LARGE',
        'BD_S3_INTERNAL_ERROR',
        'BD_S3_INVALID_ACCESS_KEY_ID',
        'BD_S3_INVALID_ACCESS_POINT',
        'BD_S3_INVALID_ACCESS_POINT_ALIAS_ERROR',
        'BD_S3_INVALID_ADDRESSING_HEADER',
        'BD_S3_INVALID_ARGUMENT',
        'BD_S3_INVALID_BUCKET_ACL_WITH_OBJECT_OWNERSHIP',
        'BD_S3_INVALID_BUCKET_NAME',
        800 => 'BD_S3_INVALID_BUCKET_STATE',
        'BD_S3_INVALID_DIGEST',
        'BD_S3_INVALID_ENCRYPTION_ALGORITHM_ERROR',
        'BD_S3_INVALID_LOCATION_CONSTRAINT',
        'BD_S3_INVALID_OBJECT_STATE',
        'BD_S3_INVALID_PART',
        'BD_S3_INVALID_PART_ORDER',
        'BD_S3_INVALID_PAYER',
        'BD_S3_INVALID_POLICY_DOCUMENT',
        'BD_S3_INVALID_RANGE',
        810 => 'BD_S3_INVALID_REQUEST',
        'BD_S3_INVALID_SECURITY',
        'BD_S3_INVALID_SOAPREQUEST',
        'BD_S3_INVALID_STORAGE_CLASS',
        'BD_S3_INVALID_TARGET_BUCKET_FOR_LOGGING',
        'BD_S3_INVALID_TOKEN',
        'BD_S3_INVALID_URI',
        'BD_S3_KEY_TOO_LONG_ERROR',
        'BD_S3_MALFORMED_ACL_ERROR',
        'BD_S3_MALFORMED_POSTREQUEST',
        820 => 'BD_S3_MALFORMED_XML',
        'BD_S3_MAX_MESSAGE_LENGTH_EXCEEDED',
        'BD_S3_MAX_POST_PRE_DATA_LENGTH_EXCEEDED_ERROR',
        'BD_S3_METADATA_TOO_LARGE',
        'BD_S3_METHOD_NOT_ALLOWED',
        'BD_S3_MISSING_ATTACHMENT',
        'BD_S3_MISSING_CONTENT_LENGTH',
        'BD_S3_MISSING_REQUEST_BODY_ERROR',
        'BD_S3_MISSING_SECURITY_ELEMENT',
        'BD_S3_MISSING_SECURITY_HEADER',
        830 => 'BD_S3_NO_LOGGING_STATUS_FOR_KEY',
        'BD_S3_NO_SUCH_BUCKET',
        'BD_S3_NO_SUCH_BUCKET_POLICY',
        'BD_S3_NO_SUCH_CORSCONFIGURATION',
        'BD_S3_NO_SUCH_KEY',
        'BD_S3_NO_SUCH_LIFECYCLE_CONFIGURATION',
        'BD_S3_NO_SUCH_MULTI_REGION_ACCESS_POINT',
        'BD_S3_NO_SUCH_WEBSITE_CONFIGURATION',
        'BD_S3_NO_SUCH_TAG_SET',
        'BD_S3_NO_SUCH_UPLOAD',
        840 => 'BD_S3_OBJECT_LOCK_CONFIGURATION_NOT_FOUND_ERROR',
        'BD_S3_OPERATION_ABORTED',
        'BD_S3_PERMANENT_REDIRECT',
        'BD_S3_PRECONDITION_FAILED',
        'BD_S3_REDIRECT',
        'BD_S3_REQUEST_HEADER_SECTION_TOO_LARGE',
        'BD_S3_REQUEST_IS_NOT_MULTI_PART_CONTENT',
        'BD_S3_REQUEST_TIMEOUT',
        'BD_S3_REQUEST_TIME_TOO_SKEWED',
        'BD_S3_REQUEST_TORRENT_OF_BUCKET_ERROR',
        850 => 'BD_S3_RESTORE_ALREADY_IN_PROGRESS',
        'BD_S3_SERVER_SIDE_ENCRYPTION_CONFIGURATION_NOT_FOUND_ERROR',
        'BD_S3_SERVICE_UNAVAILABLE',
        'BD_S3_SIGNATURE_DOES_NOT_MATCH',
        'BD_S3_SLOW_DOWN',
        'BD_S3_SLOW_DOWN_503',
        'BD_S3_TEMPORARY_REDIRECT',
        'BD_S3_TOKEN_REFRESH_REQUIRED',
        'BD_S3_TOO_MANY_ACCESS_POINTS',
        'BD_S3_TOO_MANY_BUCKETS',
        860 => 'BD_S3_TOO_MANY_MULTI_REGION_ACCESS_POINTREGIONS_ERROR',
        'BD_S3_TOO_MANY_MULTI_REGION_ACCESS_POINTS',
        'BD_S3_UNEXPECTED_CONTENT',
        'BD_S3_UNRESOLVABLE_GRANT_BY_EMAIL_ADDRESS',
        'BD_S3_USER_KEY_MUST_BE_SPECIFIED',
        'BD_S3_NO_SUCH_ACCESS_POINT',
        'BD_S3_INVALID_TAG',
        'BD_S3_MALFORMED_POLICY',
        'BD_CEPH_RADOS_GET_OMAP_KEY_VAL_ERROR ',
        'BD_CEPH_IS_NOT_HEALTHY_ERROR',
        'BD_OFFLINE_REMOVE_DEVICE_INSTALLATION_RESTRICTIONS_ERROR',
        'BD_OFFLINE_REMOVE_DEVICE_INSTALLATION_RESTRICTIONS_WARNING',

        880 => 'BD_LICENSE_INVALID_ROOT_TYPE',
        'BD_LICENSE_AUTH_SPACE_NOT_ENOUGH_ERROR',
        'BD_LICENSE_AUTH_SPACE_NOT_ENOUGH_ERROR_TO_CONTINUE_VOLCDP_BACKUP',
        'BD_LICENSE_CLIENT_CAPACITY_MAX_CONSUMED_100',
        'BD_LICENSE_CLIENT_CAPACITY_MAX_CONSUMED_150',
        'BD_LICENSE_OF_EMD_TAKEOVER_EXHAUST_ERROR',
        'BD_LICENSE_AUTH_EXPIRED',
        'BD_LICENSE_AUTH_SPACE_NOT_ENOUGH_ERROR_TO_EXEC_CDP',

        900 => 'BD_COMMAND_ERROR',
        'BD_COMMAND_PERMISSION_DENIED_ERROR',
        'BD_COMMAND_NOT_FOUND_ERROR',
        'BD_COMMAND_INTERRUPTED_ERROR',
        'BD_COMMAND_SYSTEM_ERROR',
        905 => 'BD_ISCSI_ERR',
        'BD_ISCSI_ERR_SESS_NOT_FOUND',
        'BD_ISCSI_ERR_NOMEM',
        'BD_ISCSI_ERR_TRANS',
        'BD_ISCSI_ERR_LOGIN',
        'BD_ISCSI_ERR_IDBM',
        'BD_ISCSI_ERR_INVAL',
        'BD_ISCSI_ERR_TRANS_TIMEOUT',
        'BD_ISCSI_ERR_INTERNAL',
        'BD_ISCSI_ERR_LOGOUT',
        915 => 'BD_ISCSI_ERR_PDU_TIMEOUT',
        'BD_ISCSI_ERR_TRANS_NOT_FOUND',
        'BD_ISCSI_ERR_ACCESS',
        'BD_ISCSI_ERR_TRANS_CAPS',
        'BD_ISCSI_ERR_SESS_EXISTS',
        'BD_ISCSI_ERR_INVALID_MGMT_REQ',
        'BD_ISCSI_ERR_ISNS_UNAVAILABLE',
        'BD_ISCSI_ERR_ISCSID_COMM_ERR',
        'BD_ISCSI_ERR_FATAL_LOGIN',
        'BD_ISCSI_ERR_ISCSID_NOTCONN',
        925 => 'BD_ISCSI_ERR_NO_OBJS_FOUND',
        'BD_ISCSI_ERR_SYSFS_LOOKUP',
        'BD_ISCSI_ERR_HOST_NOT_FOUND',
        'BD_ISCSI_ERR_LOGIN_AUTH_FAILED',
        'BD_ISCSI_ERR_ISNS_QUERY',
        'BD_ISCSI_ERR_ISNS_REG_FAILED',
        'BD_DATA_STORAGE_FILE_LOCK_FAILED',
        'BD_DATA_STORAGE_FILE_UNLOCK_FAILED',
        'BD_STORAGE_TAPE_GROUP_ONLY_USE_ONE_BACKUP_SET_AND_BACKUP_SET_FREEZE',

        935 => 'BD_TIMEPOINT_CHECK_BACKUP_TASK_OCCUPY_CHAIN_ERROR',
        'BD_TIMEPOINT_CHECK_BACKUP_COPY_TASK_OCCUPY_CHAIN_ERROR',
        'BD_TIMEPOINT_CHECK_RECOVERY_TASK_OCCUPY_CHAIN_ERROR',
        'BD_STORAGE_IMPORT_BACKUP_TIMEPOINT_ERROR',
        'BD_CURRENT_OBJECT_HAS_NO_BACKUP_TIMEPOINT_ERROR',
        'BD_STORAGE_ADD_HOST_TO_HOST_GROUP_ERROR',
        'BD_TAPE_STORAGE_TYPE_NOT_SUPPORT_DESTINATION_INC_OR_DIFF',
        'BD_NETWORK_UNSURPORTED_ENCRYPT_METHOD',
        'BD_TASK_ALREADY_PENDING_ERROR',
        'BD_STORAGE_TAPE_CARRIAGE_NO_ENOUGH_SPACE',
        'BD_STORAGE_TAPE_NOT_FOUND_USEABLE_CARRIAGE',
        'BD_STORAGE_TAPE_CARRIAGE_USING_FOR_ANOTHER_TASK',
        'BD_STORAGE_TAPE_GROUP_NEED_ADD_IDLE_TAPE_CARRIAGE',
        948 => 'BD_STORAGE_TAPE_GROUP_NEED_GENERATE_NEW_BACKUP_SET',
        'BD_DEPEND_TIMEPOINT_BITMAP_VERSION_OLD_ERROR',
        'BD_INTEGRITY_CHECK_ERROR',
        'BD_INTEGRITY_CHECK_ERROR_STRATEGY',
        'BD_INTEGRITY_CHECK_ERROR_NOT_SUPPORT_TASK_TYPE',
        'BD_TIMEPOINT_PENETRATION_SEARCH_NOT_COMPLETE',
        'BD_TIMEPOINT_PENETRATION_SEARCH_RESULT_ERROR',
        'BD_IP_NOT_MATCH_ERROR',
        958 => 'BD_WORM_SET_ERROR',
        'BD_API_TOKEN_TIMEOUT',
        'BD_STORAGE_WORM_CLOSE_ERROR',
        'BD_STORAGE_WORM_NOT_ENOUGH_ERROR',
        'BD_WORM_GET_LEFT_TIME_ERROR',
        'BD_WORM_STORAGE_NOT_SUPPORT_ERROR',
        'BD_WORM_STORAGE_NOT_ENABLE_ERROR',

        968 => 'BD_TIMEPOINT_PENETRATION_SEARCH_RESULT_NO_DATA',
        'BD_VIRUS_INIT_ERROR',
        'BD_VIRUS_SET_ENV_ERROR',
        'BD_VIRUS_CREATE_ENGINE_ERROR',
        'BD_VIRUS_LOAD_LIB_ERROR',
        'BD_VIRUS_LIB_INVALID_ERROR',
        'BD_VIRUS_GET_MOUNT_POINT_MAP_RELATIONSHIP_ERROR',
        'BD_VIRUS_STOP_GET_VIRUS_SCAN_PROGRESS',
        'BD_VIRUS_FILE_IN_DANGER',
        'BD_OS_TYPE_CHOSEN_MISMATCH_WITH_ACTUAL_ERROR',
        'BD_WINDOWS_SESSION_NOT_ACTIVE',
        'BD_OFFLINE_RESET_IP_ERROR',
        'BD_OFFLINE_PLACE_SCRIPT_ERROR',
        'BD_OFFLINE_DELETE_AGENT_UUID_ERROR',
        'BD_OFFLINE_RESET_HOST_NAME_ERROR',
        'BD_OFFLINE_RESET_AGENT_UUID_ERROR',
        'BD_OFFLINE_RESET_VOLCDP_PREFIX_INFO_ERROR',
        'BD_OFFLINE_ADD_DRIVER_ERROR',
        'BD_OFFLINE_REPAIR_FSTAB_ERROR',
        'BD_OFFLINE_SET_ONLIE_DISK_SCRIPT_ERROR',
        'BD_BACKUP_CHAIN_IS_USE_ERROR',
        'BD_OFFLINE_NOT_SUPPORT_SCRIPT_TYPE_ERROR',
        'BD_RANSOM_PROTECT_STATUS_MODIFY_ERROR',
        'BD_OFFLINE_MODIFY_NET_MODEL_ERROR',
        'BD_DATA_CONTAINER_SIZE_BECOME_SMALLER_ERROR',
        'BD_VIRUS_MERGE_RESULT_DB_FILE_FAILED',
        'BD_OFFLINE_NOT_SUPPORT_MOUNT_BTRFS_FILE_SYSTEM_ERROR',
        'BD_BACKUP_CHAIN_MERGE_FAIL_ERROR',
        'BD_CAN_NOT_FOUND_GRUB_MENU_CONFIG_FILE',
        'BD_VOLUME_MAX_REMAINING_SIZE_NOT_ENOUGH',
        'BD_WAITE_THE_RAMOS_ONLINE_TIMEOUT',
        'BD_OBJECT_NEWEST_BACKUP_TIMEPOINT_DIFFERENT_WITH_DEPEND_TIMEPOINT',



        1000 => 'PT_SERVER_TIME_CONVERT_ERROR',		    // convert string or timestamp error
        'PT_SERVER_QUERY_ALL_STRATEGY_ERROR',			// query all time strategy error
        'PT_SERVER_STRATEGY_NULL_ERROR',				// there is no strategy in database
        'PT_SERVER_STRATEGY_ALREADY_START_ERROR',		// strategy already started in time window
        'PT_SERVER_STRATEGY_TIME_CONFIG_ERROR',		    // time strategy configuration error

        'PT_SERVER_LICENSE_INVALID_ERROR',			    // invalid license code
        'PT_SERVER_LICENSE_NOT_BELONG_TO_HOST_ERROR',	// license not belong to this host
        'PT_SERVER_LICENSE_DETECTED_OP_ERROR',		    // detected someone change license database
        'PT_SERVER_LICENSE_ALREADY_USED',				// license already used
        'PT_SERVER_LICENSE_SMALLER_THAN_OLD',			// new license authorized less than old one

        'PT_SERVER_LICENSE_EXHAUST_ERROR',			    // license exhaust
        'PT_SERVER_LICENSE_TYPE_NOT_MATCH',			    // license type not match
        'PT_SERVER_LICENSE_NOT_ENOUGH',				// the remaining license quantity is insufficient 
        'PT_SERVER_CDP_BACKUP_FEATURE_IS_NOT_SUPPORT_ERROR', 	//not support real-time backup in license file, please contact technical support
        'PT_SERVER_CDP_TAKEOVER_FEATURE_IS_NOT_SUPPORT_ERROR', //not support real-time takeover in license file, please contact technical support
        'PT_SERVER_CDP_BACKUP_LICENSE_NOT_ENOUGH_ERROR',	//real-time backup license is not enough, please delete some real-time backup task and re-upload the license file
        'PT_SERVER_CDP_TAKEOVER_LICENSE_NOT_ENOUGH_ERROR', //real-time disaster license is not enough, please delete some real-time backup task which configured auto-takeover and re-upload the license file




        2000 => 'RT_SERVER_DATABASE_ERROR',             // convert string or timestamp error

        //******虚拟机模块错误定义******//
        3000 => 'VM_DB_MACHINE_EXIST_ERROR',	// convert string or timestamp error
        'VM_DB_VCENTER_NOT_EXIST_ERROR',		// vcenter not exist
        'VM_DB_DECODE_PASSWORD_ERROR',			// decode password error
        'VM_DB_VCENTER_ALREADY_EXIST_ERROR',	// vm_vcenter already exist in database
        'VM_DB_HOST_ALREADY_EXIST_ERROR',		// vm_host already exist in database
        'VM_HOST_NOT_AUTH_ERROR',

        'VMWARE_CONN_NETWORK_ERROR',
        'VMWARE_CONN_URL_ERROR',
        'VMWARE_GET_VCENTER_INFO_ERROR',
        'VMWARE_LOGIN_ERROR',
        'VMWARE_LOGIN_USERNAME_PASSWORD_ERROR',
        'VMWARE_INVALID_ARGUMENT',
        'VMWARE_LOGOUT_ERROR',
        'VMWARE_AUTHENTICATED_ERROR',
        'VMWARE_RETRIEVE_PROPERTY_ERROR',
        'VMWARE_RETRIEVE_PROPERTY_ZERO_ERROR',
        'VMWARE_SCAN_HOST_ERROR',
        'VMWARE_SCAN_DATACENTER_ERROR',
        'VMWARE_SCAN_VCENTER_ERROR',
        'VMWARE_UNSUPPORT_DISK_TYPE_ERROR',
        'VMWARE_NOT_FIND_VM_ERROR',
        'VMWARE_GET_VCENTER_THUMBPRINT_ERROR',
        'VMWARE_DISK_LIB_INIT_ERROR',
        'VMWARE_DISK_LIB_LOAD_ERROR',
        'VMWARE_DISK_LIB_CONNECT_ERROR',
        'VMWARE_SET_CBT_ERROR',
        'VMWARE_POWEROFF_VM_ERROR',
        'VMWARE_POWERON_VM_ERROR',
        'VMWARE_INDEPENDENT_DISK_ERROR',
        'VMWARE_RDM_DISK_ERROR',
        'VMWARE_LATEST_TIMEPOINT_NOT_EXIST_ERROR',
        'VMWARE_LATEST_SNAPSHOT_NOT_EXIST_ERROR',
        'VMWARE_NOT_SUPPORT_CBT_ERROR',
        'VMWARE_CBT_NOT_ENABLE_ERROR',
        'VMWARE_DISK_NUM_CHANGED_ERROR',
        'VMWARE_DISK_CHANGED_ERROR',
        'VMWARE_LATEST_CHANGE_ID_IS_EMPTRY_ERROR',
        'VMWARE_LATEST_SNAPSHOT_NOT_IN_CHAIN_ERROR',
        'VMWARE_QUERY_CHANGED_DISK_AREAS_ERROR',
        'VMWARE_DISK_NOT_IN_VMFS_VOLUME_ERROR',
        'VMWARE_CREATE_SNAPSHOT_ERROR',
        'VMWARE_DELETE_SNAPSHOT_ERROR',
        'VMWARE_GET_SNAPSHOT_TREE_ERROR',
        'VMWARE_GET_SNAPSHOT_DISKS_ERROR',
        'VMWARE_SNAPSHOT_NUM_NOT_ZERO_ERROR',
        'VMWARE_GET_TOTAL_BACKUP_SIZE_ERROR',
        'VMWARE_NOT_FIND_SNAPSHOT_ERROR',
        'VMWARE_CREATE_BACKUP_DIR_ERROR',
        'VMWARE_RECOVERY_TIMEPOINT_NOT_EXIST_ERROR',
        'VMWARE_DEPEND_TIMEPOINT_NOT_EXIT_ERROR',
        'VMWARE_VM_ALREADY_EXIST_ERROR',
        'VMWARE_HOST_NOT_EXIST_ERROR',
        'VMWARE_VCENTER_NOT_EXIST_ERROR',
        'VMWARE_MACHINE_NOT_EXIST_ERROR',
        'VMWARE_DATASTORE_NOT_EXIST_ERROR',
        'VMWARE_DATASTORE_SPACE_NOT_ENOUGH_ERROR',
        'VMWARE_CREATE_VM_ERROR',
        'VMWARE_VM_ALREADY_POWEROFF_ERROR',
        'VMWARE_VM_ALREADY_POWERON_ERROR',
        'VMWARE_VM_NOT_SUSPEND_ERROR',
        'VMWARE_VM_NOT_POWERON_ERROR',
        'VMWARE_PARSER_VM_CONFIG_ERROR',
        'VMWARE_HOST_NOT_SUPPORTED_VM_VERSION_ERROR',
        'VMWARE_CONNECT_DISK_LIB_ERROR',
        'VMWARE_OPEN_REMOTE_DISK_ERROR',
        'VMWARE_READ_REMOTE_DISK_ERROR',
        'VMWARE_WRITE_REMOTE_DISK_ERROR',

        'VMWARE_GET_DISK_INFO_ERROR',
        'VMWARE_GET_DATASTORE_INFO_ERROR',
        'VMWARE_GET_NETWORK_INFO_ERROR',
        'VMWARE_SCAN_DATASTORE_ERROR',
        'VMWARE_GET_BACKUP_PREPARE_INFO_ERROR',
        'VMWARE_GET_RECOVERY_PREPARE_INFO_ERROR',
        'VMWARE_GET_RECOVERY_TOTAL_SIZE_ERROR',
        'VMWARE_GET_VM_INFO_ERROR',
        'VMWARE_OPEN_BACKUP_FILE_ERROR',
        'VMWARE_READ_BACKUP_FILE_ERROR',
        'VMWARE_WRITE_BACKUP_FILE_ERROR',
        'VMWARE_OPEN_BITMAP_FILE_ERROR',
        'VMWARE_READ_BITMAP_FILE_ERROR',
        'VMWARE_WRITE_BITMAP_FILE_ERROR',
        'VMWARE_OPEN_LATEST_BITMAP_FILE_ERROR',
        'VMWARE_READ_LATEST_BITMAP_FILE_ERROR',
        'VMWARE_OPEN_METADATA_FILE_ERROR',
        'VMWARE_WRITE_METADATA_FILE_ERROR',
        'VMWARE_READ_METADATA_FILE_ERROR',
        'VMWARE_FIND_BACKUP_FILE_ID_ERROR',
        'VMWARE_SAVE_SELF_EXPLAN_FILE_ERROR',
        'VMWARE_COMPRESS_ERROR',
        'VMWARE_DECOMPRESS_ERROR',
        'VMWARE_REVERT_TO_SNAPSHOT_ERROR',
        'VMWARE_DEDUPE_BLOCK_SIZE_ERROR',
        'VMWARE_GET_RECOVERY_VALID_DATA_SIZE_ERROR',
        'VMWARE_CREATE_DATASTORE_ERROR',
        'VMWARE_DELETE_DATASTORE_ERROR',
        'VMWARE_MOUNT_NFS_ERROR',
        'VMWARE_CONNECT_TO_VINFS_ERROR',
        'VMWARE_START_NFS_ERROR',
        'VMWARE_COPY_BITMAP_FILE_ERROR',
        'VMWARE_MERGE_TIMEPOINT_ERROR',
        'VMWARE_BUILD_BACKUP_VM_LIST_ERROR',
        'VMWARE_CHECK_AND_UPDATE_VM_INFO_ERROR',
        'VMWARE_BACKUP_VM_IS_NOT_EXIST_ERROR',
        'VMWARE_BACKUP_VM_IS_NOT_CONNECTED_ERROR',
        'VMWARE_BUILD_RECOVERY_VM_LIST_ERROR',
        'VMWARE_BUILD_INSTANT_RECOVERY_VM_INFO_ERROR',
        'VMWARE_BUILD_MOTION_VM_INFO_ERROR',
        'VMWARE_EXPORT_NFS_TABLE_ERROR',
        'VMWARE_OPEN_REMOTE_DISK_NETWORK_ERROR',
        'VMWARE_DELETE_VM_ERROR',
        'VMWARE_BACKUP_CHAIN_IS_MERGING_ERROR',
        'VMWARE_UPDATE_BACKUP_TOTAL_SIZE_ERROR',
        'VMWARE_UPDATE_BACKUP_REAL_SIZE_ERROR',
        'VMWARE_BACKUP_CHAIN_IS_USING_ERROR',
        'VMWARE_VM_NOT_CREATE_COMPLETE_ERROR',
        'VMWARE_CREATE_DIR_ON_DATASTORE_ERROR',
        'VMWARE_DELETE_DIR_FROM_DATASTORE_ERROR',
        'VMWARE_PROXY_VM_SOURCE_FILE_NOT_EXIST_ERROR',

        'XENSERVER_SDK_GENERIC_ERROR',
        'XENSERVER_HOST_IS_SLAVE_ERROR',
        'XENSERVER_HOST_EMPTY_ERROR',
        'XENSERVER_HOST_NOT_FOUND_ERROR',
        'XENSERVER_TRANSPORT_FAULT_ERROR',
        'XENSERVER_FETCH_ALL_HOST_ERROR',
        'XENSERVER_FETCH_ALL_VM_ERROR',
        'XENSERVER_FETCH_ALL_SR_ERROR',
        'XENSERVER_HANDLE_INVALID_ERROR',
        'XENSERVER_VM_NOT_RUNNING_ERROR',
        'XENSERVER_VM_NOT_HALTED_ERROR',
        'XENSERVER_INVALID_UUID_ERROR',
        'XENSERVER_AUTH_FAILED_ERROR',
        'XENSERVER_VBD_NOT_DISK_ERROR',
        'XENSERVER_VDI_IS_NOT_AVAILABLE_ERROR',
        'XENSERVER_VDI_INFO_CHANGE_ERROR',		    // xenserver vdi configuration change
        'XENSERVER_VDI_IS_NOT_EXIST_ERROR',		    // xenserver vdi is not exist error
        'XENSERVER_BACKUP_CONTAINER_FULL_WARRN',	// backup container full
        'XENSERVER_SR_NOT_FOUND_ERROR',
        'XENSERVER_VM_CONFIG_NOT_MATCH_WARN',		// config not match warnning
        'XENSERVER_CREATE_SNAPSHOT_ERROR',

        'XENSERVER_COMPARE_VDI_IS_NOT_FOUND_ERROR',
        'XENSERVER_HOST_FREE_MEMORY_NOT_ENOUGH_ERROR',
        'XENSERVER_HOST_RESOURCE_NOT_ENOUGH_ERROR',
        'XENSERVER_CONNECT_TO_VXEFS_PROCESS_ERROR',
        'XENSERVER_SR_PLUG_ERROR',
        'XENSERVER_DEPEND_TIMEPOINT_NOT_EXIST',
        'XENSERVER_BACKUP_LEVEL_CHANGE_WARN',

        'VM_NOT_SUPPORT_HYPERVISOR_ERROR',
        'VM_HOST_ALREADY_EXIST_WARN',
        'VM_HOST_ALREADY_EXIST_IN_VCENTER_ERROR',

        'VM_MACHINE_LIST_NOT_FOUND_ERROR',
        'VM_MACHINE_NOT_EXIST_ERROR',
        'VMWARE_RESTRICTED_VERSION_ERROR',

        'VMWARE_ADD_FLOPPY_DEVICE_ERROR', //Vmware add floppy device error
        'VMWARE_UPLOAD_DISK_FILE_ERROR', //Vmware upload disk file error
        'VMWARE_ADD_VIRTUAL_SWITCH_ERROR', //Vmware add virtual switch error
        'VMWARE_UPDATE_VIRTUAL_SWITCH_ERROR', //Vmware update virtual switch error
        'VMWARE_ADD_PORT_GROUP_ERROR', //Vmware add port group error
        'VMWARE_UPDATE_PORT_GROUP_ERROR', //Vmware update port group error
        'VMWARE_REMOVE_VIRTUAL_SWITCH_ERROR', //Vmware remove virtual switch error
        'VMWARE_VIRTUAL_SWITCH_NOT_EXIST_ERROR', //Vmware virtual switch not exist
        'VMWARE_VIRTUAL_SWITCH_ALREADY_EXIST_ERROR', //Vmware virtual switch already exist
        'VMWARE_PORT_GROUP_NOT_EXIST_ERROR', //Vmware port group not exist
        'VMWARE_PORT_GROUP_ALREADY_EXIST_ERROR', //Vmware port group already exist
        'VMWARE_DEPLOY_ORCH_NETWORK_ERROR', //Vmware deploy orch network error
        'VMWARE_DEPLOY_PROXY_VM_ERROR', //Vmware deploy proxy vm error
        'VMWARE_GET_DATACENTER_NAME_ERROR', //Vmware get datacenter name error
        'VMWARE_GET_PROXY_INFO_ERROR', //Vmware get proxy info error
        'VMWARE_DELETE_ORCH_PROXY_ERROR', //Vmware delete orch proxy error
        'VMWARE_CREATE_FLOPPY_FILE_ERROR', //Vmware create floppy file error

        'VMWARE_BUILD_ORCH_VM_LIST_ERROR',	//构建演练虚拟机列表错误
        'VMWARE_ADD_VIRTUAL_IP_ERROR',		//备份服务器添加虚拟IP错误
        'VMWARE_DEL_VIRTUAL_IP_ERROR',		//备份服务器删除虚拟IP错误
        'VMWARE_ADD_ROUTE_ERROR',				//备份服务器添加路由错误
        'VMWARE_DEL_ROUTE_ERROR',				//备份服务器删除路由错误
        'VMWARE_ADD_DISK_FOR_VM_ERROR',         //为虚拟机添加磁盘失败
        'VMWARE_NOT_SUPPORT_BACKUP_TEMPLATE_ERROR',     //不支持备份模板
        'VMWARE_GET_THUMBPRINT_OF_VC_OR_HOST_ERROR',    //获vCenter/Host指纹错误


        'KVM_LIBVIRT_ERROR', //libvirt error
        'KVM_LOGIN_ERROR', //Login libvirt server error
        'KVM_LIBVIRT_NOT_CONNECT_ERROR', //not connect to libvirt error
        'KVM_LOGIN_USERNAME_PASSWORD_ERROR', //authenticate user and password error
        'KVM_NOT_SUPPORT_EXTERNAL_SNAPSHOT_ERROR', //not support external snapshot error
        'KVM_DISK_OPEN_MODE_ERROR', //use the wrong mode to open virtual disk
        'KVM_QCOW2_BACKINGSTORE_NOT_FOUND_ERROR', //backing store file not found error
        'KVM_NOT_SUPPORT_DISK_TYPE_ERROR', //not support virtual disk type
        'KVM_DISK_NUM_CHANGE_ERROR', //disk number change error

        'VM_QCOW2_READ_CLUSTER_ERROR', //qcow2 read cluster error
        'VM_QCOW2_READ_QCOW2_ERROR', //read qcow2 file error
        'VM_QCOW2_CLUSTER_ERROR', //qcow2 cluster error
        'VM_QCOW2_DESIGNATEDDATA_ERROR', //qcow2 designate data error
        'VM_QCOW2_INTERNALSNAPSHOT_ERROR', //internal snapshot info error
        'VM_QCOW2_L2TABLEOFFSET_ERROR', //L2 table offset error
        'VM_QCOW2_UNCOMPRESSDATA_ERROR', //uncompressed data error
        'VM_QCOW2_OPEN_QCOW2FILE_ERROR', //open qcow2 file error
        'VM_QCOW2_HEADERWRONG_ERROR', //qcow2 header error
        'VM_QCOW2_LOAD_L1TABLE_ERROR', //load L1 table error
        'VM_QCOW2_CLUSTER_BITMAP_NOT_MATCH_ERROR', //disk cluster bitmap element number not match error

        'KVM_DISK_CLUSTER_SIZE_NOT_EQUAL_ERROR', //request cluster size and header cluster size not the same error
        'KVM_NOT_SUPPORT_BACKUP_LEVEL_ERROR', //not support this backup level
        'KVM_CONTAINER_FULL_ERROR', //backup container is full
        'KVM_DISK_VIRTUAL_SIZE_CHANGE_ERROR', //disk virtual size change error
        'KVM_DISK_NOT_EXIST_ERROR', //disk not exist error
        'KVM_STORAGE_POOL_NOT_EXIST_ERROR', //storage pool is not exist
        'KVM_ADVANCE_RECOVERY_VM_CONFIG_ERROR', //advacne recovery config invalid error
        'KVM_NO_PHYSICAL_NETWORK_ERROR', //no physical network error

        // kvm libvirt error wrapper
        'KVM_LIBVIRT_NO_MEMORY_ERROR', //allocation kvm memory error
        'KVM_LIBVIRT_NOT_SUPPORT_FUNCTION_ERROR', //not support this function
        'KVM_LIBVIRT_UNKNOW_HOST_ERROR', //could not resolve hostname error
        'KVM_LIBVIRT_CONNECT_ERROR', //connect to hypervisor error
        'KVM_LIBVIRT_INVALID_DOMAIN_OBJECT_ERROR', //invliad domain object error
        'KVM_LIBVIRT_INVALID_PARAM_ERROR', //invalid function argument error
        'KVM_LIBVIRT_OPERATION_FAILED_ERROR', //operation in hypervisor error
        'KVM_LIBVIRT_HTTP_GET_ERROR', //a HTTP GET command error in libvirt
        'KVM_LIBVIRT_HTTP_POST_ERROR', //a HTTP POST command error in libvirt
        'KVM_LIBVIRT_HTTP_ERROR', //unexpected HTTP error in libvirt
        'KVM_LIBVIRT_SEXPR_SERIAL_ERROR', //failure to serialize an S-Expr in libvirt
        'KVM_LIBVIRT_UNKNOW_OS_TYPE_ERROR', //unknown operating system type in libvirt
        'KVM_LIBVIRT_NO_KERNAL_ERROR', //missing kernal information in libvirt
        'KVM_LIBVIRT_NO_ROOT_ERROR', //missing root device information in libvirt
        'KVM_LIBVIRT_NO_SOURCE_ERROR', //missing source device information in libvirt
        'KVM_LIBVIRT_NO_TARGET_ERROR', //missing  device information in libvirt
        'KVM_LIBVIRT_NO_DOMAIN_NAME_ERROR', //missing domain name information in libvirt
        'KVM_LIBVIRT_NO_DOMAIN_OS_ERROR', //missing domain os information in libvirt
        'KVM_LIBVIRT_NO_DOMAIN_DEVICE_ERROR', //missing domain device information in libvirt
        'KVM_LIBVIRT_TOO_MANY_DRIVER_ERROR', //too many driver registered error
        'KVM_LIBVIRT_NOT_SUPPORT_BY_DRIVERS_ERROR', //not support by the drivers
        'KVM_LIBVIRT_XML_ERROR', //an XML descripion is not well formed or broken in libvirt
        'KVM_LIBVIRT_DOMAIN_ALREADY_EXIST_ERROR', //domain already exist error
        'KVM_LIBVIRT_OPERATION_DENIED_ERROR', //operation forbidden on read-only libvirt connection
        'KVM_LIBVIRT_OPEN_CONF_ERROR', //failed to open a conf file in libvirt
        'KVM_LIBVIRT_READ_CONF_ERROR', //failed to read a conf file in libvirt
        'KVM_LIBVIRT_PARSE_CONF_ERROR', //failed to parse conf file in libvirt
        'KVM_LIBVIRT_CONF_SYNTAX_ERROR', //failed to parse the syntax of a conf file in libvir
        'KVM_LIBVIRT_WRITE_CONF_ERROR', //failed to write a conf file in libvirt
        'KVM_LIBVIRT_DETAIL_XML_ERROR', //detail an XML error in libvirt
        'KVM_LIBVIRT_INVALID_NETWORK_ERROR', //detected invalid network in libvirt
        'KVM_LIBVIRT_NETWORK_EXIST_ERROR', //the network already exist in libvirt
        'KVM_LIBVIRT_SYSTEM_CALL_ERROR', //'general system call failure in libvirt
        'KVM_LIBVIRT_RPC_ERROR', //some sort of RPC error in libvirt
        'KVM_LIBVIRT_GNUTLS_ERROR', //error from a GNUTLS call in libvirt
        'KVM_LIBVIRT_START_NETWORK_ERROR', //failed to start a network in libvirt
        'KVM_LIBVIRT_NO_DOMAIN_ERROR', //domain not found or unexpectedly disappeared
        'KVM_LIBVIRT_NO_NETWORK_ERROR', //not found network in libvirt
        'KVM_LIBVIRT_INVALID_MAC_ERROR', //invalid mac address in libvirt
        'KVM_LIBVIRT_AUTH_ERROR', //authentication failed in libvirt
        'KVM_LIBVIRT_INVALID_STORAGE_POOL_ERROR', //invalid storage pool in libvirt
        'KVM_LIBVIRT_INVALID_STORAGE_VOL_ERROR', //invalid storage volume in libvirt
        'KVM_LIBVIRT_START_STORAGE_POOL_ERROR', //failed to start storage pool in libvirt
        'KVM_LIBVIRT_NO_STORAGE_POOL_ERROR', //storage pool not found in libvirt
        'KVM_LIBVIRT_NO_STORAGE_VOL_ERROR', //storage volume not found in libvirt
        'KVM_LIBVIRT_START_NODE_ERROR', //failed to start node driver in libvirt
        'KVM_LIBVIRT_INVALID_NODE_DEVICE_ERROR', //detected invalid node device in libvirt
        'KVM_LIBVIRT_NO_NODE_DEVICE_ERROR', //node device not found in libvirt
        'KVM_LIBVIRT_NO_SECURITY_MODEL_ERROR', //security model not found in libvirt
        'KVM_LIBVIRT_OPERATION_INVALID_ERROR', //operation is not applicable at this time in libvirt
        'KVM_LIBVIRT_START_INTERFACE_ERROR', //failure to start interface driver in libvirt
        'KVM_LIBVIRT_NO_INTERFACE_ERROR', //interface driver not found in libvirt
        'KVM_LIBVIRT_INVALID_INTERFACE_ERROR', //invalid interface object in libvirt
        'KVM_LIBVIRT_MULTIPLE_INTERFACE_ERROR', //more than one matching interface found in libvirt
        'KVM_LIBVIRT_START_NWFILTER_ERROR', //failed to start nwfilter driver
        'KVM_LIBVIRT_INVALID_NWFILTER_ERROR', //invalid nwfilter object in libvirt
        'KVM_LIBVIRT_NO_NWFILTER_ERROR', //nwfilter pool not found in libvirt
        'KVM_LIBVIRT_BUILD_FIREWALL_ERROR', //build firewall error in libvirt
        'KVM_LIBVIRT_START_SECRET_STORAGE_ERROR', //failed to start secret storage in libvirt
        'KVM_LIBVIRT_INVALID_SECRET_ERROR', //invalid secret storage object in libvirt
        'KVM_LIBVIRT_NO_SECRET_ERROR', //secret not found in libvirt
        'KVM_LIBVIRT_NOT_SUPPORT_CONFIG_ERROR', //unsupported configuration construct in libvirt
        'KVM_LIBVIRT_OPERATION_TIMEOUT_ERROR', //operation time out error
        'KVM_LIBVIRT_MIGRATE_PERSIST_ERROR', //a migration worked, but making the VM persist on the dest host failed
        'KVM_LIBVIRT_HOOK_SCRIPT_ERROR', //a synchronous hook script failed in libvirt
        'KVM_LIBVIRT_INVALID_DOMAIN_SNAPSHOT_ERROR', //invalid domain snapshot in libvirt
        'KVM_LIBVIRT_NO_DOMAIN_SNAPSHOT_ERROR', //domain snapshot not found in libvirt
        'KVM_LIBVIRT_INVALID_STREAM_ERROR', //stream pointer not valid in libvirt
        'KVM_LIBVIRT_ARGUMENT_UNSUPPORTED_ERROR', //valid API use but unsupported by the given driver in libvirt
        'KVM_LIBVIRT_STORAGE_PROBE_ERROR', //storage pool probe failed error in libvirt
        'KVM_LIBVIRT_STORAGE_ALREADY_BUILD_ERROR', //storage pool already build in libvirt
        'KVM_LIBVIRT_SNAPSHOT_REVERT_RISKY_ERROR', //force was not requested for a risky domain snapshot revert in libvirt
        'KVM_LIBVIRT_OPERATION_ABORTED_ERROR', //operation on a domain was canceled/aborted by user
        'KVM_LIBVIRT_AUTH_CANCELLED_ERROR', //authentication cancelled in libvirt
        'KVM_LIBVIRT_NO_DOMAIN_METADATA_ERROR', //the metadata is not present in libvirt
        'KVM_LIBVIRT_MIGRATE_UNSAFE_ERROR',	//migration is not safe in libvirt
        'KVM_LIBVIRT_OVERFLOW_ERROR', //integer overflow error in libvirt
        'KVM_LIBVIRT_BLOCK_COPY_ACTIVE_ERROR', //block copy active volume prevedted by block copy job in libvirt
        'KVM_LIBVIRT_OPERATION_UNSUPPORTED_ERROR', //the requested operation is not supported in libvirt
        'KVM_LIBVIRT_SSH_ERROR', //error in ssh transport driver in libvirt
        'KVM_LIBVIRT_AGENT_UNRESPNSIVE_ERROR', //guest agent is unresponsive, not running or not usable in libvirt
        'KVM_LIBVIRT_RESOURCE_BUSY_ERROR', //resource is already in use error in libvirt
        'KVM_LIBVIRT_ACCESS_DENIED_ERROR', //operation on the object/resource was denied in libvirt
        'KVM_LIBVIRT_DBUS_SERVICE_ERROR', //an error from a dbus service in libvirt
        'KVM_LIBVIRT_STORAGE_VOLUME_EXIST_ERROR', //storage volume already exist in libvirt
        'KVM_LIBVIRT_CPU_INCOMPATIBLE_ERROR', //given CPU is incompatible with the host CP in libvirt
        'KVM_LIBVIRT_XML_INVALID_SCHEMA_ERROR',	//XML document doesn't validate against schema in libvirt
        'KVM_LIBVIRT_AUTH_UNAVAILABLE_ERROR', //authentication unavailable in libvirt
        'KVM_LIBVIRT_NO_SERVER_ERROR', //server was not found error in libvirt
        'KVM_LIBVIRT_NO_CLIENT_ERROR', //client was not found error in libvirt
        'KVM_LIBVIRT_AGENT_UNSYNCED_ERROR',	//guest agent replies with wrong id to guest-sync command in libvirt
        'KVM_LIBVIRT_LIBSSH_ERROR', //error in libssh transport driver in libvirt
        'KVM_NOT_SUPPORT_STORAGE_POOL_TYPE_ERROR',
        'XENSERVER_ACTIVATION_WHILE_NOT_FREE',
        'XENSERVER_AUTH_ALREADY_ENABLED',
        'XENSERVER_AUTH_DISABLE_FAILED',
        'XENSERVER_AUTH_DISABLE_FAILED_PERMISSION_DENIED',
        'XENSERVER_AUTH_DISABLE_FAILED_WRONG_CREDENTIALS',
        'XENSERVER_AUTH_ENABLE_FAILED',
        'XENSERVER_AUTH_ENABLE_FAILED_DOMAIN_LOOKUP_FAILED',
        'XENSERVER_AUTH_ENABLE_FAILED_PERMISSION_DENIED',
        'XENSERVER_AUTH_ENABLE_FAILED_UNAVAILABLE',
        'XENSERVER_AUTH_ENABLE_FAILED_WRONG_CREDENTIALS',
        'XENSERVER_AUTH_IS_DISABLED',
        'XENSERVER_AUTH_SERVICE_ERROR',
        'XENSERVER_AUTH_UNKNOWN_TYPE',
        'XENSERVER_BACKUP_SCRIPT_FAILED',
        'XENSERVER_BOOTLOADER_FAILED',
        'XENSERVER_BRIDGE_NOT_AVAILABLE',
        'XENSERVER_CANNOT_ADD_TUNNEL_TO_BOND_SLAVE',
        'XENSERVER_CANNOT_ADD_VLAN_TO_BOND_SLAVE',
        'XENSERVER_CANNOT_CHANGE_PIF_PROPERTIES',
        'XENSERVER_CANNOT_CONTACT_HOST',
        'XENSERVER_CANNOT_CREATE_STATE_FILE',
        'XENSERVER_CANNOT_DESTROY_DISASTER_RECOVERY_TASK',
        'XENSERVER_CANNOT_DESTROY_SYSTEM_NETWORK',
        'XENSERVER_CANNOT_ENABLE_REDO_LOG',
        'XENSERVER_CANNOT_EVACUATE_HOST',
        'XENSERVER_CANNOT_FETCH_PATCH',
        'XENSERVER_CANNOT_FIND_OEM_BACKUP_PARTITION',
        'XENSERVER_CANNOT_FIND_PATCH',
        'XENSERVER_CANNOT_FIND_STATE_PARTITION',
        'XENSERVER_CANNOT_PLUG_BOND_SLAVE',
        'XENSERVER_CANNOT_PLUG_VIF',
        'XENSERVER_CANNOT_RESET_CONTROL_DOMAIN',
        'XENSERVER_CERTIFICATE_ALREADY_EXISTS',
        'XENSERVER_CERTIFICATE_CORRUPT',
        'XENSERVER_CERTIFICATE_DOES_NOT_EXIST',
        'XENSERVER_CERTIFICATE_LIBRARY_CORRUPT',
        'XENSERVER_CERTIFICATE_NAME_INVALID',
        'XENSERVER_CHANGE_PASSWORD_REJECTED',
        'XENSERVER_COULD_NOT_FIND_NETWORK_INTERFACE_WITH_SPECIFIED_DEVICE_NAME_AND_MAC_ADDRESS',
        'XENSERVER_COULD_NOT_IMPORT_DATABASE',
        'XENSERVER_CPU_FEATURE_MASKING_NOT_SUPPORTED',
        'XENSERVER_CRL_ALREADY_EXISTS',
        'XENSERVER_CRL_CORRUPT',
        'XENSERVER_CRL_DOES_NOT_EXIST',
        'XENSERVER_CRL_NAME_INVALID',
        'XENSERVER_DB_UNIQUENESS_CONSTRAINT_VIOLATION',
        'XENSERVER_DEFAULT_SR_NOT_FOUND',
        'XENSERVER_DEVICE_ALREADY_ATTACHED',
        'XENSERVER_DEVICE_ALREADY_DETACHED',
        'XENSERVER_DEVICE_ALREADY_EXISTS',
        'XENSERVER_DEVICE_ATTACH_TIMEOUT',
        'XENSERVER_DEVICE_DETACH_REJECTED',
        'XENSERVER_DEVICE_DETACH_TIMEOUT',
        'XENSERVER_DEVICE_NOT_ATTACHED',
        'XENSERVER_DISK_VBD_MUST_BE_READWRITE_FOR_HVM',
        'XENSERVER_DOMAIN_BUILDER_ERROR',
        'XENSERVER_DOMAIN_EXISTS',
        'XENSERVER_DUPLICATE_PIF_DEVICE_NAME',
        'XENSERVER_DUPLICATE_VM',
        'XENSERVER_EVENTS_LOST',
        'XENSERVER_EVENT_FROM_TOKEN_PARSE_FAILURE',
        'XENSERVER_EVENT_SUBSCRIPTION_PARSE_FAILURE',
        'XENSERVER_FEATURE_REQUIRES_HVM',
        'XENSERVER_FEATURE_RESTRICTED',
        'XENSERVER_FIELD_TYPE_ERROR',
        'XENSERVER_GPU_GROUP_CONTAINS_NO_PGPUS',
        'XENSERVER_GPU_GROUP_CONTAINS_PGPU',
        'XENSERVER_GPU_GROUP_CONTAINS_VGPU',
        'XENSERVER_HA_ABORT_NEW_MASTER',
        'XENSERVER_HA_CANNOT_CHANGE_BOND_STATUS_OF_MGMT_IFACE',
        'XENSERVER_HA_CONSTRAINT_VIOLATION_NETWORK_NOT_SHARED',
        'XENSERVER_HA_CONSTRAINT_VIOLATION_SR_NOT_SHARED',
        'XENSERVER_HA_FAILED_TO_FORM_LIVESET',
        'XENSERVER_HA_HEARTBEAT_DAEMON_STARTUP_FAILED',
        'XENSERVER_HA_HOST_CANNOT_ACCESS_STATEFILE',
        'XENSERVER_HA_HOST_CANNOT_SEE_PEERS',
        'XENSERVER_HA_HOST_IS_ARMED',
        'XENSERVER_HA_IS_ENABLED',
        'XENSERVER_HA_LOST_STATEFILE',
        'XENSERVER_HA_NOT_ENABLED',
        'XENSERVER_HA_NOT_INSTALLED',
        'XENSERVER_HA_NO_PLAN',
        'XENSERVER_HA_OPERATION_WOULD_BREAK_FAILOVER_PLAN',
        'XENSERVER_HA_POOL_IS_ENABLED_BUT_HOST_IS_DISABLED',
        'XENSERVER_HA_SHOULD_BE_FENCED',
        'XENSERVER_HA_TOO_FEW_HOSTS',
        'XENSERVER_HOSTS_NOT_COMPATIBLE',
        'XENSERVER_HOSTS_NOT_HOMOGENEOUS',
        'XENSERVER_HOST_BROKEN',
        'XENSERVER_HOST_CANNOT_ATTACH_NETWORK',
        'XENSERVER_HOST_CANNOT_DESTROY_SELF',
        'XENSERVER_HOST_CANNOT_READ_METRICS',
        'XENSERVER_HOST_CD_DRIVE_EMPTY',
        'XENSERVER_HOST_DISABLED',
        'XENSERVER_HOST_DISABLED_UNTIL_REBOOT',
        'XENSERVER_HOST_EVACUATE_IN_PROGRESS',
        'XENSERVER_HOST_HAS_NO_MANAGEMENT_IP',
        'XENSERVER_HOST_HAS_RESIDENT_VMS',
        'XENSERVER_HOST_IN_EMERGENCY_MODE',
        'XENSERVER_HOST_IN_USE',
        'XENSERVER_HOST_IS_LIVE',
        'XENSERVER_HOST_ITS_OWN_SLAVE',
        'XENSERVER_HOST_MASTER_CANNOT_TALK_BACK',
        'XENSERVER_HOST_NAME_INVALID',
        'XENSERVER_HOST_NOT_DISABLED',
        'XENSERVER_HOST_NOT_ENOUGH_FREE_MEMORY',
        'XENSERVER_HOST_NOT_LIVE',
        'XENSERVER_HOST_OFFLINE',
        'XENSERVER_HOST_POWER_ON_MODE_DISABLED',
        'XENSERVER_HOST_STILL_BOOTING',
        'XENSERVER_HOST_UNKNOWN_TO_MASTER',
        'XENSERVER_ILLEGAL_VBD_DEVICE',
        'XENSERVER_IMPORT_ERROR',
        'XENSERVER_IMPORT_ERROR_ATTACHED_DISKS_NOT_FOUND',
        'XENSERVER_IMPORT_ERROR_CANNOT_HANDLE_CHUNKED',
        'XENSERVER_IMPORT_ERROR_FAILED_TO_FIND_OBJECT',
        'XENSERVER_IMPORT_ERROR_PREMATURE_EOF',
        'XENSERVER_IMPORT_ERROR_SOME_CHECKSUMS_FAILED',
        'XENSERVER_IMPORT_ERROR_UNEXPECTED_FILE',
        'XENSERVER_IMPORT_INCOMPATIBLE_VERSION',
        'XENSERVER_INCOMPATIBLE_PIF_PROPERTIES',
        'XENSERVER_INTERFACE_HAS_NO_IP',
        'XENSERVER_INTERNAL_ERROR',
        'XENSERVER_INVALID_DEVICE',
        'XENSERVER_INVALID_EDITION',
        'XENSERVER_INVALID_FEATURE_STRING',
        'XENSERVER_INVALID_IP_ADDRESS_SPECIFIED',
        'XENSERVER_INVALID_PATCH',
        'XENSERVER_INVALID_PATCH_WITH_LOG',
        'XENSERVER_INVALID_VALUE',
        'XENSERVER_IS_TUNNEL_ACCESS_PIF',
        'XENSERVER_JOINING_HOST_CANNOT_BE_MASTER_OF_OTHER_HOSTS',
        'XENSERVER_JOINING_HOST_CANNOT_CONTAIN_SHARED_SRS',
        'XENSERVER_JOINING_HOST_CANNOT_HAVE_RUNNING_OR_SUSPENDED_VMS',
        'XENSERVER_JOINING_HOST_CANNOT_HAVE_RUNNING_VMS',
        'XENSERVER_JOINING_HOST_CANNOT_HAVE_VMS_WITH_CURRENT_OPERATIONS',
        'XENSERVER_JOINING_HOST_CONNECTION_FAILED',
        'XENSERVER_JOINING_HOST_SERVICE_FAILED',
        'XENSERVER_LICENCE_RESTRICTION',
        'XENSERVER_LICENSE_CANNOT_DOWNGRADE_WHILE_IN_POOL',
        'XENSERVER_LICENSE_CHECKOUT_ERROR',
        'XENSERVER_LICENSE_DOES_NOT_SUPPORT_POOLING',
        'XENSERVER_LICENSE_DOES_NOT_SUPPORT_XHA',
        'XENSERVER_LICENSE_EXPIRED',
        'XENSERVER_LICENSE_FILE_DEPRECATED',
        'XENSERVER_LICENSE_HOST_POOL_MISMATCH',
        'XENSERVER_LICENSE_PROCESSING_ERROR',
        'XENSERVER_LOCATION_NOT_UNIQUE',
        'XENSERVER_MAC_DOES_NOT_EXIST',
        'XENSERVER_MAC_INVALID',
        'XENSERVER_MAC_STILL_EXISTS',
        'XENSERVER_MAP_DUPLICATE_KEY',
        'XENSERVER_MESSAGE_DEPRECATED',
        'XENSERVER_MESSAGE_METHOD_UNKNOWN',
        'XENSERVER_MESSAGE_PARAMETER_COUNT_MISMATCH',
        'XENSERVER_MESSAGE_REMOVED',
        'XENSERVER_MIRROR_FAILED',
        'XENSERVER_MISSING_CONNECTION_DETAILS',
        'XENSERVER_NETWORK_ALREADY_CONNECTED',
        'XENSERVER_NETWORK_CONTAINS_PIF',
        'XENSERVER_NETWORK_CONTAINS_VIF',
        'XENSERVER_NOT_ALLOWED_ON_OEM_EDITION',
        'XENSERVER_NOT_IMPLEMENTED',
        'XENSERVER_NOT_IN_EMERGENCY_MODE',
        'XENSERVER_NOT_SUPPORTED_DURING_UPGRADE',
        'XENSERVER_NOT_SYSTEM_DOMAIN',
        'XENSERVER_NO_HOSTS_AVAILABLE',
        'XENSERVER_NO_MORE_REDO_LOGS_ALLOWED',
        'XENSERVER_OBJECT_NOLONGER_EXISTS',
        'XENSERVER_ONLY_ALLOWED_ON_OEM_EDITION',
        'XENSERVER_OPENVSWITCH_NOT_ACTIVE',
        'XENSERVER_OPERATION_BLOCKED',
        'XENSERVER_OPERATION_NOT_ALLOWED',
        'XENSERVER_OPERATION_PARTIALLY_FAILED',
        'XENSERVER_OTHER_OPERATION_IN_PROGRESS',
        'XENSERVER_OUT_OF_SPACE',
        'XENSERVER_PATCH_ALREADY_APPLIED',
        'XENSERVER_PATCH_ALREADY_EXISTS',
        'XENSERVER_PATCH_APPLY_FAILED',
        'XENSERVER_PATCH_IS_APPLIED',
        'XENSERVER_PATCH_PRECHECK_FAILED_ISO_MOUNTED',
        'XENSERVER_PATCH_PRECHECK_FAILED_PREREQUISITE_MISSING',
        'XENSERVER_PATCH_PRECHECK_FAILED_UNKNOWN_ERROR',
        'XENSERVER_PATCH_PRECHECK_FAILED_VM_RUNNING',
        'XENSERVER_PATCH_PRECHECK_FAILED_WRONG_SERVER_BUILD',
        'XENSERVER_PATCH_PRECHECK_FAILED_WRONG_SERVER_VERSION',
        'XENSERVER_PBD_EXISTS',
        'XENSERVER_PERMISSION_DENIED',
        'XENSERVER_PGPU_INSUFFICIENT_CAPACITY_FOR_VGPU',
        'XENSERVER_PGPU_IN_USE_BY_VM',
        'XENSERVER_PGPU_NOT_COMPATIBLE_WITH_GPU_GROUP',
        'XENSERVER_PIF_ALREADY_BONDED',
        'XENSERVER_PIF_BOND_NEEDS_MORE_MEMBERS',
        'XENSERVER_PIF_CANNOT_BOND_CROSS_HOST',
        'XENSERVER_PIF_CONFIGURATION_ERROR',
        'XENSERVER_PIF_DEVICE_NOT_FOUND',
        'XENSERVER_PIF_DOES_NOT_ALLOW_UNPLUG',
        'XENSERVER_PIF_HAS_NO_NETWORK_CONFIGURATION',
        'XENSERVER_PIF_HAS_NO_V6_NETWORK_CONFIGURATION',
        'XENSERVER_PIF_INCOMPATIBLE_PRIMARY_ADDRESS_TYPE',
        'XENSERVER_PIF_IS_MANAGEMENT_INTERFACE',
        'XENSERVER_PIF_IS_PHYSICAL',
        'XENSERVER_PIF_IS_VLAN',
        'XENSERVER_PIF_TUNNEL_STILL_EXISTS',
        'XENSERVER_PIF_UNMANAGED',
        'XENSERVER_PIF_VLAN_EXISTS',
        'XENSERVER_PIF_VLAN_STILL_EXISTS',
        'XENSERVER_POOL_AUTH_ALREADY_ENABLED',
        'XENSERVER_POOL_AUTH_DISABLE_FAILED',
        'XENSERVER_POOL_AUTH_DISABLE_FAILED_PERMISSION_DENIED',
        'XENSERVER_POOL_AUTH_DISABLE_FAILED_WRONG_CREDENTIALS',
        'XENSERVER_POOL_AUTH_ENABLE_FAILED',
        'XENSERVER_POOL_AUTH_ENABLE_FAILED_DOMAIN_LOOKUP_FAILED',
        'XENSERVER_POOL_AUTH_ENABLE_FAILED_DUPLICATE_HOSTNAME',
        'XENSERVER_POOL_AUTH_ENABLE_FAILED_INVALID_ID',
        'XENSERVER_POOL_AUTH_ENABLE_FAILED_PERMISSION_DENIED',
        'XENSERVER_POOL_AUTH_ENABLE_FAILED_WRONG_CREDENTIALS',
        'XENSERVER_POOL_JOINING_EXTERNAL_AUTH_MISMATCH',
        'XENSERVER_POOL_JOINING_HOST_MUST_HAVE_PHYSICAL_MANAGEMENT_NIC',
        'XENSERVER_POOL_JOINING_HOST_MUST_HAVE_SAME_PRODUCT_VERSION',
        'XENSERVER_PROVISION_FAILED_OUT_OF_SPACE',
        'XENSERVER_PROVISION_ONLY_ALLOWED_ON_TEMPLATE',
        'XENSERVER_RBAC_PERMISSION_DENIED',
        'XENSERVER_REDO_LOG_IS_ENABLED',
        'XENSERVER_RESTORE_INCOMPATIBLE_VERSION',
        'XENSERVER_RESTORE_SCRIPT_FAILED',
        'XENSERVER_RESTORE_TARGET_MGMT_IF_NOT_IN_BACKUP',
        'XENSERVER_RESTORE_TARGET_MISSING_DEVICE',
        'XENSERVER_ROLE_ALREADY_EXISTS',
        'XENSERVER_ROLE_NOT_FOUND',
        'XENSERVER_SESSION_INVALID',
        'XENSERVER_SESSION_NOT_REGISTERED',
        'XENSERVER_SLAVE_REQUIRES_MANAGEMENT_INTERFACE',
        'XENSERVER_SM_PLUGIN_COMMUNICATION_FAILURE',
        'XENSERVER_SR_ATTACH_FAILED',
        'XENSERVER_SR_BACKEND_FAILURE',
        'XENSERVER_SR_DEVICE_IN_USE',
        'XENSERVER_SR_FULL',
        'XENSERVER_SR_HAS_MULTIPLE_PBDS',
        'XENSERVER_SR_HAS_NO_PBDS',
        'XENSERVER_SR_HAS_PBD',
        'XENSERVER_SR_INDESTRUCTIBLE',
        'XENSERVER_SR_IS_CACHE_SR',
        'XENSERVER_SR_NOT_ATTACHED',
        'XENSERVER_SR_NOT_EMPTY',
        'XENSERVER_SR_NOT_SHARABLE',
        'XENSERVER_SR_OPERATION_NOT_SUPPORTED',
        'XENSERVER_SR_REQUIRES_UPGRADE',
        'XENSERVER_SR_UNKNOWN_DRIVER',
        'XENSERVER_SR_UUID_EXISTS',
        'XENSERVER_SR_VDI_LOCKING_FAILED',
        'XENSERVER_SSL_VERIFY_ERROR',
        'XENSERVER_SUBJECT_ALREADY_EXISTS',
        'XENSERVER_SUBJECT_CANNOT_BE_RESOLVED',
        'XENSERVER_SYSTEM_STATUS_MUST_USE_TAR_ON_OEM',
        'XENSERVER_SYSTEM_STATUS_RETRIEVAL_FAILED',
        'XENSERVER_TASK_CANCELLED',
        'XENSERVER_TOO_BUSY',
        'XENSERVER_TOO_MANY_PENDING_TASKS',
        'XENSERVER_TOO_MANY_STORAGE_MIGRATES',
        'XENSERVER_TRANSPORT_PIF_NOT_CONFIGURED',
        'XENSERVER_UNKNOWN_BOOTLOADER',
        'XENSERVER_USER_IS_NOT_LOCAL_SUPERUSER',
        'XENSERVER_V6D_FAILURE',
        'XENSERVER_VALUE_NOT_SUPPORTED',
        'XENSERVER_VBD_CDS_MUST_BE_READONLY',
        'XENSERVER_VBD_IS_EMPTY',
        'XENSERVER_VBD_NOT_EMPTY',
        'XENSERVER_VBD_NOT_REMOVABLE_MEDIA',
        'XENSERVER_VBD_NOT_UNPLUGGABLE',
        'XENSERVER_VBD_TRAY_LOCKED',
        'XENSERVER_VDI_CONTAINS_METADATA_OF_THIS_POOL',
        'XENSERVER_VDI_INCOMPATIBLE_TYPE',
        'XENSERVER_VDI_IN_USE',
        'XENSERVER_VDI_IS_A_PHYSICAL_DEVICE',
        'XENSERVER_VDI_IS_NOT_ISO',
        'XENSERVER_VDI_LOCATION_MISSING',
        'XENSERVER_VDI_MISSING',
        'XENSERVER_VDI_NEEDS_VM_FOR_MIGRATE',
        'XENSERVER_VDI_NOT_AVAILABLE',
        'XENSERVER_VDI_NOT_IN_MAP',
        'XENSERVER_VDI_NOT_MANAGED',
        'XENSERVER_VDI_NOT_SPARSE',
        'XENSERVER_VDI_READONLY',
        'XENSERVER_VDI_TOO_SMALL',
        'XENSERVER_VGPU_TYPE_NOT_COMPATIBLE_WITH_RUNNING_TYPE',
        'XENSERVER_VGPU_TYPE_NOT_ENABLED',
        'XENSERVER_VGPU_TYPE_NOT_SUPPORTED',
        'XENSERVER_VIF_IN_USE',
        'XENSERVER_VLAN_TAG_INVALID',
        'XENSERVER_VMPP_ARCHIVE_MORE_FREQUENT_THAN_BACKUP',
        'XENSERVER_VMPP_HAS_VM',
        'XENSERVER_VMS_FAILED_TO_COOPERATE',
        'XENSERVER_VM_ASSIGNED_TO_PROTECTION_POLICY',
        'XENSERVER_VM_ATTACHED_TO_MORE_THAN_ONE_VDI_WITH_TIMEOFFSET_MARKED_AS_RESET_ON_BOOT',
        'XENSERVER_VM_BAD_POWER_STATE',
        'XENSERVER_VM_BIOS_STRINGS_ALREADY_SET',
        'XENSERVER_VM_CANNOT_DELETE_DEFAULT_TEMPLATE',
        'XENSERVER_VM_CHECKPOINT_RESUME_FAILED',
        'XENSERVER_VM_CHECKPOINT_SUSPEND_FAILED',
        'XENSERVER_VM_CRASHED',
        'XENSERVER_VM_DUPLICATE_VBD_DEVICE',
        'XENSERVER_VM_FAILED_SHUTDOWN_ACKNOWLEDGMENT',
        'XENSERVER_VM_HALTED',
        'XENSERVER_VM_HAS_CHECKPOINT',
        'XENSERVER_VM_HAS_PCI_ATTACHED',
        'XENSERVER_VM_HAS_TOO_MANY_SNAPSHOTS',
        'XENSERVER_VM_HAS_VGPU',
        'XENSERVER_VM_HOST_INCOMPATIBLE_VERSION',
        'XENSERVER_VM_HVM_REQUIRED',
        'XENSERVER_VM_INCOMPATIBLE_WITH_THIS_HOST',
        'XENSERVER_VM_IS_PART_OF_AN_APPLIANCE',
        'XENSERVER_VM_IS_PROTECTED',
        'XENSERVER_VM_IS_TEMPLATE',
        'XENSERVER_VM_LACKS_FEATURE_SHUTDOWN',
        'XENSERVER_VM_LACKS_FEATURE_SUSPEND',
        'XENSERVER_VM_LACKS_FEATURE_VCPU_HOTPLUG',
        'XENSERVER_VM_MEMORY_SIZE_TOO_LOW',
        'XENSERVER_VM_MIGRATE_FAILED',
        'XENSERVER_VM_MISSING_PV_DRIVERS',
        'XENSERVER_VM_NOT_RESIDENT_HERE',
        'XENSERVER_VM_NO_CRASHDUMP_SR',
        'XENSERVER_VM_NO_SUSPEND_SR',
        'XENSERVER_VM_NO_VCPUS',
        'XENSERVER_VM_OLD_PV_DRIVERS',
        'XENSERVER_VM_REBOOTED',
        'XENSERVER_VM_REQUIRES_GPU',
        'XENSERVER_VM_REQUIRES_IOMMU',
        'XENSERVER_VM_REQUIRES_NETWORK',
        'XENSERVER_VM_REQUIRES_SR',
        'XENSERVER_VM_REQUIRES_VDI',
        'XENSERVER_VM_REQUIRES_VGPU',
        'XENSERVER_VM_REVERT_FAILED',
        'XENSERVER_VM_SHUTDOWN_TIMEOUT',
        'XENSERVER_VM_SNAPSHOT_WITH_QUIESCE_FAILED',
        'XENSERVER_VM_SNAPSHOT_WITH_QUIESCE_NOT_SUPPORTED',
        'XENSERVER_VM_SNAPSHOT_WITH_QUIESCE_PLUGIN_DEOS_NOT_RESPOND',
        'XENSERVER_VM_SNAPSHOT_WITH_QUIESCE_TIMEOUT',
        'XENSERVER_VM_TOO_MANY_VCPUS',
        'XENSERVER_VM_TO_IMPORT_IS_NOT_NEWER_VERSION',
        'XENSERVER_VM_UNSAFE_BOOT',
        'XENSERVER_WLB_AUTHENTICATION_FAILED',
        'XENSERVER_WLB_CONNECTION_REFUSED',
        'XENSERVER_WLB_CONNECTION_RESET',
        'XENSERVER_WLB_DISABLED',
        'XENSERVER_WLB_INTERNAL_ERROR',
        'XENSERVER_WLB_MALFORMED_REQUEST',
        'XENSERVER_WLB_MALFORMED_RESPONSE',
        'XENSERVER_WLB_NOT_INITIALIZED',
        'XENSERVER_WLB_TIMEOUT',
        'XENSERVER_WLB_UNKNOWN_HOST',
        'XENSERVER_WLB_URL_INVALID',
        'XENSERVER_WLB_XENSERVER_AUTHENTICATION_FAILED',
        'XENSERVER_WLB_XENSERVER_CONNECTION_REFUSED',
        'XENSERVER_WLB_XENSERVER_MALFORMED_RESPONSE',
        'XENSERVER_WLB_XENSERVER_TIMEOUT',
        'XENSERVER_WLB_XENSERVER_UNKNOWN_HOST',
        'XENSERVER_XAPI_HOOK_FAILED',
        'XENSERVER_XENAPI_MISSING_PLUGIN',
        'XENSERVER_XENAPI_PLUGIN_FAILURE',
        'XENSERVER_XEN_VSS_REQ_ERROR_ADDING_VOLUME_TO_SNAPSET_FAILED',
        'XENSERVER_XEN_VSS_REQ_ERROR_CREATING_SNAPSHOT',
        'XENSERVER_XEN_VSS_REQ_ERROR_CREATING_SNAPSHOT_XML_STRING',
        'XENSERVER_XEN_VSS_REQ_ERROR_INIT_FAILED',
        'XENSERVER_XEN_VSS_REQ_ERROR_NO_VOLUMES_SUPPORTED',
        'XENSERVER_XEN_VSS_REQ_ERROR_PREPARING_WRITERS',
        'XENSERVER_XEN_VSS_REQ_ERROR_PROV_NOT_LOADED',
        'XENSERVER_XEN_VSS_REQ_ERROR_START_SNAPSHOT_SET_FAILED',
        'XENSERVER_XMLRPC_UNMARSHAL_FAILURE',

        'VM_DISK_ALREADY_OPEN_ERROR',							// 虚拟磁盘已经被打开
        'VM_DISK_NOT_OPEN_ERROR',								// 磁盘没有被打开
        'VM_SNAPSHOT_NOT_EXIST_ERROR',							// 快照不存在
        'VM_QCOW_SNAPSHOT_ID_TOO_LONG_ERROR',					// 快照id或者名称太长
        'VM_DISK_OP_NOT_SUPPORT_ERROR',							// 不支持的虚拟磁盘类型
        'VM_DISK_CLUSTER_TYPE_UNEXCEPTED_ERROR',				// 检测到位置的簇类型
        'VM_DISK_OPEN_MODE_ERROR',								// 虚拟磁盘打开模式不匹配
        'VM_DISK_DRIVER_NOT_SUPPORT_META_OPERATION',			// 当前磁盘不支持相应的元数据请求

        'VM_OVIRT_HTTP_POST_ERROR',								// HTTP POST 错误
        'VM_CREATE_SNAPSHOT_TIMEOUT',							// 创建快照超时
        'VM_OVIRT_OPERATION_TIME_OUT_ERROR',					// 执行OVIRT操作超时
        'VM_HYPERVISOR_TYPE_NOT_COMPATIBLE_ERROR',				// 虚拟化类型不匹配

        'KVM_SANGFOR_FIND_PUBKEY_ERROR',						// 获取系统验证公钥失败
        'KVM_SANGFOR_RSA_GET_PUBLICKEY_ERROR',					// 解析验证公钥失败
        'KVM_SANGFOR_INIT_ENCRYPT_ERROR',						// 初始化加密对象失败
        'KVM_SANGFOR_BIO_PUBKEY_ERROR',							// 公约写入内存流失败
        'KVM_SANGFOR_ENCRYPT_PASSWORD_ERROR',					// 加密系统验证信息失败
        'KVM_SANGFOR_LOGIN_READ_CSRFPTOKEN_EORROR',				// 获取会话token失败
        'KVM_SANGFOR_LOGIN_MODIFY_HEADER_EORROR',				// 修改请求头cookie及会话token失败
        'KVM_SANGFOR_LOGIN_GET_VMJSONLIST_EORROR',				// 将虚拟机信息转换为json字符串失败
        'KVM_SANGFOR_LOGIN_GET_HOSTJSONLIST_EORROR',			// 将主机信息转换为json字符串失败
        'KVM_SANGFOR_VM_POWEROFFBYUUID_EORROR',					// 关闭虚拟机失败
        'KVM_SANGFOR_VM_POWERONBYUUID_EORROR',					// 开启虚拟机失败
        'KVM_SANGFOR_STRING_TO_JSON_ERROR',						// 解析json字符串失败
        'KVM_SANGFOR_PHYSICALNETWORLK_GETLIST_EORROR',			// 获取物理网卡列表失败
        'KVM_SANGFOR_PHYSICALNETWORLK_GETBYUUID_EORROR',		// 通过主机id和网络id获取网络信息失败
        'KVM_SANGFOR_HOST_GETINFO_BYUUID_ERROR',				// 通过主机id获取主机信息失败
        'KVM_SANGFOR_VM_GETINFO_BYUUID_ERROR',					// 通过虚拟机id获取虚拟机信息失败
        'KVM_SANGFOR_VM_DOBACKUP_EORROR',						// 通过虚拟机id备份虚拟机失败
        'KVM_SANGFOR_STORAGEPOOL_GETINFOBYUUID_ERROR',			// 通过存储池id获取存储池信息失败
        'KVM_SANGFOR_VM_GETSNAPSHOT_BYUUID_ERROR',				// 获取虚拟机快照列表失败
        'KVM_SANGFOR_VM_DELETESNAPSHOT_ERROR',					// 删除虚拟机快照失败
        'KVM_SANGFOR_STORAGEPOOL_BYHOSTUUID_ERROR',				// 获取主机上的存储池列表失败
        'KVM_SANGFOR_STORAGEPOOL_LIST_ERROR', 					// 获取存储池列表失败KvmSangfor Error: get storage pool list failed
        'KVM_SANGFOR_STORAGEPOOL_DETAIL_ERROR',					// 通过存储池id获取存储池详细信息失败KvmSangfor Error: get storage pool detail information by uuid failed
        'KVM_SANGFOR_VM_DELETE_ERROR',							// 删除虚拟机失败
        'KVM_SANGFOR_VM_CREATE_ERROR', 							// 创建虚拟机失败

        'KVM_OPENSTACK_GENERAL_STRINGTOJSON_ERROR',				// OpenStack Error: translate string to json error
        'KVM_OPENSTACK_LOGIN_GET_UNSCOPEDTOKENID_ERROR',		// OpenStack Error: get unscoped token id from json value error
        'KVM_OPENSTACK_MODIFY_HEADER_EORROR',					// OpenStack Error: modify header add auth token id string fail
        'KVM_OPENSTACK_LOGIN_GET_TENANTNAME_ERROR',				// OpenStack Error: get user tenant name fail
        'KVM_SANGFOR_LOGIN_ERROR',								// KvmSangfor Error: KVM sangfor login error
        'XENSERVER_VM_HAS_SNAPSHOT_MERGE_OPERATION',            // Current Vm Has a running snapshot merge operation



        'XENSERVER_PIF_IP_IS_EMPTY_ERROR',						// there is not exist valid ip address

        // --------- kvinfs new error code --------------------------------
        'KVINFS_TASK_NOT_FOUND_ERROR',							// kvinfs task not found error
        'KVINFS_FILTER_DISK_ALREADY_EXIST_ERROR',					// filter disk alreay exist in hash map error
        'KVINFS_CREATE_CACHE_DIR_ERROR',							// create cache dir error
        'KVINFS_FUSE_UNEXPECTED_READ_REQUEST_ERROR',				// unexpected read request error
        'KVINFS_FILTER_DISK_NOT_EXIST_ERROR',						// filter disk not exist error

        'VM_CREATE_VM_TIMEOUT_ERROR',								// create vm timeout error
        // --------- openstack error code --------------------------------
        'KVM_OPENSTACK_GET_VM_INFO_BYUUID_ERROR',					// KvmOpenStack Error: get vm info by vm uuid error
        'KVM_OPENSTACK_GET_FLAVOR_INFO_BYUUID_ERROR',				// KvmOpenStack Error: get flavor info by flavor uuid error
        'KVM_OPENSTACK_GET_ALL_TENANTS_ERROR',					// KvmOpenStack Error: get all tenants info error
        'KVM_OPENSTACK_GET_USER_ON_TENANT_ERROR',					// KvmOpenstack Error: get user on a tenant error

        // --------- kvm sangfor error code --------------------------------
        'KVM_SANGFOR_GET_VMS_INFO_ERROR',							// KvmSangfor Error: get vm info by uuid fail
        'KVM_SANGFOR_POWEROFF_VM_TIMEOUT',						// KvmSangfor Error: power off vm timeout
        'KVM_SANGFOR_CREATE_VM_TIMEOUT',							// KvmSangfor Error: create vm timeout
        'KVM_SANGFOR_DELETE_VM_TIMEOUT',							// KvmSangfor Error: delete vm timeout

        // --------- vmware error code
        'VMWARE_HAS_NO_UNUSED_PORT_ERROR',						// vmware need a available port	to start backup server
        'VMWARE_START_BACKUP_SERVER_ERROR',						// vmware start backup server error
        'VMWARE_CONNECT_TO_BACKUP_SERVER_ERROR',					// vmware connect to backup server error
        'VMWARE_RECEIVE_BACKUP_SERVER_MSG_ERROR',					// vmware receive msg from backup server error
        'VMWARE_SEND_ACK_TO_VM_SERVER_ERROR',						// vmware send ack message to vm server error
        'VMWARE_BACKUP_SERVER_AREADY_EXIST_ERROR',				// vmware backup_server already exist error
        'VMWARE_GET_VCENTER_VERSION_ERROR',						// vmware get vcenter version error
        'VMWARE_PORT_IS_USING_BY_BACKUP_SERVER_ERROR',			// vmware port is using by backup_server error
        'VMWARE_PORT_IS_NOT_USING_BY_BACKUP_SERVER_ERROR',		// vmware port is not using by backup_server error

        'KVM_INSTANT_RECOVERY_KVINFS_NFS_STORAGE_NOT_EXIST_ERROR', // instant recovery nfs storage not exist error
        'KVM_INSTANT_RECOVERY_KVINFS_NFS_STORAGE_BROKEN_ERROR',	// instant recovery nfs storage is broken error

        'KVM_OPENSTACK_GET_NETWORKS_ERROR',                     // KvmOpenStack Error: get networks info fail
        'KVM_OPENSTACK_CREATE_IMAGE_ENTRY_ERROR',               // KvmOpenStack Error: create image entry error
        'KVM_OPENSTACK_UPLOAD_IMAGE_ERROR',                     // KvmOpenStack Error: upload image fail
        'KVM_OPENSTACK_DELETE_IMAGE_ERROR',                     // KvmOpenStack Error: delete image entry and data fail
        'KVM_OPENSTACK_CREATE_FLAVOR_ERROR',                    // KvmOpenStack Error: create flavor fail
        'KVM_OPENSTACK_DELETE_FLAVOR_ERROR',                    // KvmOpenStack Error: delete flavor fail
        'KVM_OPENSTACK_CREATE_VM_ERROR',                        // KvmOpenStack Error: create vm fail
        'KVM_OPENSTACK_CREATE_VM_TIMEOUT',                      // KvmOpenStack Error: create vm timeout
        'KVM_OPENSTACK_GET_IMAGE_INFO_BYUUID_ERROR',            // KvmOpenStack Error: get image info by uuid fail
        'KVM_OPENSTACK_CREATE_IMAGE_TIMEOUT',                   // KvmOpenStack Error: create image time out
        'KVM_OPENSTACK_DELETE_VM_ERROR',                        // KvmOpenStack Error: delete vm by uuid fail
        'KVM_OPENSTACK_GET_HOSTIP_BY_NAME_ERROR',				// KvmOpenStack Error: get host ip by name fail

        'KVM_OPENSTACK_RESET_HEADER_EORROR',                      // KvmOpenStack Error: reset http header fail
        'KVM_OPENSTACK_GET_SCOPEDTOKENID_ERROR',                  // KvmOpenStack Error: get scoped token id error
        'KVM_OPENSTACK_GET_VM_INFO_ERROR',                        // KvmOpenStack Error: get all vm detail info error
        'KVM_OPENSTACK_SERVICE_NOT_EXIST',                        // KvmOpenStack Error: openstack service not exist
        'KVM_OPENSTACK_GET_HOSTS_INFO_ERROR',                     // KvmOpenStack Error: get all host info error
        'KVM_OPENSTACK_GET_VM_TENANTID_ERROR',                    // KvmOpenStack Error: get vm tenant id by vm uuid error
        'KVM_OPENSTACK_GET_VM_NAME_ERROR',                        // KvmOpenStack Error: get vm name by vm uuid error
        'KVM_OPENSTACK_GET_VM_STATE_ERROR',	                      // KvmOpenStack Error: get vm state by vm uuid error
        'KVM_OPENSTACK_REFRESH_TOKENID_ERROR',                    // KvmOpenStack Error: refresh token id error
        'KVM_OPENSTACK_GET_VM_INFO_BY_HOSTNAME_ERROR',            // KvmOpenStack Error: get all vm info belong to a same host
        'KVM_OPENSTACK_FIND_ADMIN_ROLE_ERROR',                    // KvmOpenStack Error: find user admin role error, not enough authority
        'KVM_OPENSTACK_VM_POWEROFF_ERROR',                        // KvmOpenStack Error: power off vm failed
        'KVM_OPENSTACK_VM_POWERON_ERROR',                         // KvmOpenStack Error: power on vm failed
        'KVM_OPENSTACK_GET_ALLTENANTS_ERROR',                     // KvmOpenStack Error: get all tenants information failed" }
        'KVM_OPENSTACK_GET_VM_INFO_BY_TENANTID_ERROR',            // KvmOpenStack Error: get all vm info belong to a same tenant
        'KVM_SANGFOR_NFSPOOL_CREATE_ERROR',                       // KvmSangfor Error: create nfs storage pool error
        'KVM_SANGFOR_NFSPOOL_CREATE_TIMEOUT',                     // KvmSangfor Error: create nfs storage pool time out
        'KVM_SANGFOR_STORAGE_DELETE_ERROR',                       // KvmSangfor Error: delete storage by uuid failed

        'VM_OPERATION_TIMEOUT_ERROR',                             //do operation timeout

        'KVM_SANGFOR_GET_VM_SNAPSHOT_LIST_ERROR',				  // KvmSangfor Error: get vm snapshot list error

        // --------- kvm h3c error code --------------------------------
        'KVM_H3C_SCAN_HOSTPOOL_ERROR',                            // KvmH3C Error: scan host pool error
        'KVM_H3C_SCAN_CLUSTER_ERROR',                             // KvmH3C Error: scan cluster error
        'KVM_H3C_SCAN_HOST_ERROR',                                // KvmH3C Error: scan host error
        'KVM_H3C_VM_GETINFO_BYUUID_ERROR',                        // KvmH3C Error: get vm info by uuid error
        'KVM_H3C_HOST_GETINFO_BYUUID_ERROR',                      // KvmH3C Error: get host info by uuid error
        'KVM_H3C_CLUSTER_GETINFO_BYUUID_ERROR',                   // KvmH3C Error: get cluster info by uuid error
        'KVM_H3C_POOL_GETINFO_BYUUID_ERROR',                      // KvmH3C Error: get host pool info by uuid error
        'KVM_H3C_STRING_TO_JSON_ERROR',                           // KvmH3C Error: translate json to string error
        'KVM_H3C_HOST_GETNETWORK_BYUUID_ERROR',                   // KvmH3C Error: get host network by uuid error
        'KVM_H3C_EMPTY_INFO_ERROR',                               // KvmH3C Error: get empty info error
        'KVM_H3C_POWEROFF_VM_ERROR',                              // KvmH3C Error: poweroff vm error
        'KVM_H3C_POWERON_VM_ERROR',                               // KvmH3C Error: poweron vm error
        'KVM_H3C_CREATE_VM_ERROR',                                // KvmH3C Error: create vm error
        'KVM_H3C_DELETE_VM_ERROR',                                // KvmH3C Error: delete vm error
        'KVM_H3C_SNAPSHORT_CREAT_EEROR',                          // KvmH3C Error: create vm snapshot error
        'KVM_H3C_NFSPOOL_CREATE_TIMEOUT',                         // KvmH3C Error: create nfs pool timeout
        'KVM_H3C_MONITORSTASTICS_GET_ERROR',                      // KvmH3C Error: get vm monitor stastics error
        'KVM_H3C_STORAGEPOOL_GET_ERROR',                          // KvmH3C Error: get host storage pool list error
        'KVM_H3C_SNAPSHOTLIST_GET_EERROR',                        // KvmH3C Error: get vm snapshot list error
        'KVM_H3C_SNAPSHOT_DELETE_EERROR',                         // KvmH3C Error: delete snapshot error

        'KVM_OPENSTACK_GET_ALL_VOLUMES_INFO_ERROR',				  // KvmOpenStack Error: get all volumes info error
        'KVM_OPENSTACK_GET_VOLUME_SIZE_BY_UUID_ERROR',			  // KvmOpenStack Error: get volume size by uuid error
        'KVM_SANGFOR_GET_PROCESS_STATUS_ERROR',					  // KvmSangfor Error: get process status by upid error
        'KVM_SANGFOR_POWERON_VM_TIMEOUT',						  // KvmSangfor Error: power on vm by uuid time out
        'KVM_SANGFOR_DELETE_SNAPSHOT_TIMEOUT',					  // KvmSangfor Error: delete snapshot time out
        'KVM_SANGFOR_DELETE_STORAGE_POOL_TIMEOUT',				  // KvmSangfor Error: delete storage pool time out

        'KVM_H3C_VOLUME_CREATE_EERROR',                           // KvmH3C Error: create volume error
        'KVM_SANGFOR_CREATE_VM_SNAPSHOT_ERROR',					  // KvmSangfor Error: create vm snapshot error
        'VMWARE_HAS_DIFF_TIMEPOINT_EXIST_ERROR',					// vmware has diff timepoint exist error
        'VMWARE_DIFF_AND_INC_CAN_NOT_COEXIST_ERROR',				// vmware diff and inc can not coexist error

        'VMWARE_BUILD_EXPORT_VM_TIMEPOINT_LIST_ERROR',				// vmware build export vm timepoint list error
        'VMWARE_GET_EXPORT_VALID_DATA_SIZE_ERROR',					// vmware get export valid data size error

        'KVM_OVIRT_SNAPSHOT_NOT_CONTAINS_ALL_DISKS_ERROR', 			// some disks attached to vm not snapshoted
        'KVM_OVIRT_SNAPSHOT_HAVE_LOCKED_ERROR', 					// some operation locked vm's snapshot operation, may be snapshot merging
        'VMWARE_BUILD_METADATA_FILE_FOR_FULL_BACKUP_ERROR',


        //******new******//
        'VMWARE_VDDK_SERVER_REFUSED_CONNECTION_ERROR',			// vmware server refused connection error (== VIX_E_HOST_NETWORK_CONN_REFUSED)
        'VMWARE_VDDK_HOST_TCP_CONN_LOST_ERROR',					// vmware host tcp connection was lost error (== VIX_E_HOST_NETWORK_CONN_REFUSED)

        'KVM_OPENSTACK_CREATE_VOLUME_ERROR',						// KvmOpenStack Error: create openstack volume error
        'KVM_OPENSTACK_GET_VOULUME_INFO_ERROR',					// KvmOpenStack Error: get openstack volume info failed
        'KVM_OPENSTACK_CREATE_VOLUME_TIME_OUT',					// KvmOpenStack Error: create openstack volume timeout
        'KVM_OPENSTACK_ATTACH_VOLUME_TO_VM_ERROR',				// KvmOpenStack Error: attach volume to vm error
        'KVM_OPENSTACK_DELETE_VOLUME_ERROR',						// KvmOpenStack Error: delete volume error
        'KVM_OPENSTACK_GET_ALL_BACKEND_POOLS_ERROR',				// KvmOpenStack Error: get all back_end storage pools error
        'KVM_OPENSTACK_GET_ALL_VOLUME_TYPES_ERROR',				// KvmOpenStack Error: get all volume types error
        'KVM_OPENSTACK_SET_VOLUME_BOOTABLE_ERROR',				// KvmOpenStack Error: set openstack volume bootable error

        // --------- osvinfs new error code --------------------------------
        'OSVINFS_TASK_NOT_FOUND_ERROR',							// osvinfs task not found error
        'OSVINFS_FILTER_DISK_ALREADY_EXIST_ERROR',				// filter disk alreay exist in hash map error
        'OSVINFS_CREATE_CACHE_DIR_ERROR',							// create cache dir error
        'OSVINFS_FUSE_UNEXPECTED_READ_REQUEST_ERROR',				// unexpected read request error
        'OSVINFS_FILTER_DISK_NOT_EXIST_ERROR',					// filter disk not exist error
        'KVM_INSTANT_RECOVERY_OSVINFS_NFS_STORAGE_NOT_EXIST_ERROR', // instant recovery nfs storage not exist error
        'KVM_INSTANT_RECOVERY_OSVINFS_NFS_STORAGE_BROKEN_ERROR',	// instant recovery nfs storage is broken error

        'KVM_OPENSTACK_GET_VOULUMES_INFO_ERROR',					// KvmOpenStack Error: get openstack all volumes info failed
        'KVM_OPENSTACK_GET_STORAGE_POOL_INFO_ERROR',				// KvmOpenStack Error: get openstack volume storage pool info failed
        'KVM_OPENSTACK_DELETE_VM_TIMEOUT',						// KvmOpenStack Error: delete vm time out
        'KVM_OPENSTACK_DELETE_VOLUME_TIMEOUT',					// KvmOpenStack Error: delete volume time out

        'VMWARE_TASK_NOT_ENABLE_CBT_ERROR',                     //vmware task not enable cbt error

        'VM_STORAGE_TYPE_NOT_SUPPORT_ERROR', 					//current backup system not support the storage type 
        'VM_VHD_BLOCK_LEN_INVALID_ERROR', 						//detected invalid block length in vhd 
        'VM_VHD_VDI_UUID_NOT_MATCH_WITH_OPEN_ONE_ERROR', 		//detected request packet's vdi uuid is not match with opened one 
        'XS_INIT_LANFREE_DRIVER_ERROR',							//initialize lanfree disk driver error 
        'XS_NOT_SUPPORT_BACKUP_LEVEL_ERROR', 					//not support backup level error
        'XS_SR_NOT_SUPPORT_ERROR', 								//unsupport SR type error
        'VM_VHD_DISK_PHYSICAL_LEN_INVALID_ERROR', 				//invalid block vhd size

        'KVM_OPENSTACK_ISO_ROOT_DISK_ERROR',    				//KvmOpenStack Error: root disk type is iso, current version not support
        'XS_NO_VALID_NBD_CONNECTION_INFO_FOUND_ERROR',			//could not found the valid nbd connection info
        'VM_GET_IP_BY_DISK_ERROR',								// can't get ip list by vm disk, ip list empty
        'VM_DISK_CLUSTER_LEN_INVALID_ERROR',					// disk cluster length invalid error
        'VM_DEE_IS_CHANGED_ERROR',								// deep and effective data extraction changed error
        'XS_VDI_IN_USE_ERROR',									//vdi in used error
        'XS_VDI_IS_NOT_ENABLED_CBT_ERROR',						//vdi is not enabled cbt, reset it and do full backup again
        'KVM_OPENSTACK_NO_USABLE_COMPUTE_IP_ERROR',             //KvmOpenStack Error: no usable compute ip for connect
        'KVM_OPENSTACK_NO_USABLE_CONTROLLER_IP_ERROR',          //KvmOpenStack Error: no usable controller ip for connect
        'KVM_H3C_GET_VESION_ERROR',                             //H3C get CVK version error


        /* Openstack Flex snapshot api */
        'KVM_OPENSTACK_FLEX_CREATE_VM_SNAPSHOT_ERROR',            //KvmOpenStack Error: create flex vm snapshot error
        'KVM_OPENSTACK_FLEX_CREATE_VOLUME_SNAPSHOT_ERROR',        //KvmOpenStack Error: create flex volume snapshot error
        'KVM_OPENSTACK_FLEX_GET_VM_SNAPSHOT_STATUS_ERROR',        //KvmOpenStack Error: get flex vm snapshot status error
        'KVM_OPENSTACK_FLEX_GET_VOLUME_SNAPSHOT_STATUS_ERROR',    //KvmOpenStack Error: get flex volume snapshot status error
        'KVM_OPENSTACK_FLEX_DELETE_VM_SNAPSHOT_ERROR',            //KvmOpenStack Error: delete flex vm snapshot error
        'KVM_OPENSTACK_FLEX_DELETE_VOLUME_SNAPSHOT_ERROR',        //KvmOpenStack Error: delete flex volume snapshot error
        'KVM_H3C_GET_PORTPROFILE_ERROR',							// KvmH3C Error: all port profile error
        'KVM_H3C_NO_MACTHED_PORTPROFILE_ERROR',					// KvmH3C Error: get no matched portprofile error 
        'VM_OPENSTACK_CONTROLLER_TEST_CONNECT_ERROR',           // test openstack controller connection error. 1. please check the ip is correct or not; 2. please check is the iptable filter is block the port 37031; 3. please check the instant recovery controller patch is installed or not(can be download from login page).

        'KVM_OPENSTACK_KEYSTONE_VERSION_UNSUPPORT_ERROR',			// KvmOpenStack Error: keystone version not support
        'KVM_OPENSTACK_MODIFY_PORT_MACADDR_ERROR',					// KvmOpenStack Error: modify openstack port mac address error
        'KVM_OPENSTACK_GET_VM_PORT_INFO_BYUUID_ERROR',				// KvmOpenStack Error: get openstack vm port info by vm uuid error
        'KVM_OPENSTACK_CREATE_NEUTRON_PORT_ERROR',					// KvmOpenStack Error: create openstack neutron port error
        'KVM_OPENSTACK_DELETE_VM_PORT_BYUUID_ERROR',				// KvmOpenStack Error: delete openstack vm port by uuid error

        // add xenserver sr backen failed
        'XS_SR_BACKEND_ERR_1',
        'XS_SR_BACKEND_ERR_100',
        'XS_SR_BACKEND_ERR_101',
        'XS_SR_BACKEND_ERR_102',
        'XS_SR_BACKEND_ERR_103',
        'XS_SR_BACKEND_ERR_104',
        'XS_SR_BACKEND_ERR_105',
        'XS_SR_BACKEND_ERR_106',
        'XS_SR_BACKEND_ERR_107',
        'XS_SR_BACKEND_ERR_108',
        'XS_SR_BACKEND_ERR_109',
        'XS_SR_BACKEND_ERR_110',
        'XS_SR_BACKEND_ERR_111',
        'XS_SR_BACKEND_ERR_112',
        'XS_SR_BACKEND_ERR_113',
        'XS_SR_BACKEND_ERR_114',
        'XS_SR_BACKEND_ERR_115',
        'XS_SR_BACKEND_ERR_116',
        'XS_SR_BACKEND_ERR_120',
        'XS_SR_BACKEND_ERR_1200',
        'XS_SR_BACKEND_ERR_121',
        'XS_SR_BACKEND_ERR_122',
        'XS_SR_BACKEND_ERR_123',
        'XS_SR_BACKEND_ERR_124',
        'XS_SR_BACKEND_ERR_125',
        'XS_SR_BACKEND_ERR_126',
        'XS_SR_BACKEND_ERR_127',
        'XS_SR_BACKEND_ERR_128',
        'XS_SR_BACKEND_ERR_129',
        'XS_SR_BACKEND_ERR_130',
        'XS_SR_BACKEND_ERR_131',
        'XS_SR_BACKEND_ERR_132',
        'XS_SR_BACKEND_ERR_133',
        'XS_SR_BACKEND_ERR_134',
        'XS_SR_BACKEND_ERR_135',
        'XS_SR_BACKEND_ERR_140',
        'XS_SR_BACKEND_ERR_141',
        'XS_SR_BACKEND_ERR_142',
        'XS_SR_BACKEND_ERR_143',
        'XS_SR_BACKEND_ERR_144',
        'XS_SR_BACKEND_ERR_150',
        'XS_SR_BACKEND_ERR_151',
        'XS_SR_BACKEND_ERR_152',
        'XS_SR_BACKEND_ERR_153',
        'XS_SR_BACKEND_ERR_16',
        'XS_SR_BACKEND_ERR_160',
        'XS_SR_BACKEND_ERR_161',
        'XS_SR_BACKEND_ERR_162',
        'XS_SR_BACKEND_ERR_163',
        'XS_SR_BACKEND_ERR_164',
        'XS_SR_BACKEND_ERR_165',
        'XS_SR_BACKEND_ERR_166',
        'XS_SR_BACKEND_ERR_167',
        'XS_SR_BACKEND_ERR_168',
        'XS_SR_BACKEND_ERR_169',
        'XS_SR_BACKEND_ERR_170',
        'XS_SR_BACKEND_ERR_171',
        'XS_SR_BACKEND_ERR_172',
        'XS_SR_BACKEND_ERR_173',
        'XS_SR_BACKEND_ERR_174',
        'XS_SR_BACKEND_ERR_175',
        'XS_SR_BACKEND_ERR_176',
        'XS_SR_BACKEND_ERR_180',
        'XS_SR_BACKEND_ERR_181',
        'XS_SR_BACKEND_ERR_19',
        'XS_SR_BACKEND_ERR_2',
        'XS_SR_BACKEND_ERR_20',
        'XS_SR_BACKEND_ERR_200',
        'XS_SR_BACKEND_ERR_201',
        'XS_SR_BACKEND_ERR_202',
        'XS_SR_BACKEND_ERR_203',
        'XS_SR_BACKEND_ERR_220',
        'XS_SR_BACKEND_ERR_221',
        'XS_SR_BACKEND_ERR_222',
        'XS_SR_BACKEND_ERR_223',
        'XS_SR_BACKEND_ERR_224',
        'XS_SR_BACKEND_ERR_225',
        'XS_SR_BACKEND_ERR_226',
        'XS_SR_BACKEND_ERR_227',
        'XS_SR_BACKEND_ERR_228',
        'XS_SR_BACKEND_ERR_24',
        'XS_SR_BACKEND_ERR_37',
        'XS_SR_BACKEND_ERR_38',
        'XS_SR_BACKEND_ERR_39',
        'XS_SR_BACKEND_ERR_40',
        'XS_SR_BACKEND_ERR_400',
        'XS_SR_BACKEND_ERR_401',
        'XS_SR_BACKEND_ERR_402',
        'XS_SR_BACKEND_ERR_41',
        'XS_SR_BACKEND_ERR_410',
        'XS_SR_BACKEND_ERR_411',
        'XS_SR_BACKEND_ERR_412',
        'XS_SR_BACKEND_ERR_413',
        'XS_SR_BACKEND_ERR_414',
        'XS_SR_BACKEND_ERR_416',
        'XS_SR_BACKEND_ERR_417',
        'XS_SR_BACKEND_ERR_418',
        'XS_SR_BACKEND_ERR_419',
        'XS_SR_BACKEND_ERR_42',
        'XS_SR_BACKEND_ERR_420',
        'XS_SR_BACKEND_ERR_421',
        'XS_SR_BACKEND_ERR_422',
        'XS_SR_BACKEND_ERR_423',
        'XS_SR_BACKEND_ERR_424',
        'XS_SR_BACKEND_ERR_425',
        'XS_SR_BACKEND_ERR_426',
        'XS_SR_BACKEND_ERR_427',
        'XS_SR_BACKEND_ERR_428',
        'XS_SR_BACKEND_ERR_429',
        'XS_SR_BACKEND_ERR_43',
        'XS_SR_BACKEND_ERR_430',
        'XS_SR_BACKEND_ERR_431',
        'XS_SR_BACKEND_ERR_432',
        'XS_SR_BACKEND_ERR_433',
        'XS_SR_BACKEND_ERR_434',
        'XS_SR_BACKEND_ERR_435',
        'XS_SR_BACKEND_ERR_436',
        'XS_SR_BACKEND_ERR_437',
        'XS_SR_BACKEND_ERR_438',
        'XS_SR_BACKEND_ERR_439',
        'XS_SR_BACKEND_ERR_44',
        'XS_SR_BACKEND_ERR_440',
        'XS_SR_BACKEND_ERR_441',
        'XS_SR_BACKEND_ERR_442',
        'XS_SR_BACKEND_ERR_443',
        'XS_SR_BACKEND_ERR_444',
        'XS_SR_BACKEND_ERR_445',
        'XS_SR_BACKEND_ERR_446',
        'XS_SR_BACKEND_ERR_447',
        'XS_SR_BACKEND_ERR_448',
        'XS_SR_BACKEND_ERR_449',
        'XS_SR_BACKEND_ERR_450',
        'XS_SR_BACKEND_ERR_451',
        'XS_SR_BACKEND_ERR_452',
        'XS_SR_BACKEND_ERR_453',
        'XS_SR_BACKEND_ERR_454',
        'XS_SR_BACKEND_ERR_455',
        'XS_SR_BACKEND_ERR_456',
        'XS_SR_BACKEND_ERR_457',
        'XS_SR_BACKEND_ERR_458',
        'XS_SR_BACKEND_ERR_459',
        'XS_SR_BACKEND_ERR_46',
        'XS_SR_BACKEND_ERR_460',
        'XS_SR_BACKEND_ERR_47',
        'XS_SR_BACKEND_ERR_48',
        'XS_SR_BACKEND_ERR_49',
        'XS_SR_BACKEND_ERR_50',
        'XS_SR_BACKEND_ERR_51',
        'XS_SR_BACKEND_ERR_52',
        'XS_SR_BACKEND_ERR_53',
        'XS_SR_BACKEND_ERR_54',
        'XS_SR_BACKEND_ERR_55',
        'XS_SR_BACKEND_ERR_56',
        'XS_SR_BACKEND_ERR_57',
        'XS_SR_BACKEND_ERR_58',
        'XS_SR_BACKEND_ERR_59',
        'XS_SR_BACKEND_ERR_60',
        'XS_SR_BACKEND_ERR_61',
        'XS_SR_BACKEND_ERR_62',
        'XS_SR_BACKEND_ERR_63',
        'XS_SR_BACKEND_ERR_64',
        'XS_SR_BACKEND_ERR_65',
        'XS_SR_BACKEND_ERR_66',
        'XS_SR_BACKEND_ERR_67',
        'XS_SR_BACKEND_ERR_68',
        'XS_SR_BACKEND_ERR_69',
        'XS_SR_BACKEND_ERR_70',
        'XS_SR_BACKEND_ERR_71',
        'XS_SR_BACKEND_ERR_72',
        'XS_SR_BACKEND_ERR_73',
        'XS_SR_BACKEND_ERR_74',
        'XS_SR_BACKEND_ERR_75',
        'XS_SR_BACKEND_ERR_76',
        'XS_SR_BACKEND_ERR_77',
        'XS_SR_BACKEND_ERR_78',
        'XS_SR_BACKEND_ERR_79',
        'XS_SR_BACKEND_ERR_80',
        'XS_SR_BACKEND_ERR_81',
        'XS_SR_BACKEND_ERR_82',
        'XS_SR_BACKEND_ERR_83',
        'XS_SR_BACKEND_ERR_84',
        'XS_SR_BACKEND_ERR_85',
        'XS_SR_BACKEND_ERR_86',
        'XS_SR_BACKEND_ERR_87',
        'XS_SR_BACKEND_ERR_88',
        'XS_SR_BACKEND_ERR_89',
        'XS_SR_BACKEND_ERR_90',
        'XS_SR_BACKEND_ERR_91',
        'XS_SR_BACKEND_ERR_92',
        'XS_SR_BACKEND_ERR_93',
        'XS_SR_BACKEND_ERR_94',
        'XS_SR_BACKEND_ERR_95',
        'XS_SR_BACKEND_ERR_96',
        'XS_SR_BACKEND_ERR_97',
        'XS_SR_BACKEND_ERR_98',
        'XS_SR_BACKEND_ERR_99',

        //******FC******//
        'FC_CONNECT_TO_FC_SERVER_ERROR',
        'FC_LOGIN_EEROR',
        'FC_INCORRECT_USR_OR_PWD_ERROR',
        'FC_SCAN_VCENTER_ERROR',
        'FC_BACKUP_VM_TOOLS_NOT_RUNNING_ERROR',
        'FC_NOT_SUPPORT_BACKUP_TEMPLATE_ERROR',
        'FC_GET_BACKUP_PREPARE_INFO_ERROR',
        'FC_GET_SNAPSHOT_TREE_ERROR',
        'FC_GET_SNAPSHOT_INFO_ERROR',
        'FC_INDEPENDENT_DISK_ERROR',
        'FC_CREATE_SNAPSHOT_ERROR',
        'FC_GET_SNAPSHOT_DISKS_ERROR',
        'FC_QUERY_CHANGED_DISK_INFO_ERROR',
        'FC_DELETE_SNAPSHOT_ERROR',
        'FC_DISK_NUM_CHANGED_ERROR',
        'FC_DISK_CHANGED_ERROR',
        'FC_DISK_IS_NOT_EXIST_ERROR',
        'FC_CBT_NOT_ENABLE_ERROR',
        'FC_GET_VM_INFO_ERROR',
        'FC_GET_VM_CONFIG_ERROR',
        'FC_CONNECT_TO_CNA_ERROR',
        'FC_READ_REMOTE_DISK_ERROR',
        'FC_GET_REMOTE_DISK_INFO_ERROR',
        'FC_CPP_SDK_INIT_ERROR',
        'FC_OPEN_REMOTE_DISK_ERROR',
        'FC_CLOSE_REMOTE_DISK_ERROR',
        'FC_MODIFY_BACKUP_RESOURCE_ERROR',
        'FC_CPP_SDK_VERIFY_LUN_ERROR',
        'FC_INIT_DISK_DRIVER_ERROR',
        'FC_BACKUP_CONTAINER_FULL_ERROR',
        'FC_DELETE_BACKUP_RESOURCE_ERROR',
        'FC_GET_DATASTORE_INFO_ERROR',
        'FC_DEPEND_SNAPSHOT_DISK_NOT_EXIST_ERROR',
        'FC_POWER_OFF_VM_ERROR',
        'FC_GET_HOST_DATASTORE_ERROR',
        'FC_GET_HOST_NETWORK_ERROR',
        'FC_CREATE_VM_ERROR',
        'FC_NOT_FIND_MATCHED_DATASTORE_ERROR',
        'FC_WRITE_REMOTE_DISK_ERROR',
        'FC_POWER_ON_VM_ERROR',
        'FC_DELETE_VM_ERROR',
        'FC_JAVA_SERVER_NOT_INIT_ERROR',
        'FC_GET_HOST_INFO_ERROR',
        'FC_CPP_SDK_ERROR',
        'FC_CHECK_NAS_EXITENCE_ERROR',
        'FC_CHECK_CBT_STATUS_ERROR',
        'FC_GET_VERSION_AND_LOGIN_LINK_ERROR',
        'FC_CONVERT_STRING_TO_JSON_ERROR',
        'FC_LOGIN_TO_VRM_BY_RESTFUL_API_ERROR',
        'FC_GET_AUTH_TOKEN_ERROR',
        'FC_GET_SITE_ID_ERROR',
        'FC_GET_TASK_STATUS_ERROR',
        'FC_CREATE_STORAGE_ERROR',
        'FC_GET_STORAGE_RESOURCE_ERROR',
        'FC_CREATE_NAS_DATASTORE_ERROR',
        'FC_CONNECT_STORAGE_RESOURCE_ERROR',
        'FC_GET_STORAGE_UINIT_ERROR',
        'FC_SCAN_VMS_OF_SCOPE_ERROR',
        'FC_GET_NAS_DATASTORE_DELETE_STATUS_ERROR',
        'FC_DISCONNECT_HOST_TO_DATASTORE_ERROR',
        'FC_DELETE_STORAGE_RESOURCE_ERROR',
        'FC_CONNECT_TO_FCVINFS_ERROR',
        'FC_DATASTORE_STATUS_ABNORMAL',
        'FC_REFRESH_STORAGE_UNIT_ERROR',
        'FC_CPPSDK_RESOURCE_UNVAILABLE_ERROR',
        'FC_CPPSDK_SYSTEM_INTERRUPT_ERROR',
        'FC_DISCONNECT_STORAGE_RESOURCE_ERROR',
        'FC_CHECK_SUPPORT_QUIESCE_SNAPSHOT_ERROR',
        'FC_NOT_SUPPORT_VERSION',
        'FC_OPERATE_BY_VRM_RESTFUL_API_ERROR',
        'FC_LOGIN_TO_VRM_BY_RESTFUL_API_TIMEOUT_ERROR',
        'FC_CHECK_LUN_ABILITY_ERROR',
        'FC_DATASTORE_IN_USE_ERROR',
        'FC_STORAGE_RESOURCE_IN_USE_ERROR',
        'FC_HIBERNATE_VM_ERROR',
        'FC_RESUME_VM_ERROR',

        'FC_CPPSDK_INVALID_PARAM_ERROR',
        'FC_CPPSDK_VERIFY_RESULT_ERROR',
        'FC_CPPSDK_CONNECT_REFUSED_ERROR',
        'FC_CPPSDK_LUN_NOT_UNREACHABLE_ERROR',
        'FC_CPPSDK_INVALID_LUN',
        'FC_CPPSDK_LUN_NOT_EXIST',
        'FC_CPPSDK_TRANS_MODE_CONFLICT',
        'FC_CPPSDK_CNA_NETOWRK_UNREACHABLE',
        'FC_BACKUP_MODE_CHANGED_ERROR',
        'FC_MAC_ALREADY_EXIST_ERROR',					// fc mac address already exist error
        'FC_NOT_SUPPORT_DATASTORE_TYPE_EEROR',          //fc does not support un_virtualized datastore for backup and recovery
        'FC_OPERATION_IN_PROCESS_ERROR',				// fc operation is in process
        'FC_DATASTORE_ALREADY_EXIST_ERROR',				// fc datastore already exist

        /* Openstack LVM new error num */
        'KVM_OPENSTACK_DISK_TYPE_NOT_SUPPORT_ERROR',				// KvmOpenStack Error: openstack disk type not support yet
        'KVM_OPENSTACK_CREATE_VOLUME_SNAPSHOT_ERROR',				// KvmOpenStack Error: create openstack volume snapshot by cinder api failed
        'KVM_OPENSTACK_CREATE_VOLUME_SNAPSHOT_TIME_OUT',			// KvmOpenStack Error: openstack create volume snapshot time out by cinder api
        'KVM_OPENSTACK_GET_VOLUME_SNAPSHOT_INFO_ERROR',				// KvmOpenStack Error: openstack get volume snapshot info by uuid error
        'KVM_OPENSTACK_DELETE_VOLUME_SNAPSHOT_ERROR',				// KvmOpenStack Error: openstack delete volume snapshot by cinder uuid error
        'KVM_OPENSTACK_DELETE_VM_SNAPHOT_ERROR',					// KvmOpenStack Error: openstack delete vm snapshot failed
        'KVM_OPENSTACK_GET_VM_DISK_SNAPSHOT_LIST_ERROR',			// KvmOpenStack Error: openstack get vm disk snapshot list failed

        'FC_CBT_STATUS_CHANGED_ERROR',							// fc cbt status error
        'FC_STORAGE_RESOURCE_NOT_CONNECTED_ERROR',				// fc host not connected to storage resource
        'FC_STORAGE_RESOURCE_NOT_EXIST_ERROR',					// fc storage resource not exist

        //2018.8.10 new error_log
        'VSERVER_DISK_SR_TYPE_NOT_ALL_THE_SAME_ERROR',			// not support contains difference storage type disk
        'VSERVER_DISK_TYPE_NOT_BLOCK_ERROR',					// disk is not block device
        'VSERVER_NOT_SUPPORT_LANFREE_ERROR',					// not support lanfree error
        'VSERVER_CREATE_SNAPSHOT_ERROR',						// create vm snapshot error

        'VM_HOST_NOT_EXIST_ERROR',								// host is not exist error
        'FC_GET_VOLUMES_OF_DATASTORE_ERROR',                    //fc get volumes of specified datastore error
        'FC_DATASTORE_NAME_EXIST',								//fc nas dastore name has already exist

        'FC_DATASTORE_ALREADY_DETACHED_ERROR',					// fc datastore already detached to host
        'FC_DATASTORE_DETACHING_ERROR',							// fc datastore is detaching error

        'ICS_KVM_API_ERROR',										// ICS api return error
        'VM_HYPERVISOR_NOT_SUPPORT_OPERATION_ERROR',				// current hypervisor is not support current operation
        'VM_DISK_MORE_THAN_ONE_SAME_NAME_ERROR',					// there is more than one disk has the same name in storage
        'ICS_HOST_AND_EXIST_NFS_SR_NOT_SAME_DATACENTER_ERROR',		// host and exist nfs not in the same datacenter

        'FC_STORAGE_RESOURCE_EXIST_ERROR',						// fc storage resource already exist
        'FC_STORAGE_RESOURCE_NAME_EXIST_ERROR',					// fc storage resource name already exist
        'XS_LARGER_THAN_TWO_TB_VDI_ONLY_SUPPORT_GFS2_SR_ERROR',	// only support create larger than 2TB vdi on gfs sr
        'FC_DATASTORE_NOT_EXIST_ERROR',							// fc datastore is not exist
        'VM_DISK_NOT_EXIST_ERROR',								// vm disk is not exist

        //CDP
        'VMWARE_OPEN_LOG_METADATA_FILE_ERROR', 					//Vmware open log metadata file error 
        'VMWARE_READ_LOG_METADATA_FILE_ERROR', 					//Vmware read log metadata file error
        'VMWARE_WRITE_LOG_METADATA_FILE_ERROR', 				//Vmware write log metadata file error
        'VMWARE_OPEN_BIT_FILE_ERROR', 							//Vmware open bit file error
        'VMWARE_READ_BIT_FILE_ERROR',							//Vmware read bit file error
        'VMWARE_WRITE_BIT_FILE_ERROR',			 				//Vmware write bit file error
        'VMWARE_TIMESTAMP_NOT_FOUND_ERROR', 					//Vmware timestamp not found error
        'VMWARE_PORT_IS_NOT_USING_BY_SYNC_SERVER_ERROR', 		//Vmware port is not using by sync server error
        'VMWARE_SYNC_SERVER_AREADY_EXIST_ERROR', 				//Vmware sync server already exist error
        'VMWARE_START_SYNC_SERVER_ERROR', 						//Vmware start sync server error
        'VMWARE_CONNECT_TO_SYNC_SERVER_ERROR', 					//Vmware connect to sync server error
        'VMWARE_HAS_NO_VM_CDP_TASK_EXIST_ERROR', 				//Vmware has no  vm cdp task exist error
        'VMWARE_DISK_ALREADY_EXIST_IN_MAP_ERROR',				//Vmware disk already exist in map error
        'VMWARE_NOT_FOUND_LOG_CACHE_ERROR', 					//Vmware not found log cache error
        'VMWARE_ADD_DISK_TO_VM_LOG_CACHE_MAP_ERROR',		 	//Vmware add disk to vm log cache map error
        'VMWARE_LOG_CACHE_DATA_NOT_ENOUGH_ERROR',				//Vmware log cache data not enough error
        'VMWARE_LOG_CACHE_REMAIN_SPACE_NOT_ENOUGH_ERROR', 		//Vmware log cach remain space is not enough error
        'VMWARE_PARSE_VM_LOG_CACHE_INFO_ERROR', 				//Vmware parse vm log cache info error
        'VMWARE_BUILD_VM_LOG_CACHE_INFO_ERROR', 				//Vmware build log cache info error
        'VMWARE_UPDATE_VM_LOG_CACHE_INFO_ERROR',			 	//Vmware update vm log cache info error
        'VMWARE_OPEN_LOG_CACHE_FILE_ERROR', 					//Vmware open log cache file error
        'VMWARE_READ_LOG_CACHE_FILE_ERROR', 					//Vmware read log cache file error
        'VMWARE_WRITE_LOG_CACHE_FILE_ERROR', 					//Vmware write log cache file error
        'VMWARE_LOG_TASK_ALREADY_EXIST_IN_MAP_ERROR', 			//Vmware log task already exist in map error
        'VMWARE_NOT_FOUND_LOG_TASK_ERROR', 						//Vmware not found log task error
        'VMWARE_NOT_FOUND_VM_IN_LOG_TASK_ERROR', 				//Vmware not found vm in log task error
        'VMWARE_ADD_LOG_TASK_TO_MAP_ERROR', 					//Vmware add log task to map error
        'VMWARE_LOAD_LOG_TASK_INFO_ERROR', 						//Vmware load log task info error
        'VMWARE_OPEN_BACKUP_CHAIN_FILE_ERROR', 					//Vmware open backup chain file error
        'VMWARE_UPDATE_VM_TMP_LOG_INFO_ERROR', 					//Vmware update vm temporary log info error
        'VMWARE_ARCHIVE_LOG_BACKUP_TIMEPOINT_ERROR', 			//Vmware archive log backup timepoint error
        'VMWARE_RELEASE_VM_LOG_TASK_INFO_ERROR', 				//Vmware release vm log task info error
        'VMWARE_RELOAD_VM_LOG_TASK_INFO_ERROR', 				//Vmware reload vm log task info error	
        'VMWARE_CONNECT_TO_VRD_ERROR', 							//Vmware connect to vrd error
        'VMWARE_TASK_IS_NOT_RUNNING_ERROR', 					//Vmware task is not running error
        'VMWARE_QUERY_IOFILTER_INFO_ERROR', 					//Vmware query iofilter info error error
        'VMWARE_INSTALL_IOFILTER_INFO_ERROR', 					//Vmware install iofilter info error
        'VMWARE_RECOVERY_TIMESTAMP_NOT_CORRECT_ERROR',
        'VMWARE_RECOVERY_TIMEPOINT_NOT_CORRENT_ERROR',


        //----------------------------hyper-v error code-------------------------------------------
        //for vm management
        'HYPERV_SCAN_VCENTER_ERROR',							//hyper-v scan vcenter error
        'HYPERV_SNAPSHOT_NOT_EXIST_ERROR',						//hyper-v the snapshot not exist error
        'HYPERV_CREATE_SNAPSHOT_ERROR',							//hyper-v create vm's snapshot error
        'HYPERV_DELETE_SNAPSHOT_ERROR',							//hyper-v delete vm's snapshot error
        'HYPERV_GET_VM_INFO_ERROR',								//hyper-v get vm information error
        'HYPERV_GET_VM_CONFIGURE_INFORMATION_ERROR',			//hyper-v get vm's configure information error after taking snapshot
        'HYPERV_CREATE_VM_ERROR',								//hyper-v create virtual machine error
        'HYPERV_DELETE_VM_ERROR', 								//hyper-v delete virtual machine error
        'HYPERV_POWER_ON_VM_ERROR',								//hyper-v power on vm error
        'HYPERV_POWER_OFF_VM_ERROR',							//hyper-v power off vm error
        'HYPERV_SUSPEND_VM_ERROR',								//hyper-v suspend vm error
        'HYPERV_RESUME_VM_ERROR',								//hyper-v resume vm error
        'HYPERV_VM_ALREADY_POWERON_ERROR',						//hyper-v vm is already poweron error
        'HYPERV_VM_ALREADY_POWEROFF_ERROR',						//hyper-v vm is already poweroff error
        'HYPERV_VM_NOT_SUSPEND_ERROR',							//hyper-v vm is not suspend error
        'HYPERV_VM_NOT_POWERON_ERROR',							//hyper-v vm is not poweron error
        'HYPERV_GET_VM_ALL_DISKS_ERROR',						//hyper-v get vm's disks before taking snapshot error
        'HYPERV_NOT_FIND_VM_NAME_ERROR',						//hyper-v not find vm name error

        //for host 
        'HYPERV_SCAN_DATASTORE_ERROR',							//hyper-v scan datastore error
        'HYPERV_DATASTORE_NOT_EXIST_ERROR',						//hyper-v datastore not exist error
        'HYPERV_DATASTORE_SPACE_NOT_ENOUGH_ERROR',				//hyper-v datastore space not enough error
        'HYPERV_CHECK_AND_CREATE_DIR_ERROR',					//hyper-v check and create directory error
        'HYPERV_SCAN_VIRTUAL_SWITCH_ERROR',						//hyper-v scan virtual switch error

        //for hyper-v disk transport
        'HYPERV_SET_ENCRYPTED_TRANSMISSION_ERROR',				//hyper-v set disk encrypted transmission error
        'HYPERV_OPEN_DISK_ERROR',								//hyper-v open disk error
        'HYPERV_CLOSE_DISK_ERROR',								//hyper-v close disk error
        'HYPERV_GET_DISK_BITMAP_ERROR',							//hyper-v get disk bitmap error
        'HYPERV_READ_MAPPED_DATA_ERROR',						//hyper-v read mapped data error
        'HYPERV_READ_BLOCK_DATA_ERROR',							//hyper-v read block data error
        'HYPERV_WRITE_BLOCK_DATA_ERROR',						//hyper-v write block data error
        'HYPERV_WRITE_METADATA_ERROR',							//hyper-v wirte metadata error
        'HYPERV_WRITE_VHD_FOOTER_ERROR',						//hyper-v write vhd footer error

        //for hyper-v agent service 
        'HYPERV_CONNECT_VM_MANAGEMENT_AGENT_SERVICE_ERROR',		//hyper-v connect to vm's management agent service program error
        'HYPERV_CONNECT_DISK_TRANSPORT_AGENT_SERVICE_ERROR',	//hyper-v connect to disk transport agent service program error


        //for backup and recovery
        'HYPERV_SAVE_SELF_EXPLAN_FILE_ERROR',					//hyper-v save self explain file error
        'HYPERV_BUILD_BACKUP_VM_LIST_ERROR',					//hyper-v build backup vm list from database error
        'HYPERV_BACKUP_VM_IS_NOT_EXIST_ERROR',					//hyper-v backup vm is not exist 
        'HYPERV_NOT_SUPPORT_BACKUP_TEMPLATE_ERROR',				//hyper-v not support to backup vm template error
        'HYPERV_OPEN_BACKUP_FILE_ERROR',						//hyper-v open backup file error
        'HYPERV_OPEN_BITMAP_FILE_ERROR',						//hyper-v open bitmap file error
        'HYPERV_COMPRESS_ERROR',								//hyper-v compress erorr
        'HYPERV_WRITE_BACKUP_FILE_ERROR',						//hyper-v write backup file error
        'HYPERV_WRITE_BITMAP_FILE_ERROR',						//hyper-v write bitmap file error
        'HYPERV_OPEN_LATEST_BITMAP_FILE_ERROR',					//hyper-v open latest bitmap file error
        'HYPERV_READ_LATEST_BITMAP_FILE_ERROR',					//hyper-v read latest bitmap file error
        'HYPERV_CREATE_BACKUP_DIR_ERROR',						//hyper-v create backup directory error
        'HYPERV_LATEST_TIMEPOINT_NOT_EXIST_ERROR',				//hyper-v latest timepoint not exist error
        'HYPERV_DISK_NUM_CHANGED_ERROR',						//hyper-v disk number changed error(disk num increased)
        'HYPERV_DISK_CHANGED_ERROR',							//hyper-v disk change error
        'HYPERV_DIFF_AND_INC_CAN_NOT_COEXIST_ERROR',			//hyper-v diff and inc can't coexist error
        'HYPERV_BLOCK_SIZE_CHANGED_ERROR',						//hyper-v block size changed error
        'HYPERV_OPEN_DEEP_VALID_DATA_BITMAP_FILE_ERROR',		//hyper-v open deep valid data bitmap file error
        'HYPERV_READ_DEEP_VALIA_DATA_BITMAP_FILE_ERROR',		//hyper-v read deep valid data bitmap file error
        'HYPERV_WRITE_DEEP_VALIA_DATA_BITMAP_FILE_ERROR',		//hyper-v write deep valid data bitmap file error
        'HYPERV_OPEN_BINARY_BITMAP_FILE_ERROR',					//hyper-v open binary bitmap file error
        'HYPERV_READ_BINARY_BITMAP_FILE_ERROR',					//hyper-v read binary bitmap file error
        'HYPERV_WRITE_BINARY_BITMAP_FILE_ERROR',				//hyper-v write binary bitmap file error
        'HYPERV_OPEN_RESULT_MERGE_BITMAP_FILE_ERROR',			//hyper-v open result merge bitmap file error
        'HYPERV_READ_RESULT_MERGE_BITMAP_FILE_ERROR',			//hyper-v read result merge bitmap file error
        'HYPERV_WRITE_RESULT_MERGE_BITMAP_FILE_ERROR',			//hyper-v write result merge bitmap file error
        'HYPERV_BINARY_BITMAP_FILE_SIZE_NOT_EQUAL_TO_DEEP_VALID_BITMAP_FILE_SIZE',	//hyper-v binary bitmap file's size not equal to deep valid bitmap file's size

        'HYPERV_BUILD_RECOVERY_VM_LIST_ERROR',				 	//hyper-v build recovery vm list error
        'HYPERV_GET_RECOVERY_TOTAL_SIZE_ERROR',					//hyper-v get recovery total size error
        'HYPERV_BACKUP_CHAIN_IS_MERGING_ERROR',					//the backup chain is merging error 
        'HYPERV_BACKUP_CHAIN_IS_USING_ERROR',					//the backup chain is using error
        'HYPERV_READ_BITMAP_FILE_ERROR',						//hyper-v read bitmap file error
        'HYPERV_FIND_BACKUP_FILE_ID_ERROR',						//hyper-v find the backup file id error
        'HYPERV_READ_BACKUP_FILE_ERROR',						//hyper-v read backup file error
        'HYPERV_DECOMPRESS_ERROR',								//hyper-v decompress error
        'HYPERV_PARSE_VM_CONFIG_ERROR',							//hyper-v parse vm configure error
        'HYPERV_GET_RECOVERY_VALID_DATA_SIZE_ERROR',			//hyper-v get recovery valid data size error

        //BACKUP COPY
        'BACKUP_COPY_CLIENT_INIT_ERROR',						// backup copy initialize error
        'BACKUP_COPY_CONNECT_TO_SERVER_ERROR',					// connect to backup copy system error
        'BACKUP_COPY_GET_BACKUP_REPOSITORY_INFO_ERROR',			// get offiste backup repository info error
        'BACKUP_COPY_TASK_ALREADY_EXIST_ERROR',					// task from source to same target already exist
        'BACKUP_COPY_TASK_VM_LIST_EMPTY_ERROR',					// backup copy vm task empty error
        'BACKUP_COPY_CHECK_SOURCE_STATUS_ERROR',				// backup copy check source task and vm status error
        'BACKUP_COPY_SOURCE_BACKUPS_IS_LOCKED_ERROR',			// backup copy source backups(chain) are locked by other task or process
        'BACKUP_COPY_GET_SOURCE_TIMEPOINT_ERROR',				// backup copy get source timepoint info error according to source task and vm
        'BACKUP_COPY_SERVER_INIT_ERROR',						// backup copy initialize error
        'BACKUP_COPY_GET_REMOTE_STORAGE_STATISTICS_ERROR',		// backup copy get remote summery storage statistics error
        'BACKUP_COPY_OPEN_TIMEPOINT_DIRECTORY_ERROR',			// backup copy open timepoint directory error
        'BACKUP_COPY_COPY_TIMEPOINT_ERROR',						// backup copy copy timepoint data error
        'BACKUP_COPY_GET_STORAGE_FREE_SPACE_ERROR',				// backup copy get storage free space error
        'BACKUP_COPY_NOT_ENOUGH_SPACE_ERROR',					// backup copy free space is not enough error
        'BACKUP_COPY_GET_STORAGE_MOUNT_POINT_ERROR',			// backup copy get storage mount point error
        'BACKUP_COPY_CREATE_DIRECTORY_ERROR',					// backup copy create directory error
        'BACKUP_COPY_INIT_STORAGE_OPERATOR_EEROR',				// backup copy init target storage operator error	
        'BACKUP_COPY_GET_CONTENTS_OF_STORAGE_ERROR',			// backup copy get contents of storage error
        'BACKUP_COPY_GET_STORAGE_INFO_ERROR',					// backup copy get storage detail info error
        'BACKUP_COPY_INIT_OBJECT_OPERATOR_EEROR',				// backup copy init target object operator error
        'BACKUP_COPY_OPEN_OBJECT_ERROR',						// backup copy open target object error
        'BACKUP_COPY_OBJECT_NOT_OPEN_ERROR',					// backup copy has not opened error
        'BACKUP_COPY_DELETE_TARGET_EROOR',						// backup copy delete target folder or object error
        'BACKUP_COPY_GET_OBJECT_SIZE_ERROR',					// backup copy get target object size error 
        'BACKUP_COPY_READ_DATA_ERROR',							// backup copy read data error
        'BACKUP_COPY_WRITE_DATA_ERROR',							// backup copy write data error
        'BACKUP_COPY_CLOSE_TARGET_ERROR',						// backup copy close target error
        'BACKUP_COPY_CREATE_NEW_TIMEPOINT_ERROR',				// backup copy create new timepoint error
        'BACKUP_COPY_INCORRECT_USER_AND_PASSWORD',				// incorrect user or password for remote backup and recovery system
        'BACKUP_COPY_GET_REMOTE_BACKUP_COPY_TIMEPOINTS_ERROR',	// backup copy get copy timepoints of remote backup and recovery system error
        'BACKUP_COPY_IMPORT_COPY_TIMEPOINT_ERROR',				// backup copy import copy timepoints error;
        'BACKUP_COPY_CREAT_SELF_DESCRIPTION_FILE_ERROR',		// backup copy create self description file for timepoint error
        'BACKUP_COPY_GET_STORAGET_UUID_ERROR',					// backup copy get target storage uuid error
        'BACKUP_COPY_GET_TIMEPOINT_INFO_ERROR',					// backup copy get timepoint info error
        'BACKUP_COPY_DELETE_TIMEPOINT_ERROR',					// backup copy delete timepoint error
        'BACKUP_COPY_NO_NEW_RESTORE_POINT_FOUND',				// bakcup copy found no new timepoints
        'BACKUP_COPY_INSERT_BACKUP_TIMEPOINT_INFO_ERROR',		// backup copy insert timepont into database error
        'BACKUP_COPY_TIMEPOINT_NOT_AVAILABLE_ERROR',			// backup copy timepoint not available error
        'BACKUP_COPY_TIMEPOINT_NOT_EXIST_ERROR',				// backup copy timepoint not exist error

        /* for xenserver refcount */
        'XENSERVER_VM_SNAPSHOT_IS_MERGING_ERROR',				// may be vm snapshot is merging error, please try the operation again later
        'BACKUP_COPY_TIMEPOINT_MERGING_ERROR',					// backup copy timepoint is merging, not available error
        'BACKUP_COPY_CREATE_THREAD_FOR_MERGE_ERROR',			// backup copy create thread to merge vmware timepoint error
        'BACKUP_COPY_TIMEPOINT_IN_USE_ERROR',					// backup copy is in use error

        'BACKUP_COPY_STORAGE_OPERATOR_NOT_INIT_ERROR',			// backup copy storage operator not init error
        'BACKUP_COPY_OBJECT_OPERATOR_NOT_INIT_ERROR',			// backup copy object operator not init error
        'BACKUP_COPY_NETWORK_FAULT_ERROR',						// backup copy network fault
        'BACKUP_COPY_SET_WRITE_OFFSET_ERROR',					// backup copy set write offset in object error
        'BACKUP_COPY_UPDATE_BITMAP_ERROR',						// backup copy update bitmap content for vmware error
        'KVM_SANGFOR_API_ERROR',								// sangfor api error
        'BACKUP_COPY_UNKOWN_OPCODE_ERROR',						// backup copy unknown opcode between client and server

        //add for fusioncompute c++ sdk error
        'FC_CPPSDK_BROKEN_PIPE_ERROR',							// fc c++ sdk connection rest by peer error
        'FC_CPP_FATAL_ERROR',									// fc c++ fatal error, need to reconnect
        'BACKUP_COPY_SOURCE_TAKS_NOT_EXIST_ERROR',				// backup copy related source task not exist, may already been deleted
        'KVM_OPENSTACK_GET_AVAILABILITY_ZONE_ERROR',            //get availability zone error
        'BACKUP_COPY_LIST_BUCKET_ERROR',						// backup copy list all bucket of user error
        'BACKUP_COPY_BUCKET_NOT_EXIST_ERROR',					// backup copy target bucket not exist

        'VM_DISK_CONFIG_INFO_CHANGE_ERROR',						// vm's disk configuration change from last backup
        'BACKUP_COPY_LIST_FOLDER_OF_BUCKET_ERROR',				// backup copy get folder list of bucket error
        'BACKUP_COPY_SUB_FOLDER_NOT_EXIST_ERROR',					// backup copy sub folder not exist error
        'BACKUP_COPY_OBJECT_NOT_EXIST_ERROR',						// backup copy object not exist error
        'BACKUP_COPY_OBJECT_INCORRECT_URL_ERROR',					// backup copy object url not correct error

        'BACKUP_COPY_UPLOAD_OBJECT_ERROR',						// backup copy upload cloud object error
        'BACKUP_COPY_DOWNLOAD_OBJECT_ERROR',						// backup copy download cloud object error
        'BACKUP_COPY_CALCULATE_TASK_SIZE_ERROR',					// backup copy calculate task size error
        'BACKUP_COPY_GET_TIMEPOINT_BLOCK_SIZE_ERROR',				// backup copy get block size of timepoint error

        'VM_BACKUP_CHAIN_IS_MERGING_ERROR',						// backup chains is merging 

        /* zstack error code */
        'ZSTACK_API_ERROR',										// ZStack Api return error
        'ZSTACK_SESSION_INVALID',									// ZStack login session invalid, may be expired
        'ZSTACK_CLUSTER_NOT_EXIST_ERROR',							// ZStack cluster is not exist
        'ZSTACK_ZONE_NOT_EXIST_ERROR',							// ZStack zone is not exist
        'ZSTACK_L2_NETWORK_NOT_EXIST_ERROR',						// ZStack L2 network is not eixst error
        'ZSTACK_L3_NETWORK_NOT_EXIST_ERROR',						// ZStack L3 network is not eixst error
        'ZSTACK_VM_OFFERING_NOT_EXIST_ERROR',						// ZStack Vm Offering is not exist error
        'ZSTACK_DISK_OFFERING_NOT_EXIST_ERROR',					// ZStack Disk Offering is not exist error
        'ZSTACK_VOLUME_NOT_EXIST_ERROR',							// ZStack Volume is not exist error
        'ZSTACK_CREATE_VOLUME_SNAPSHOT_ERROR',					// Create volume snapshot error

        'ZSTACK_CREATE_NFS_STORAGE_ERROR',						// Create NFS storage error
        'ZSTACK_CREATE_DISK_OFFERING_ERROR',					// create disk offering error
        'ZSTACK_CREATE_VM_OFFERING_ERROR',						// create vm offering error
        'ZSTACK_VOLUME_INSTALL_PATH_INVALID_ERROR',				// volume install path is invalid 
        'ZSTACK_NOT_SUPPORT_BACKUP_STORAGE_TYPE_ERROR',			// current version not support the primary storage type

        'ZSTACK_IMAGE_NOT_EXIST_ERROR',							// image is not exist error
        'ZSTACK_BACKUP_STORAGE_NOT_EXIST_ERROR',				// Backup/Image Storage is not Exist
        'ZSTACK_CANNOT_FIND_ISO_IMAGE_ERROR',					// can't find iso image in the system
        'ZSTACK_CREATE_VM_ERROR',								// create vm error
        'ZSTACK_CREATE_VOLUME_ERROR',							// create volume error
        'ZSTACK_ATTACH_VOLUME_TO_VM_ERROR',						// attach data volume to vm error
        'ZSTACK_DOWNLOAD_CEPH_CONNECTION_INFO_ERROR',			// download ceph connection info error
        'BACKUP_COPY_GET_ARCHIVE_TIMEPOINT_LIST_ERROR',			// get archive timepoint list error

        /* for new disk driver interface */
        'VM_DISK_CLUSTER_SIZE_UNABLE_DIVIDE_ERROR',				// request cluster size unable divided by original cluster size
        'VM_DISK_INVALID_CLUSTER_SIZE_ERROR', 					//caught invalid disk cluster size error
        'VM_SNAPSHOT_ALREADY_CREATED_ERROR',						// vm snapshot is already created error
        'VM_SNAPSHOT_THREAD_JOIN_TIMEOUT_ERROR',					// vm pre create snapshot join timeout

        /* for fusioncompute kvm*/
        'FC_KVM_ACCOUNT_LOCKED_ERROR',							// fusioncompute kvm account be locked for wrong passwd
        'FC_KVM_VERSION_DISMATCHED_ERROR',						// fusioncompute kvm version not matched error
        'FC_KVM_GET_TASK_URI_ERROR',								// fusioncompute kvm get task uri error
        'FC_KVM_TARGET_NOT_EXIST_ERROR',							// fusioncompute kvm object or operation target not exist error
        'FC_KVM_JSON_KEY_NOT_EXIST',								// fusioncompute kvm json key not exist error
        'FC_KVM_SNAPSHOT_IN_USE_ERROR',							// fusioncompute kvm snapshot in use, please wait and retry
        'FC_KVM_INSTANT_MOUNT_POINT_NOT_FOUND_ERROR',				// fusioncompute kvm target

        'VM_VCENTER_ENV_HAS_CHANGE_ERROR',                        //vm infrastructure environment has been changed, please delete the old one and re add

        'FC_KVM_HTTP_OPERATION_ERROR',							// fusioncompute kvm do http operation error

        'ZSTACK_UPLOAD_DEFAULT_RECOVERY_ISO_ERROR',
        'ZSTACK_NOT_FOUND_VALID_IMAGE_BACKUP_STORAGE_ERROR',

        //for object storage
        'BACKUP_COPY_GET_OBJECT_METADATA_ERROR',					// get metadta of object error
        'KVM_OPENSTACK_DETACH_VOLUME_TO_VM_ERROR',				// detach volume from vm error

        'VM_REQUEST_CLUSTER_IO_VEC_SIZE_INVALID_ERROR', //io vector result size is invalid error" 
        'VM_ENCRYPT_CHANGED_ERROR', //vm encrypt changed error" 


        ///// fusion compute kvm version all error code definition, add by sky, date: 2021-03-91
        'VM_FC_KVM_DEFAULT_ERROR', //FC API default error code" 
        'VM_FC_KVM_PUB_10000001_ERROR', //invalid request format" 
        'VM_FC_KVM_PUB_10000002_ERROR', //login session is out of date" 
        'VM_FC_KVM_PUB_10000003_ERROR', //permission denied with current user" 
        'VM_FC_KVM_PUB_10000004_ERROR', //failed to op database" 
        'VM_FC_KVM_PUB_10000005_ERROR', //VRM service is recovering, please try op later" 
        'VM_FC_KVM_PUB_10000006_ERROR', //system is busy, please try again later" 
        'VM_FC_KVM_PUB_10000007_ERROR', //there are too much tasks handled by system, please try again later" 
        'VM_FC_KVM_PUB_10000008_ERROR', //object is not exist" 
        'VM_FC_KVM_PUB_10000009_ERROR', //object is in invalid status, or operation conflict" 
        'VM_FC_KVM_PUB_10000010_ERROR', //operation failed" 
        'VM_FC_KVM_PUB_10000011_ERROR', //string length is invalid, valid range [1, 64]" 
        'VM_FC_KVM_PUB_10000012_ERROR', //'name' parameter is invalid, please input again" 
        'VM_FC_KVM_PUB_10000013_ERROR', //'limit' parameter is invalid, please input again" 
        'VM_FC_KVM_PUB_10000014_ERROR', //'IP' parameter is invalid, please input again" 
        'VM_FC_KVM_PUB_10000015_ERROR', //'cluster tag' parameter is invalid, please input again" 
        'VM_FC_KVM_PUB_10000016_ERROR', //'offset' parameter is invalid, please input again" 
        'VM_FC_KVM_PUB_10000018_ERROR', //'descripion' parameter is invalid, please input again" 
        'VM_FC_KVM_PUB_10000020_ERROR', //site is not exist" 
        'VM_FC_KVM_PUB_10200258_ERROR', //failed to communicate with another VRM " 
        'VM_FC_KVM_PUB_10000022_ERROR', //invalid version " 
        'VM_FC_KVM_PUB_10300276_ERROR', //DR VM is not support the operation" 
        'VM_FC_KVM_PUB_10300275_ERROR', //stub VM is not support the operation" 
        'VM_FC_KVM_PUB_10540103_ERROR', //virtual netcard's portgroup is invalid or not exist" 
        'VM_FC_KVM_PUB_10301037_ERROR', //the host is in maintenance mode, operation is not allowed" 

        // FusionCompute KVM resource(site, cluster, vm) error code
        'VM_FC_KVM_RESOURCE_10300001_ERROR', //IDE bus is not support hotplug feature" 
        'VM_FC_KVM_RESOURCE_10300002_ERROR', //VM's name is empty" 
        'VM_FC_KVM_RESOURCE_10300003_ERROR', //'cpu core num' of VM is invalid " 
        'VM_FC_KVM_RESOURCE_10300004_ERROR', //'cpu quota' of VM is invalid " 
        'VM_FC_KVM_RESOURCE_10300005_ERROR', //'cpu limit' of VM is invalid " 
        'VM_FC_KVM_RESOURCE_10300006_ERROR', //'memory size' of VM is invalid " 
        'VM_FC_KVM_RESOURCE_10300007_ERROR', //'memory quota' of VM is invalid " 
        'VM_FC_KVM_RESOURCE_10300008_ERROR', //'disk slot' of VM is invalid " 
        'VM_FC_KVM_RESOURCE_10300009_ERROR', //'disk size' of VM is invalid " 
        'VM_FC_KVM_RESOURCE_10300010_ERROR', //disk number is over limit" 
        'VM_FC_KVM_RESOURCE_10300011_ERROR', //virtual network adapter's portgroup couldn't be null" 
        'VM_FC_KVM_RESOURCE_10300012_ERROR', //virtual network adapter number is over limit" 
        'VM_FC_KVM_RESOURCE_10300013_ERROR', //'boot mode' of VM is invalid" 
        'VM_FC_KVM_RESOURCE_10300014_ERROR', //'Fault handling strategy' of VM is invalid" 
        'VM_FC_KVM_RESOURCE_10300015_ERROR', //operation is not allowed for the VM" 
        'VM_FC_KVM_RESOURCE_10300016_ERROR', //system resource is insufficient, please retry later" 
        'VM_FC_KVM_RESOURCE_10300017_ERROR', //virtual disk is related with different type of datastore, is not allowed to create snapshot" 
        'VM_FC_KVM_RESOURCE_10300018_ERROR', //the type of datastore is not support to create virtual disk snapshot" 
        'VM_FC_KVM_RESOURCE_10300019_ERROR', //VM 'creation/clone/template provision/boot by network' operation is failed due to lack of resource" 
        'VM_FC_KVM_RESOURCE_10300020_ERROR', //VM sleep failed due to lack of storage resource" 
        'VM_FC_KVM_RESOURCE_10300021_ERROR', //target VM is template, opreation is not allowed" 
        'VM_FC_KVM_RESOURCE_10300022_ERROR', //assigne mac, create vm or add vnic error" 
        'VM_FC_KVM_RESOURCE_10300023_ERROR', //IP resource is insufficient, please configure more IP resource" 
        'VM_FC_KVM_RESOURCE_10300024_ERROR', //VM couldn't be migrated to the same host" 
        'VM_FC_KVM_RESOURCE_10300025_ERROR', //the host is busy, please retry later" 
        'VM_FC_KVM_RESOURCE_10300026_ERROR', //guest tool is not running, please retry later" 
        'VM_FC_KVM_RESOURCE_10300027_ERROR', //target host is not exist" 
        'VM_FC_KVM_RESOURCE_10300028_ERROR', //target cluster is not exist" 
        'VM_FC_KVM_RESOURCE_10300029_ERROR', //there is exsit vm in cluster, delete opreation is not allowed" 
        'VM_FC_KVM_RESOURCE_10300030_ERROR', //there is exsit host in cluster, delete opreation is not allowed" 
        'VM_FC_KVM_RESOURCE_10300031_ERROR', //system is exist more than 32 clusters, failed to add new cluster" 
        'VM_FC_KVM_RESOURCE_10300032_ERROR', //some VMs running under the host that does not meet the migration conditions, host can't be empty" 
        'VM_FC_KVM_RESOURCE_10300033_ERROR', //cdrom is already loaded on target VM, couldn't be loaded again" 
        'VM_FC_KVM_RESOURCE_10300034_ERROR', //cdrom is not loaded on target VM, couldn't do loading operation" 
        'VM_FC_KVM_RESOURCE_10300035_ERROR', //'URL' is invalid" 
        'VM_FC_KVM_RESOURCE_10300037_ERROR', //volume is not attached to VM, the operation is not allowed" 
        'VM_FC_KVM_RESOURCE_10300038_ERROR', //tools is attached to VM, couldn't be mounted again" 
        'VM_FC_KVM_RESOURCE_10300039_ERROR', //tools is not attached to VM, the operation is not allowed" 
        'VM_FC_KVM_RESOURCE_10300040_ERROR', //invalid parameter of VM's 'memory limit'" 
        'VM_FC_KVM_RESOURCE_10300041_ERROR', //invalid 'OS type' of VM" 
        'VM_FC_KVM_RESOURCE_10300042_ERROR', //invalid 'OS version' of VM" 
        'VM_FC_KVM_RESOURCE_10300043_ERROR', //invalid 'CPU reserve' of VM" 
        'VM_FC_KVM_RESOURCE_10300044_ERROR', //invalid 'memory reserve' of VM" 
        'VM_FC_KVM_RESOURCE_10300045_ERROR', //mac address is already exist, create vm/vnic failed" 
        'VM_FC_KVM_RESOURCE_10300046_ERROR', //failed to free mac address, delete vnic failed" 
        'VM_FC_KVM_RESOURCE_10300047_ERROR', //mac resource is insufficient, create VM/add vnic failed, please configure more mac resources" 
        'VM_FC_KVM_RESOURCE_10300065_ERROR', //host can't be move in the same cluster" 
        'VM_FC_KVM_RESOURCE_10300066_ERROR', //value of 'mac address' is invalid" 
        'VM_FC_KVM_RESOURCE_10300067_ERROR', //value of 'location' of VM is invalid" 
        'VM_FC_KVM_RESOURCE_10300068_ERROR', //value of 'datastore flag' is invalid" 
        'VM_FC_KVM_RESOURCE_10300069_ERROR', //value of 'OS type' is empty" 
        'VM_FC_KVM_RESOURCE_10300070_ERROR', //disk slot num is in use, please use another slot num" 
        'VM_FC_KVM_RESOURCE_10300071_ERROR', //number of host in cluster is over limit, failed to add new host to target cluster" 
        'VM_FC_KVM_RESOURCE_10300072_ERROR', //The target host/cluster cannot meet the storage conditions for the virtual machine to run." 
        'VM_FC_KVM_RESOURCE_10300073_ERROR', //The target host/cluster cannot meet the network conditions for the virtual machine to run." 
        'VM_FC_KVM_RESOURCE_10300074_ERROR', //failed to attach disk to VM due to the disk is attached to another VM or the status of disk is invalid" 
        'VM_FC_KVM_RESOURCE_10300075_ERROR', //failed to attach disk to VM due to the number of attached disk is over limit" 
        'VM_FC_KVM_RESOURCE_10300076_ERROR', //the disk is already attached to VM, don't do it again" 
        'VM_FC_KVM_RESOURCE_10300077_ERROR', //value length of 'group' is invalid" 
        'VM_FC_KVM_RESOURCE_10300078_ERROR', //disk need to be attached is not exist" 
        'VM_FC_KVM_RESOURCE_10300079_ERROR', //the disk contains volume snapshot, the operation is not allowed" 

        'VM_FC_KVM_RESOURCE_10300083_ERROR', //target location can't find the host whose storage condition meet the VM startup" 
        'VM_FC_KVM_RESOURCE_10300084_ERROR', //target location can't find the host whose network condition meet the VM startup" 
        'VM_FC_KVM_RESOURCE_10300085_ERROR', //VRM internal error, please contact technical support" 

        'VM_FC_KVM_RESOURCE_10300092_ERROR', //the VM is stopped, current operation is not allowed" 
        'VM_FC_KVM_RESOURCE_10300093_ERROR', //the VM is suspended, current operation is not allowed" 
        'VM_FC_KVM_RESOURCE_10300094_ERROR', //the VM is already running, boot operation is not allowed" 

        'VM_FC_KVM_RESOURCE_10300096_ERROR', //current operation is in progress, don't do it again" 
        'VM_FC_KVM_RESOURCE_10300097_ERROR', //VM is suspended, don't do stop operation" 
        'VM_FC_KVM_RESOURCE_10300098_ERROR', //VM is stopped, don't do suspend operation" 

        'VM_FC_KVM_RESOURCE_10300100_ERROR', //exist share disk attached to current VM, operation is no allowed" 
        'VM_FC_KVM_RESOURCE_10300101_ERROR', //the VM is busy, please retry operation later" 

        'VM_FC_KVM_RESOURCE_10300109_ERROR', //snapshot is not exist, please select other snapshot and retry" 
        'VM_FC_KVM_RESOURCE_10300110_ERROR', //operation is not allowed due to current snapshot status" 
        'VM_FC_KVM_RESOURCE_10300111_ERROR', //failed to create snapshot due to number of snapshot is over limit" 
        'VM_FC_KVM_RESOURCE_10300112_ERROR', //task of creating snapshot is exist, a VM is not allowed create snapshots at the same time" 

        'VM_FC_KVM_RESOURCE_10300058_ERROR', //the disk contains other VM's snapshot " 

        'VM_FC_KVM_RESOURCE_10300113_ERROR', //failed to create snapshot which contains VM's memory, please retry later" 
        'VM_FC_KVM_RESOURCE_10300114_ERROR', //failed to modify the VM to template, because the VM contains snapshot " 
        'VM_FC_KVM_RESOURCE_10300115_ERROR', //failed to boot VM on host when the snapshot of VM is recovering" 
        'VM_FC_KVM_RESOURCE_10300116_ERROR', //failed to create VM snapshot, because suspended VM is only support create snapshot with memory" 
        'VM_FC_KVM_RESOURCE_10300118_ERROR', //link clone VM is not allowed current operation" 
        'VM_FC_KVM_RESOURCE_10300119_ERROR', //failed to create VM snapshot, because stopped VM is not support create snapshot with memory" 
        'VM_FC_KVM_RESOURCE_10300121_ERROR', //vnic's name is already exist" 

        'VM_FC_KVM_RESOURCE_10300138_ERROR', //queried VM list is invalid" 

        'VM_FC_KVM_RESOURCE_10300141_ERROR', //template is not support snapshot operation, please convert to VM and retry" 

        'VM_FC_KVM_RESOURCE_10300153_ERROR', //Please check whether a snapshot is taken for the disk" 

        'VM_FC_KVM_RESOURCE_10900021_ERROR', //CPU in system is over license limit" 

        'VM_FC_KVM_RESOURCE_10310034_ERROR', //floppy device is attached to VM, operation is not allowed, please unattached the device" 

        'VM_FC_KVM_RESOURCE_10300808_ERROR', //failed to bind the usb device, please shutdown the VM, then reboot the VM and retry" 

        'VM_FC_KVM_RESOURCE_11400049_ERROR', //VM's PCI is passthrough, memory should be 100% reserve" 
        'VM_FC_KVM_RESOURCE_11400050_ERROR', //VM is bound PCI device, memory limit configuration is not allowed" 
        'VM_FC_KVM_RESOURCE_11400052_ERROR', //PCI passthrough VM, must be bound with host" 
        'VM_FC_KVM_RESOURCE_11400051_ERROR', //VM can not be bound with VIRTIO and IDE disks at the same time" 

        'VM_FC_KVM_RESOURCE_10300175_ERROR', //VM which be bound with USB device is not support current operation" 
        'VM_FC_KVM_RESOURCE_10300843_ERROR', //the VM is not support the mounted device type" 

        'VM_FC_KVM_RESOURCE_10300987_ERROR', //the VM whose boot order is not specified is not support configure the boot order options" 
        'VM_FC_KVM_RESOURCE_10300988_ERROR', //if VM's boot order is specified, user should configure the boot order of the VM" 
        'VM_FC_KVM_RESOURCE_10300828_ERROR', //There is no host whose affinity or anti affinity conditions meet the virtual machine startup in the specified location." 
        'VM_FC_KVM_RESOURCE_10300839_ERROR', //the VM is not support change the resource group due to it's status, please retry after VM reboot" 

        'VM_FC_KVM_RESOURCE_10300953_ERROR', //normal cluster is not support to run huge page VM" 
        'VM_FC_KVM_RESOURCE_10300956_ERROR', //couldn't not configure huge page for the VM, because the VM isn't in cluster " 
        'VM_FC_KVM_RESOURCE_10300957_ERROR', //huge page VM is not support to configure memory boot mode" 
        'VM_FC_KVM_RESOURCE_10300962_ERROR', //normal cluster is not support to run NUMA VM" 
        'VM_FC_KVM_RESOURCE_10300963_ERROR', //could not configure NUMA for the VM, because the VM isn't in cluster " 
        'VM_FC_KVM_RESOURCE_10300964_ERROR', //could not change the system volume when the VM is running" 
        'VM_FC_KVM_RESOURCE_10300965_ERROR', //should be configured with 100% CPU reserve when the VM is bound with CPU" 
        'VM_FC_KVM_RESOURCE_10300966_ERROR', //could not configure CPU bound and CPU range at the same time" 

        'VM_FC_KVM_RESOURCE_10300970_ERROR', //bound CPU is not support hotplug feature" 
        'VM_FC_KVM_RESOURCE_10300971_ERROR', //normal cluster is not support to run NUMA/realtime VM" 

        'VM_FC_KVM_RESOURCE_10300978_ERROR', //vnic is already in task" 

        'VM_FC_KVM_RESOURCE_10300980_ERROR', //NUMA resource is insufficient, please check VM 'CPU cores, reserve, limit and mhz'" 

        'VM_FC_KVM_RESOURCE_10301107_ERROR', //DPI VM type is invalid" 
        'VM_FC_KVM_RESOURCE_10301111_ERROR', //DPI VM is only support normal vswitch" 

        'VM_FC_KVM_RESOURCE_10301009_ERROR', //the VM is not bound with host, please cleanup the configuration of CPU bound" 
        'VM_FC_KVM_RESOURCE_10301011_ERROR', //the VM is not specified the NUMA bound bitmap" 
        'VM_FC_KVM_RESOURCE_10301015_ERROR', //the VM is already bound with CPU, please unbound and retry" 
        'VM_FC_KVM_RESOURCE_10301016_ERROR', //the VM's hotplug vcpu is insufficient" 

        'VM_FC_KVM_RESOURCE_10301025_ERROR', //could not take snapshot for VM which contains cdrom, please unload and retry" 
        'VM_FC_KVM_RESOURCE_10301026_ERROR', //failed to boot VM due to internal reboot or other exception" 

        'VM_FC_KVM_RESOURCE_10301042_ERROR', //failed to set auto upgrade for VM tools due to internal error" 
        'VM_FC_KVM_RESOURCE_10301043_ERROR', //clone VM or template can not be modify firmware when provisioning" 
        'VM_FC_KVM_RESOURCE_10301044_ERROR', //sriov VM is not support memory hotplug feature" 
        'VM_FC_KVM_RESOURCE_10300440_ERROR', //libvirt service exception in current host, please check the host" 
        'VM_FC_KVM_RESOURCE_10300441_ERROR', //could not modify boot firmware online" 
        'VM_FC_KVM_RESOURCE_10300442_ERROR', //failed to define VM" 
        'VM_FC_KVM_RESOURCE_10300443_ERROR', //VM internal erorr or unknown exception occured, please retry or contact technical support" 
        'VM_FC_KVM_RESOURCE_10300444_ERROR', //SRIOV nic is not support selected port group" 
        'VM_FC_KVM_RESOURCE_10300445_ERROR', //failed to operate the device, please check log in guest or contact technical support" 
        'VM_FC_KVM_RESOURCE_10300446_ERROR', //Linux VM's IDE system volume, when configured memory " 
        'VM_FC_KVM_RESOURCE_10300447_ERROR', //the VM memory hot plug exceeds maximum limit or hot plug size exceeds maximum memory limit" 
        'VM_FC_KVM_RESOURCE_12000016_ERROR', //target host's memory is insufficient, please adjust and retry" 
        'VM_FC_KVM_RESOURCE_12000017_ERROR', //target host's memory multiplex rate is too high, please adjust and retry" 
        'VM_FC_KVM_RESOURCE_12000018_ERROR', //the host's memory multiplex rate is too high, please adjust and retry" 
        'VM_FC_KVM_RESOURCE_12000019_ERROR', //exist host's memory multiplex rate is over 100% in the cluster, please adjust and retry" 
        'VM_FC_KVM_RESOURCE_12000020_ERROR', //number of VM's vcpu is over the limit of host or cluster, please adjust and retry" 
        'VM_FC_KVM_RESOURCE_12000022_ERROR', //cluster HA resource's CPU reserve is insufficient, please adjust and retry" 
        'VM_FC_KVM_RESOURCE_12000023_ERROR', //cluster HA resource's memory reserve is insufficient, please adjust and retry" 

        'VM_FC_KVM_RESOURCE_12000027_ERROR', //exist uncontrolled disk in the VM, please adjust and retry" 

        'VM_FC_KVM_RESOURCE_10321005_ERROR', //current guest operating system is not support hotplug disk" 
        'VM_FC_KVM_RESOURCE_10321008_ERROR', //current guest operating system is not support hotplug netcard" 
        'VM_FC_KVM_RESOURCE_10321009_ERROR', //current guest operating system is not support hotplug SRIOV netcard" 
        'VM_FC_KVM_RESOURCE_10321010_ERROR', //current guest operating system is not support unplug netcard online" 
        'VM_FC_KVM_RESOURCE_10321016_ERROR', //bound PCI device is not support current operation" 

        'VM_FC_KVM_RESOURCE_10321020_ERROR', //linked clone VM's number of disk is over limit" 
        'VM_FC_KVM_RESOURCE_10321021_ERROR', //linked clone VM's ID disk's slot in use, failed to do creation" 

        'VM_FC_KVM_RESOURCE_10321028_ERROR', //guest operating system is not support snapshot creation" 

        'VM_FC_KVM_RESOURCE_10321039_ERROR', //Linux guest is not support VGA video device" 

        'VM_FC_KVM_RESOURCE_10321046_ERROR', //VM's cpu cores is over the limit in current topology, please adjust" 

        'VM_FC_KVM_RESOURCE_10321050_ERROR', //operation failed due to host exception" 
        'VM_FC_KVM_RESOURCE_10321051_ERROR', //operation failed, because memory multiplex ratio is too high" 
        'VM_FC_KVM_RESOURCE_10321052_ERROR', //database operation exception, please try again later" 
        'VM_FC_KVM_RESOURCE_10321053_ERROR', //failed to send message to host, please try again later" 
        'VM_FC_KVM_RESOURCE_10321054_ERROR', //failed to recieve message from host, please try again later" 
        'VM_FC_KVM_RESOURCE_10321055_ERROR', //guest tools is not installed or guest internal error" 

        'VM_FC_KVM_RESOURCE_10421008_ERROR', //'SATA' virtual disk is not support current operation" 
        'VM_FC_KVM_RESOURCE_10321061_ERROR', //cd or tools is mounted to VM, please unmount and retry" 
        'VM_FC_KVM_RESOURCE_10321062_ERROR', //VM is not support anti-virus feature" 

        'VM_FC_KVM_RESOURCE_10321094_ERROR', //exist different format disks in the VM, opeation is not allowed" 

        'VM_FC_KVM_RESOURCE_10321120_ERROR', //'BIOS' boot mode is not allowed in ARM architecture" 

        'VM_FC_KVM_RESOURCE_10321123_ERROR', //architecture of VM is incompatible with the compute resource" 
        'VM_FC_KVM_RESOURCE_10321124_ERROR', //failed to query the architecture of host, please check the network and retry" 

        'VM_FC_KVM_RESOURCE_10321125_ERROR', //ARM VM is not support IDE disk" 
        'VM_FC_KVM_RESOURCE_10321127_ERROR', //failed to delete snapshot due to VM's status change, please retry after VM become normal" 

        // FusionCompute KVM node management error code" 
        'VM_FC_KVM_NM_10200101_ERROR', //input parameter is null" 
        'VM_FC_KVM_NM_10200102_ERROR', //host name is null" 
        'VM_FC_KVM_NM_10200103_ERROR', //host name is null" 
        'VM_FC_KVM_NM_10200104_ERROR', //host name is already exist" 
        'VM_FC_KVM_NM_10200105_ERROR', //length of host name is over limit" 
        'VM_FC_KVM_NM_10200106_ERROR', //host IP is null" 
        'VM_FC_KVM_NM_10200107_ERROR', //host IP is invalid" 
        'VM_FC_KVM_NM_10200108_ERROR', //host IP is already exist " 
        'VM_FC_KVM_NM_10200110_ERROR', //parameter of 'BMC IP' is invlaid" 
        'VM_FC_KVM_NM_10200112_ERROR', //length of BMC user name is over limit" 
        'VM_FC_KVM_NM_10200113_ERROR', //BMC user name is invalid" 
        'VM_FC_KVM_NM_10200114_ERROR', //BMC password is invalid" 
        'VM_FC_KVM_NM_10200115_ERROR', //id of cluster is invalid" 
        'VM_FC_KVM_NM_10200116_ERROR', //cluster is not exist" 
        'VM_FC_KVM_NM_10200117_ERROR', //format of ntpip is invalid" 
        'VM_FC_KVM_NM_10200118_ERROR', //format of logip is invalid" 
        'VM_FC_KVM_NM_10200119_ERROR', //format of kboxip is invalid" 
        'VM_FC_KVM_NM_10200120_ERROR', //the operation target host is not exist" 
        'VM_FC_KVM_NM_10200121_ERROR', //ID of host is invalid" 

        'VM_FC_KVM_NM_10200124_ERROR', //operation failed due to the host status" 
        'VM_FC_KVM_NM_10200130_ERROR', //failed to add new vm, because the host is in maintenance" 

        // FusionCompute KVM user/role error code" 
        'VM_FC_KVM_NM_10100107_ERROR', //current user only can modify its password" 
        'VM_FC_KVM_NM_10100108_ERROR', //to ensure the security of account password, please change the password" 
        'VM_FC_KVM_NM_10100109_ERROR', //the password is already out of date, please change the password" 
        'VM_FC_KVM_NM_10100112_ERROR', //login failed, please login again" 
        'VM_FC_KVM_NM_10100113_ERROR', //login failed too more, the account is locked for a few minutes" 

        'VM_FC_KVM_NM_10100306_ERROR', //role of user is not exist" 
        'VM_FC_KVM_NM_10100307_ERROR', //the user is not exist" 

        // FusionCompute KVM FM error" 
        'VM_FC_KVM_FM_11100011_ERROR', //query realtime warnning message failed" 
        'VM_FC_KVM_FM_11100013_ERROR', //query history warnning message failed" 
        'VM_FC_KVM_FM_11100015_ERROR', //query task failed" 

        // FusionCompute task error" 
        'VM_FC_KVM_TASK_10800001_ERROR', //this type of task can not be cancel" 
        'VM_FC_KVM_TASK_10800002_ERROR', //the task is already finished, cann't be cancel" 

        'VM_FC_KVM_TASK_BE_CANCELLING_ERROR', //the operation task in FusionCompute is cancelled" 
        'VM_FC_KVM_TASK_BE_FAILED_ERROR', //the operation task in FusionCompute failed" 

        'VM_FC_KVM_FAILED_TO_LOCATE_HOST_IN_SITE_ERROR', //unable to locate the host in site error" 
        'VM_FC_KVM_FAILED_TO_LOCATE_CLUSTER_IN_SITE_ERROR', //unable to locate the cluster in site error" 
        'VM_FC_KVM_SITE_LIST_EMPTY_ERROR', //site list is empty, may be due to the" 
        'VM_FC_KVM_URN_INVALID_ERROR', //urn is invalid" 

        'VM_FC_KVM_TASK_ENTITY_URN_NULL_ERROR', //FC internal task's entity urn is null" 
        'VM_FC_KVM_URI_INVALID_ERROR', //uri string is invalid" 
        'VM_FC_KVM_INVALID_IR_DATASTORE_INFO', //"Invalid instant recovery datastore info" 
        'VM_FC_KVM_INVALID_CBT_BITMAP_BLOCK_LEN', //invalid FC cbt bitmap block length" 
        'VM_FC_KVM_INVALID_CBT_BITMAP_LENGTH', //invlaid FC cbt bitmap string length" 
        'VM_FC_KVM_PREPARE_RESOURCE_ERROR', //prepare backup or recovery resource error" 
        'VM_FC_KVM_DELETE_BACKUP_RESOURCE_ERROR', //delete backup or recovery resource error" 
        'VM_FC_KVM_BITMAP_OFFSET_IS_NOT_ALIGN', //request bitmap offset is not align erorr" 

        'VM_FC_KVM_NBD_INVALID_OP_CODE_ERROR', //invalid request op code" 
        'VM_FC_KVM_NBD_FILE_NOT_EXIST_ERROR', //remote disk file is not exist error" 
        'VM_FC_KVM_NBD_INVALID_TOKEN_ERROR', //invalid request token" 
        'VM_FC_KVM_NBD_INVALID_OPEN_FLAGS_ERROR', //invalid request open flag" 
        'VM_FC_KVM_NBD_FILE_HANDLE_NOT_EXIST_ERROR', //remote file handle is not exist error" 
        'VM_FC_KVM_NBD_START_BLOCK_OUT_OF_RANGE_ERROR', //request start block is invalid" 
        'VM_FC_KVM_NBD_READ_SIZE_OUT_OF_RANGE', //request read size is invalid" 
        'VM_FC_KVM_NBD_READ_FAILED_ERROR', //request to read file error" 
        'VM_FC_KVM_NBD_WRITE_SIZE_OUT_OF_RANGE', //request write size is invalid" 
        'VM_FC_KVM_NBD_WRITE_FAILED_ERROR', //request to write file error" 
        'VM_FC_KVM_NBD_UNKNWON_ERROR', //unknown remote fc server error" 
        'VM_FC_KVM_INC_MODE_AND_TRANSPORT_MODE_NOT_MATCH_ERROR', //incremental mode and transport mode is not match error, API LAN transport mode only support CBT incremantal mode" 
        'VM_FC_KVM_GET_BACKUP_SERVER_IP_LIST_IS_EMPTY', //backup server ip list is empty" 
        'VM_DISK_LIST_IS_EMPTY_ERROR', //disk list of task is empty, exclude all disk or vm is not include disk is not support the" 

        'VM_FC_KVM_IS_ROOT_SNAPSHOT_ERROR', //snapshot is root error

        'VM_OVIRT_CBT_BACKUP_ID_IS_NULL_ERROR', //create cbt backup success, but response message is invalid
        'VM_OVIRT_CBT_BACKUP_CREATE_FAILED', //failed to create cbt backup
        'VM_OVIRT_CBT_BACKUP_CREATE_TIMEOUT', //create cbt backup timeout
        'VM_OVIRT_GET_BACKUP_INFO_ERROR', //failed to get cbt backup info
        'VM_OVIRT_TRANSFER_ID_IS_NULL_ERROR', //create imagetransfer success, but response transfer id is invalid
        'VM_OVIRT_TRANSFER_URL_IS_NULL_ERROR', //create imagetransfer success, but response transfer url is invalid

        'VM_FC_MACHINE_HAS_INDEP_DISK_ERORR', //backup vm which contains independent disk is not support
        'VM_FC_MACHINE_HAS_SHARABLE_DISK_ERROR', //backup vm which contains sharable disk is not support

        'BACKUP_COPY_NODE_LOCATION_ERROR', //the location of backup copy service is different from source backup timepoint data, please check if source backup task changed the storage"}
        'VM_FC_KVM_STORAGE_10410003_ERROR',						//Target storage resource is insufficient
        'VM_OVIRT_ENGINE_VERSION_IS_NOT_SUPPORT_CBT',				// engine version is to low. it's not support CBT features(engine version should be bigger than 4.4.7)
        'VM_FC_KVM_VM_10300797_ERROR', 								//This operation is not allowed because the VM has disks whose bus type is IDE

        'VM_OVIRT_CREATE_TRANSFER_ERROR',                           //create imagetransfer error, detect invalid phase(黄总跟你说中文翻译)
        'VM_ICS_KVM_INVALID_CBT_BITMAP_BLOCK_LEN',					//invalid ICS KVM cbt bitmap block length（无效的cbt位图块长度）
        'VM_ICS_KVM_BITMAP_OFFSET_IS_NOT_ALIGN',					//request bitmap offset is not align erorr （请求的bitmap偏移没有对齐）
        'VM_ICS_KVM_CBT_DIFF_BACKUP_MODEL_CREATE_SNAPSHOT_ERROR',     //ICS KVM not support cbt diff backup（cbt不支持差异备份）
        'VM_ICS_KVM_V2_QCOW_DISK_NOT_SUPPORT_HIGH_SPEED_MODEL',  //v2 qcow disk not support high speed model(V2 QCOW磁盘虚拟机不支持增量高速模式)
        'VM_ICS_KVM_SYNC_CACHE_TO_PHYSICAL_ERROR',	             // sync cache to physical error 刷新磁盘缓存失败
        'VM_CONVERT_LIC_NUM_EXHUAST',                           //convert license is exhaust

        'VM_ICS_KVM_PUB_220008_ERROR',
        'VM_ICS_KVM_PUB_220009_ERROR',
        'VM_ICS_KVM_PUB_220010_ERROR',
        'VM_ICS_KVM_PUB_220012_ERROR',
        'VM_ICS_KVM_PUB_220039_ERROR',
        'VM_ICS_KVM_PUB_220044_ERROR',
        'VM_ICS_KVM_PUB_220047_ERROR',
        'VM_ICS_KVM_PUB_220049_ERROR',
        'VM_ICS_KVM_PUB_220055_ERROR',
        'VM_ICS_KVM_PUB_220122_ERROR',
        'VM_ICS_KVM_PUB_220158_ERROR',
        'VM_ICS_KVM_PUB_220228_ERROR',
        'VM_ICS_KVM_RESOURCE_220001_ERROR',
        'VM_ICS_KVM_RESOURCE_220002_ERROR',
        'VM_ICS_KVM_RESOURCE_220003_ERROR',
        'VM_ICS_KVM_RESOURCE_220004_ERROR',
        'VM_ICS_KVM_RESOURCE_220006_ERROR',
        'VM_ICS_KVM_RESOURCE_220011_ERROR',
        'VM_ICS_KVM_RESOURCE_220013_ERROR',
        'VM_ICS_KVM_RESOURCE_220017_ERROR',
        'VM_ICS_KVM_RESOURCE_220030_ERROR',
        'VM_ICS_KVM_RESOURCE_220032_ERROR',
        'VM_ICS_KVM_RESOURCE_220033_ERROR',
        'VM_ICS_KVM_RESOURCE_220034_ERROR',
        'VM_ICS_KVM_RESOURCE_220035_ERROR',
        'VM_ICS_KVM_RESOURCE_220045_ERROR',
        'VM_ICS_KVM_RESOURCE_220046_ERROR',
        'VM_ICS_KVM_RESOURCE_220048_ERROR',
        'VM_ICS_KVM_RESOURCE_220051_ERROR',
        'VM_ICS_KVM_RESOURCE_220053_ERROR',
        'VM_ICS_KVM_RESOURCE_220056_ERROR',
        'VM_ICS_KVM_RESOURCE_220057_ERROR',
        'VM_ICS_KVM_RESOURCE_220059_ERROR',
        'VM_ICS_KVM_RESOURCE_220062_ERROR',
        'VM_ICS_KVM_RESOURCE_220063_ERROR',
        'VM_ICS_KVM_RESOURCE_220065_ERROR',
        'VM_ICS_KVM_RESOURCE_220067_ERROR',
        'VM_ICS_KVM_RESOURCE_220069_ERROR',
        'VM_ICS_KVM_RESOURCE_220073_ERROR',
        'VM_ICS_KVM_RESOURCE_220074_ERROR',
        'VM_ICS_KVM_RESOURCE_220077_ERROR',
        'VM_ICS_KVM_RESOURCE_220078_ERROR',
        'VM_ICS_KVM_RESOURCE_220080_ERROR',
        'VM_ICS_KVM_RESOURCE_220082_ERROR',
        'VM_ICS_KVM_RESOURCE_220084_ERROR',
        'VM_ICS_KVM_RESOURCE_220087_ERROR',
        'VM_ICS_KVM_RESOURCE_220089_ERROR',
        'VM_ICS_KVM_RESOURCE_220090_ERROR',
        'VM_ICS_KVM_RESOURCE_220091_ERROR',
        'VM_ICS_KVM_RESOURCE_220095_ERROR',
        'VM_ICS_KVM_RESOURCE_220096_ERROR',
        'VM_ICS_KVM_RESOURCE_220098_ERROR',
        'VM_ICS_KVM_RESOURCE_220100_ERROR',
        'VM_ICS_KVM_RESOURCE_220104_ERROR',
        'VM_ICS_KVM_RESOURCE_220106_ERROR',
        'VM_ICS_KVM_RESOURCE_220107_ERROR',
        'VM_ICS_KVM_RESOURCE_220108_ERROR',
        'VM_ICS_KVM_RESOURCE_220109_ERROR',
        'VM_ICS_KVM_RESOURCE_220110_ERROR',
        'VM_ICS_KVM_RESOURCE_220111_ERROR',
        'VM_ICS_KVM_RESOURCE_220112_ERROR',
        'VM_ICS_KVM_RESOURCE_220113_ERROR',
        'VM_ICS_KVM_RESOURCE_220117_ERROR',
        'VM_ICS_KVM_RESOURCE_220118_ERROR',
        'VM_ICS_KVM_RESOURCE_220123_ERROR',
        'VM_ICS_KVM_RESOURCE_220124_ERROR',
        'VM_ICS_KVM_RESOURCE_220125_ERROR',
        'VM_ICS_KVM_RESOURCE_220127_ERROR',
        'VM_ICS_KVM_RESOURCE_220128_ERROR',
        'VM_ICS_KVM_RESOURCE_220130_ERROR',
        'VM_ICS_KVM_RESOURCE_220133_ERROR',
        'VM_ICS_KVM_RESOURCE_220134_ERROR',
        'VM_ICS_KVM_RESOURCE_220135_ERROR',
        'VM_ICS_KVM_RESOURCE_220136_ERROR',
        'VM_ICS_KVM_RESOURCE_220139_ERROR',
        'VM_ICS_KVM_RESOURCE_220141_ERROR',
        'VM_ICS_KVM_RESOURCE_220142_ERROR',
        'VM_ICS_KVM_RESOURCE_220143_ERROR',
        'VM_ICS_KVM_RESOURCE_220144_ERROR',
        'VM_ICS_KVM_RESOURCE_220145_ERROR',
        'VM_ICS_KVM_RESOURCE_220146_ERROR',
        'VM_ICS_KVM_RESOURCE_220151_ERROR',
        'VM_ICS_KVM_RESOURCE_220152_ERROR',
        'VM_ICS_KVM_RESOURCE_220159_ERROR',
        'VM_ICS_KVM_RESOURCE_220160_ERROR',
        'VM_ICS_KVM_RESOURCE_220161_ERROR',
        'VM_ICS_KVM_RESOURCE_220162_ERROR',
        'VM_ICS_KVM_RESOURCE_220163_ERROR',
        'VM_ICS_KVM_RESOURCE_220164_ERROR',
        'VM_ICS_KVM_RESOURCE_220165_ERROR',
        'VM_ICS_KVM_RESOURCE_220166_ERROR',
        'VM_ICS_KVM_RESOURCE_220167_ERROR',
        'VM_ICS_KVM_RESOURCE_220168_ERROR',
        'VM_ICS_KVM_RESOURCE_220175_ERROR',
        'VM_ICS_KVM_RESOURCE_220190_ERROR',
        'VM_ICS_KVM_RESOURCE_220191_ERROR',
        'VM_ICS_KVM_RESOURCE_220195_ERROR',
        'VM_ICS_KVM_RESOURCE_220197_ERROR',
        'VM_ICS_KVM_RESOURCE_220198_ERROR',
        'VM_ICS_KVM_RESOURCE_220199_ERROR',
        'VM_ICS_KVM_RESOURCE_220200_ERROR',
        'VM_ICS_KVM_RESOURCE_220201_ERROR',
        'VM_ICS_KVM_RESOURCE_220202_ERROR',
        'VM_ICS_KVM_RESOURCE_220203_ERROR',
        'VM_ICS_KVM_RESOURCE_220205_ERROR',
        'VM_ICS_KVM_RESOURCE_220206_ERROR',
        'VM_ICS_KVM_RESOURCE_220207_ERROR',
        'VM_ICS_KVM_RESOURCE_220208_ERROR',
        'VM_ICS_KVM_RESOURCE_220209_ERROR',
        'VM_ICS_KVM_RESOURCE_220210_ERROR',
        'VM_ICS_KVM_RESOURCE_220211_ERROR',
        'VM_ICS_KVM_RESOURCE_220216_ERROR',
        'VM_ICS_KVM_RESOURCE_220221_ERROR',
        'VM_ICS_KVM_RESOURCE_220222_ERROR',
        'VM_ICS_KVM_RESOURCE_220227_ERROR',
        'VM_ICS_KVM_RESOURCE_220229_ERROR',
        'VM_ICS_KVM_RESOURCE_220233_ERROR',
        'VM_ICS_KVM_RESOURCE_220235_ERROR',

        'VM_ICS_KVM_NM_220005_ERROR',
        'VM_ICS_KVM_NM_220007_ERROR',
        'VM_ICS_KVM_NM_220015_ERROR',
        'VM_ICS_KVM_NM_220016_ERROR',
        'VM_ICS_KVM_NM_220018_ERROR',
        'VM_ICS_KVM_NM_220019_ERROR',
        'VM_ICS_KVM_NM_220021_ERROR',
        'VM_ICS_KVM_NM_220024_ERROR',
        'VM_ICS_KVM_NM_220025_ERROR',
        'VM_ICS_KVM_NM_220026_ERROR',
        'VM_ICS_KVM_NM_220029_ERROR',
        'VM_ICS_KVM_NM_220031_ERROR',
        'VM_ICS_KVM_NM_299999_ERROR',
        'VM_ICS_KVM_NM_220036_ERROR',
        'VM_ICS_KVM_NM_220037_ERROR',
        'VM_ICS_KVM_NM_220038_ERROR',
        'VM_ICS_KVM_NM_220040_ERROR',
        'VM_ICS_KVM_NM_220041_ERROR',
        'VM_ICS_KVM_NM_220042_ERROR',
        'VM_ICS_KVM_NM_220043_ERROR',
        'VM_ICS_KVM_NM_220050_ERROR',
        'VM_ICS_KVM_NM_220052_ERROR',
        'VM_ICS_KVM_NM_220054_ERROR',
        'VM_ICS_KVM_NM_220058_ERROR',
        'VM_ICS_KVM_NM_220060_ERROR',
        'VM_ICS_KVM_NM_220061_ERROR',
        'VM_ICS_KVM_NM_220066_ERROR',
        'VM_ICS_KVM_NM_220068_ERROR',
        'VM_ICS_KVM_NM_220070_ERROR',
        'VM_ICS_KVM_NM_220071_ERROR',
        'VM_ICS_KVM_NM_220072_ERROR',
        'VM_ICS_KVM_NM_220075_ERROR',
        'VM_ICS_KVM_NM_220076_ERROR',
        'VM_ICS_KVM_NM_220079_ERROR',
        'VM_ICS_KVM_NM_220083_ERROR',
        'VM_ICS_KVM_NM_220085_ERROR',
        'VM_ICS_KVM_NM_220092_ERROR',
        'VM_ICS_KVM_NM_220093_ERROR',
        'VM_ICS_KVM_NM_220097_ERROR',
        'VM_ICS_KVM_NM_220101_ERROR',
        'VM_ICS_KVM_NM_220102_ERROR',
        'VM_ICS_KVM_NM_220103_ERROR',
        'VM_ICS_KVM_NM_220105_ERROR',
        'VM_ICS_KVM_NM_220114_ERROR',
        'VM_ICS_KVM_NM_220115_ERROR',
        'VM_ICS_KVM_NM_220116_ERROR',
        'VM_ICS_KVM_NM_220120_ERROR',
        'VM_ICS_KVM_NM_220121_ERROR',
        'VM_ICS_KVM_NM_220126_ERROR',
        'VM_ICS_KVM_NM_220129_ERROR',
        'VM_ICS_KVM_NM_220132_ERROR',
        'VM_ICS_KVM_NM_220137_ERROR',
        'VM_ICS_KVM_NM_220140_ERROR',
        'VM_ICS_KVM_NM_220147_ERROR',
        'VM_ICS_KVM_NM_220148_ERROR',
        'VM_ICS_KVM_NM_220149_ERROR',
        'VM_ICS_KVM_NM_220150_ERROR',
        'VM_ICS_KVM_NM_220153_ERROR',
        'VM_ICS_KVM_NM_220154_ERROR',
        'VM_ICS_KVM_NM_220155_ERROR',
        'VM_ICS_KVM_NM_220156_ERROR',
        'VM_ICS_KVM_NM_220157_ERROR',
        'VM_ICS_KVM_NM_220169_ERROR',
        'VM_ICS_KVM_NM_220170_ERROR',
        'VM_ICS_KVM_NM_220171_ERROR',
        'VM_ICS_KVM_NM_220172_ERROR',
        'VM_ICS_KVM_NM_220173_ERROR',
        'VM_ICS_KVM_NM_220174_ERROR',
        'VM_ICS_KVM_NM_220176_ERROR',
        'VM_ICS_KVM_NM_220177_ERROR',
        'VM_ICS_KVM_NM_220178_ERROR',
        'VM_ICS_KVM_NM_220179_ERROR',
        'VM_ICS_KVM_NM_220180_ERROR',
        'VM_ICS_KVM_NM_220181_ERROR',
        'VM_ICS_KVM_NM_220182_ERROR',
        'VM_ICS_KVM_NM_220183_ERROR',
        'VM_ICS_KVM_NM_220184_ERROR',
        'VM_ICS_KVM_NM_220185_ERROR',
        'VM_ICS_KVM_NM_220186_ERROR',
        'VM_ICS_KVM_NM_220188_ERROR',
        'VM_ICS_KVM_NM_220189_ERROR',
        'VM_ICS_KVM_NM_220192_ERROR',
        'VM_ICS_KVM_NM_220193_ERROR',
        'VM_ICS_KVM_NM_220194_ERROR',
        'VM_ICS_KVM_NM_220204_ERROR',
        'VM_ICS_KVM_NM_220212_ERROR',
        'VM_ICS_KVM_NM_220213_ERROR',
        'VM_ICS_KVM_NM_220214_ERROR',
        'VM_ICS_KVM_NM_220215_ERROR',
        'VM_ICS_KVM_NM_220217_ERROR',
        'VM_ICS_KVM_NM_220218_ERROR',
        'VM_ICS_KVM_NM_220219_ERROR',
        'VM_ICS_KVM_NM_220220_ERROR',
        'VM_ICS_KVM_NM_220226_ERROR',
        'VM_ICS_KVM_NM_220230_ERROR',
        'VM_ICS_KVM_NM_220231_ERROR',

        'VM_ICS_KVM_TOOL_220014_ERROR',
        'VM_ICS_KVM_TOOL_220020_ERROR',
        'VM_ICS_KVM_TOOL_220022_ERROR',
        'VM_ICS_KVM_TOOL_220023_ERROR',
        'VM_ICS_KVM_TOOL_220232_ERROR',

        'VM_ICS_KVM_SNAP_220027_ERROR',
        'VM_ICS_KVM_SNAP_220028_ERROR',
        'VM_ICS_KVM_SNAP_220064_ERROR',
        'VM_ICS_KVM_SNAP_220081_ERROR',
        'VM_ICS_KVM_SNAP_220086_ERROR',
        'VM_ICS_KVM_SNAP_220088_ERROR',
        'VM_ICS_KVM_SNAP_220094_ERROR',
        'VM_ICS_KVM_SNAP_220119_ERROR',
        'VM_ICS_KVM_SNAP_220131_ERROR',
        'VM_ICS_KVM_SNAP_220138_ERROR',
        'VM_ICS_KVM_SNAP_220187_ERROR',

        'KVM_OPENSTACK_SET_VOLUME_IMAGE_METADATA_ERROR',    // KvmOpenStack Error : set openstack volume image metadata error
        'KVM_OPENSTACK_GET_VOLUME_QOS_INFO_ERROR',          //KvmOpenStack Error : get openstack volume qos info error
        'KVM_OPENSTACK_SET_VOLUME_QOS_INFO_ERROR',          //KvmOpenStack Error : set openstack volume qos info error

        'VM_ICS_KVM_QCOW_DISK_NOT_SUPPORT_CBT_MODEL', 		//qcow disk not support CBT model"
        'VM_DETECTED_INDEPENDENT_PERSISTENT_DISK_ERROR', 	//detected vm contains independent-persistent disk, not support backup, please exclude the independent-disk disk and try again"

        // Ceph s3 cloud storage error code
        'CEPH_S3_CLOUD_STORAGE_BUCKET_NAME_TOO_LONG_ERROR',		// Ceph s3 cloud storage: bucket name too long
        'CEPH_S3_CLOUD_STORAGE_BUCKET_NAME_TOO_SHORT_ERROR',		// Ceph s3 cloud storage: bucket name too short
        'CEPH_S3_CLOUD_STORAGE_NAME_LOOKUP_ERROR',				// Ceph s3 cloud storage: not find the user name
        'CEPH_S3_CLOUD_STORAGE_CONNECT_ERROR',					// Ceph s3 cloud storage: connect error
        'CEPH_S3_CLOUD_STORAGE_ACCESS_DENIED_ERROR',				// Ceph s3 cloud storage: access denied
        'CEPH_S3_CLOUD_STORAGE_ACCOUNT_PROBLEM_ERROR',			// Ceph s3 cloud storage: access problem
        'CEPH_S3_CLOUD_STORAGE_BUCKET_ALREADY_EXIST_ERROR',		// Ceph s3 cloud storage: bucket already exist
        'CEPH_S3_CLOUD_STORAGE_BUCKET_NOT_EMPTY_ERROR',		    // Ceph s3 cloud storage: bucket not empty
        'CEPH_S3_CLOUD_STORAGE_ENTITY_TOO_SMALL_ERROR',			// Ceph s3 cloud storage: entity too small
        'CEPH_S3_CLOUD_STORAGE_ENTITY_TOO_LARGE_ERROR',			// Ceph s3 cloud storage: entity too large
        'CEPH_S3_CLOUD_STORAGE_INVALID_ACCESS_KEY_ID_ERROR',		// Ceph s3 cloud storage: invalid access key id
        'CEPH_S3_CLOUD_STORAGE_INVALID_BUCKET_NAME_ERROR',		// Ceph s3 cloud storage: invalid bucket name
        'CEPH_S3_CLOUD_STORAGE_INVALID_BUCKET_STATE_ERROR',		// Ceph s3 cloud storage: invalid bucket state
        'CEPH_S3_CLOUD_STORAGE_INVALID_LOCATION_CONSTRAINT_ERROR',// Ceph s3 cloud storage: invalid location constraint
        'CEPH_S3_CLOUD_STORAGE_INVALID_OBJECT_STATE_ERROR',		// Ceph s3 cloud storage: invalid object state
        'CEPH_S3_CLOUD_STORAGE_KEY_TOO_LONG_ERROR',				// Ceph s3 cloud storage: key too long
        'CEPH_S3_CLOUD_STORAGE_NO_SUCH_BUCKET_ERROR',				// Ceph s3 cloud storage: no such bucket
        'CEPH_S3_CLOUD_STORAGE_NO_SUCH_KEY_ERROR',				// Ceph s3 cloud storage: no such key
        'CEPH_S3_CLOUD_STORAGE_NO_SUCH_BUCKET_POLICY_ERROR',		// Ceph s3 cloud storage: no such bucket policy
        'CEPH_S3_CLOUD_STORAGE_SERVICE_UNAVAILABLE_ERROR',		// Ceph s3 cloud storage: service unavailable
        'CEPH_S3_CLOUD_STORAGE_QUOTA_EXCEEDED_ERROR',				// Ceph s3 cloud storage: quota exceeded
        'CEPH_S3_CLOUD_STORAGE_REQUEST_TIMEOUT_ERROR',			// Ceph s3 cloud storage: request timeout, check ceph state
        'CEPH_S3_CLOUD_STORAGE_S3_INIT_ERROR',					// Ceph s3 cloud storage: ceph s3 init error

        // Winhong kvm error code 
        'KVM_WINHONG_HOST_POOL_LIST_EMPTY_ERROR',					// kvm winhong: host pool list is empty error
        'KVM_WINHONG_SWITCH_NOT_EXIST_ERROR',						// kvm winhong: virtual switch is not exist error
        'KVM_WINHONG_POWEROFF_VM_ERROR',							// kvm winhong: power off vm error
        'KVM_WINHONG_POWERON_VM_ERROR',							// kvm winhong: power on vm error
        'KVM_WINHONG_CREATE_VM_ERROR',                            // kvm winhong: create vm error
        'KVM_WINHONG_PORT_GROUPS_NOT_EXIST_ERROR',				// kvm winhong: port groups is not exist error
        'KVM_WINHONG_VM_NOT_EXIST_ERROR',							// kvm winhong: vm is not exist error
        'KVM_WINHONG_DELETE_VM_ERROR',							// kvm winhong: delete vm error
        'KVM_WINHONG_DELETE_STORAGE_POOL_ERROR',					// kvm winhong: delete storage pool error
        'KVM_WINHONG_REFRESH_STORAGE_POOL_ERROR',					// kvm winhong: refresh storage pool error
        'KVM_WINHONG_RESOURCE_NOT_EXIST_ERROR',					// kvm winhong: resource is not exist error
        'KVM_WINHONG_CREATE_NAS_STORAGE_POOL_ERROR',				// kvm winhong: create nas storage pool error
        'KVM_WINHONG_DELETE_VOLUME_ERROR',						// kvm winhong: delete volume error
        'KVM_WINHONG_CREATE_NAS_STORAGE_STORE_ERROR',				// kvm winhong: create nas storage store error
        'KVM_WINHONG_CREATE_SNAPSHOT_ERROR',						// kvm winhong: create snapshot error
        'KVM_WINHONG_DELETE_SNAPSHOT_ERROR',						// kvm winhong: delete snapshot error
        'KVM_WINHONG_CEPH_CONFIG_NOT_EXIST_ERROR',				// kvm winhong: ceph config is not exist error
        'KVM_WINHONG_CONNECT_VIRT_AGENT_ERROR',					// kvm winhong: connect virt agent error
        'KVM_WINHONG_STORAGE_POOL_NOT_EXIST_ERROR',				// kvm winhong: storage pool is not exist error
        'KVM_WINHONG_STORAGE_VOLUME_NOT_EXIST_ERROR',				// kvm winhong: storage volume is not exist error
        'KVM_WINHONG_SCAN_STORE_RESOURCE_ERROR',					// kvm winhong: scan store resource error
        'KVM_WINHONG_STOP_STORAGE_POOL_ERROR',					// kvm winhong: stop storage pool error
        'KVM_WINHONG_START_STORAGE_POOL_ERROR',					// kvm winhong: start storage pool error
        'KVM_WINHONG_API_ERROR',									// kvm winhong: api return error
        'KVM_WINHONG_MODIFY_VM_BOOT_ERROR',						// modify vm boot error
        'KVM_WINHONG_VM_INSTALL_TOOLS_ERROR',					//  vm install tools error
        'KVM_WINHONG_MODIFY_VM_CONSOLE_ERROR',					// modify vm console error
        'KVM_WINHONG_MODIFY_INTERFACE_ERROR',					// modify interface error
        'KVM_WINHONG_VM_ADD_VIDEO_ERROR',						// vm add video error
        'KVM_WINHONG_MODIFY_VM_VIDEO_ERROR',					// modify vm video error

        'KVM_OPENSTACK_GET_ALL_FLAVOR_TYPES_ERROR',				// get all flavor types error
        'KVM_OPENSTACK_GET_FLAVOR_TYPE_DETAIL_ERROR',			// get flavor type detail error
        'KVM_OPENSTACK_V2V_NOT_SUPPORT_IMAGE_START',			// openstack v2v not support image start
        'KVM_OPENSTACK_APPLIANCE_RECOVERY_NOT_SUPPORT_IMAGE_START', //KvmOpenStack Error: openstack appliance mode recovery not support image start

        'VM_V2V_APPLIANCE_CHOOSE_ERROR',							// VM Error: appliance choose error

        'KVM_WINHONG_GET_HOST_ERROR',							// winhong kvm: get host error（获取主机信息失败）
        'KVM_WINHONG_GET_HOST_RESOURCE_ERROR',					// winhong kvm: get host resource error（获取主机资源失败）
        'KVM_WINHONG_GET_HOST_POOL_ERROR',						// winhong kvm: get host pool error（获取主机池失败）
        'KVM_WINHONG_GET_CLUSTER_ERROR',						// winhong kvm: get cluster error（获取集群信息失败）
        'KVM_WINHONG_GET_VM_ERROR',								// winhong kvm: get vm error（获取虚拟机信息失败）
        'KVM_WINHONG_GET_VM_DISK_ERROR',						// winhong kvm: get vm disk error（获取虚拟机磁盘失败）
        'KVM_WINHONG_GET_VM_CONSOLE_ERROR',						// winhong kvm: get vm console error（获取虚拟机控制台信息失败）
        'KVM_WINHONG_GET_STORAGE_POOL_ERROR',					// winhong kvm: get storage pool error（获取存储池失败）
        'KVM_WINHONG_GET_STORAGE_VOLUME_ERROR',					// winhong kvm: get storage volume error（获取存储卷失败）
        'KVM_WINHONG_GET_STORAGE_STORE_ERROR',					// winhong kvm: get storage store error（获取存储设备失败）
        'KVM_WINHONG_GET_STORE_RESOURCE_ERROR',					// winhong kvm: get store resource error（获取存储设备资源失败）
        'KVM_WINHONG_GET_SNAPSHOT_ERROR',						// winhong kvm: get snapshot error（获取快照失败）
        'KVM_WINHONG_GET_PORT_GROUP_ERROR',						// winhong kvm: get port group error（获取端口组信息失败）
        'KVM_WINHONG_GET_PRODUCT_VERSION_ERROR',				// winhong kvm: get product version error（获取版本失败）
        'KVM_WINHONG_GET_VSWITCH_ERROR',						// winhong kvm: get virual switch error（获取虚拟交换机失败）
        'KVM_WINHONG_GET_TASK_ERROR',							// winhong kvm: get task error（获取任务信息失败）
        'KVM_WINHONG_LOGIN_NODE_ERROR',							// winhong kvm: login to winhong node error（登录失败）

        /*********sure backup************/
        /*******add by BrinePineapple****/
        //virtual lab
        'SR_DEPLOY_SURE_BACKUP_NETWORK_ERROR',				//vmware: deploy sure backup network error
        'SR_ANALYSIS_IP_ADDRESS_ERROR',						//vmware: analysis ip address error
        'SR_ANALYSIS_NETMASK_ERROR',							//vmware: analysis netmask error
        'SR_NETMASK_ILLEGAL',									//vmware: netmask is illegal
        'SR_VIRTUAL_LAB_ALL_THE_ISOLATED_SEGMENT_USED',		//vmware: isolated segment is used
        'SR_VIRTUAL_LAB_NETWORK_MAP_LIST_ERROR',				//vmware: network map list error
        'SR_VIRTUAL_LAB_ISOLATED_NETWORK_NOT_ENOUGH',			//vmware: sure backup of virtual lab is not enought
        'SR_VIRTUAL_ANALYSIS_PROXY_NETWORK_ERROR',			//vmware: analysis virtual lab proxy error
        'SR_GET_VIRTUAL_LAB_PROXY_INFO_ERROR',				//vmware: virtual lab proxy info error
        'SR_DELETE_VIRTUAL_LAB_ERROR',						//vmware: delete virtual lab error
        'SR_GET_RESOURCE_POOL_ERROR',							//vmware: get resource pool error
        'SR_ADD_RESOURCE_POOL_ERROR',							//vmware: add resource pool error
        'SR_GET_HOST_CLUSTER_ERROR',							//vmware: get host cluster error
        'SR_ADD_FOLDER_ERROR',								//vmware: add folder error
        'SR_ADD_NETCARD_FOR_VM_ERROR',						//vmware: add netcard for vm error
        'SR_NETWORK_MAP_LIST_ERROR',							//vmware: network map list error
        'SR_DELETE_NETWORK_MAP_ERROR',						//vmware: delete network map error
        'SR_REMOVE_RESOURCE_POOL_ERROR',						//vmware: remove resource pool error
        'SR_REMOVE_FOLDER_ERROR',								//vmware: remove folder error
        'VM_INSTANT_RECOVERY_NFS_SERVER_IP_NOT_MATCH_ERROR',		//vmware: NFS server is not match
        'SR_GET_HOST_CONFIG_MANAGER_ERROR',					//vmware: get host config manager error
        'SR_GET_VM_IP_ADDRESS_ERROR',							//vmware: get vm ip address error
        //sure backup recovery task 
        'SR_WITHOUT_ISOLATED_NETWORK',						//vmware: without isolated network
        'SR_BUILD_SURE_BACKUP_VM_LIST_ERROR',					//vmware: build sure backup vm list error

        //verifited way 
        'SR_SCREEN_SHOT_VM_ERROR',							//vmware: screen shot error
        'SR_PING_TEST_VM_ERROR',								//vmware: ping test error
        'SR_HEARTBEAT_TEST_VM_ERROR',							//vmware: heartbeat test vm error
        'SR_DOWNLOAD_SCREEN_SHOT_ERROR',						//vmware: download screen shot error

        //add for backup and recovery UEFI boot file
        'VMWARE_UPLOAD_FILE_ERROR',								//vmware: upload file error
        'VMWARE_DOWNLOAD_FILE_ERROR',								//vmware: download file error
        'VMWARE_RECOVERY_BOOT_FILE_ERROR',						//vmware: recovery boot file error
        'VMWARE_BACKUP_BOOT_FILE_ERROR',							//vmware: backup boot file error

        'KVM_SMARTX_DATA_CENTER_GET_ERROR',					// smartx kvm: get data center error（获取数据中心失败）
        'KVM_SMARTX_ORGANIZATION_GET_ERROR',				// smartx kvm: get organization error（获取组织失败）
        'KVM_SMARTX_ORGANIZATION_NOT_EXIST_ERROR',				// smartx kvm: the organization does exist error（组织不存在）
        'KVM_SMARTX_HOST_NOT_EXIST_ERROR',					// smartx kvm: the host does not exist error（主机不存在）
        'KVM_SMARTX_HOST_GET_ERROR',					// smartx kvm: get host info error（获取主机信息失败）
        'KVM_SMARTX_CLUSTER_GET_ERROR',					// smartx kvm: get cluster info error（获取集群信息失败）
        'KVM_SMARTX_VLAN_NOT_EXIST_ERROR',					// smartx kvm: the vlan does not exist error（vlan不存在）
        'KVM_SMARTX_VLAN_GET_ERROR',					// smartx kvm: get vlan info error（获取vlan信息失败）
        'KVM_SMARTX_SWITCH_GET_ERROR',					// smartx kvm: get virtual switch info error（获取虚拟交换机失败）
        'KVM_SMARTX_SNAPSHOT_CREATE_ERROR',				// smartx kvm: the vm creates snapshot error（创建快照失败）
        'KVM_SMARTX_SNAPSHOT_NOT_EXIST_ERROR',				// smartx kvm: the snapshot does not exist error（快照不存在）
        'KVM_SMARTX_SNAPSHOT_DELETE_ERROR',				// smartx kvm: the vm deletes snapshot error（删除快照失败）
        'KVM_SMARTX_SNAPSHOT_GET_ERROR',					// smartx kvm: get snapshot info error（获取快照信息失败）
        'KVM_SMARTX_LUN_SNAPSHOT_GET_ERROR',				// smartx kvm: get lun snapshot info error（获取lun快照信息失败）
        'KVM_SMARTX_ISCSI_LUN_GET_ERROR',					// smartx kvm: get iscsi lun error（获取iscsi lun信息失败）
        'KVM_SMARTX_VM_NOT_EXIST_ERROR',					// smartx kvm: the vm does not exist error（虚拟机不存在）
        'KVM_SMARTX_VM_CREATE_ERROR',					// smartx kvm: the vm creates error（创建虚拟机失败）
        'KVM_SMARTX_VM_DELETE_ERROR',					// smartx kvm: the vm deletes error（删除虚拟机失败）
        'KVM_SMARTX_VM_GET_ERROR',					// smartx kvm: get vm info error（获取虚拟机信息失败）
        'KVM_SMARTX_VM_UPDATE_ERROR',					// smartx kvm: update vm info error（更新虚拟机失败）
        'KVM_SMARTX_VM_POWEROFF_ERROR',					// smartx kvm: the vm powers off error（关闭虚拟机失败）
        'KVM_SMARTX_VM_POWERON_ERROR',					// smartx kvm: the vm powers on error（打开虚拟机失败）
        'KVM_SMARTX_VM_DISK_GET_ERROR',					// smartx kvm: get vm disk info error（获取虚拟机磁盘信息失败）
        'KVM_SMARTX_VM_VOLUME_GET_ERROR',					// smartx kvm: get vm volume info error（获取虚拟卷失败）
        'KVM_SMARTX_TASK_GET_ERROR',					// smartx kvm: get task info error（获取任务信息失败）
        'KVM_SMARTX_LOGIN_NODE_ERROR',					// smartx kvm: login to smartx node error（登录到smartx节点失败）
        'KVM_SMARTX_ZBS_READ_VOLUME_DATA_ERROR',				// smartx kvm: read volume data error（读取虚拟卷数据失败）
        'KVM_SMARTX_ZBS_LIST_VOLUME_EXTENT_ERROR',				// smartx kvm: list volume extents error（列出虚拟卷区段失败）
        'KVM_SMARTX_ZBS_CREATE_ERROR',					// smartx kvm: create zbs object error（创建zbs对象失败）
        'KVM_SMARTX_ZBS_WRITE_DATA_ERROR',					// smartx kvm: zbs writes data error（虚拟卷写入数据失败）
        'KVM_SMARTX_ZBS_CONFIG_PARAM_GET_ERROR',				// smartx kvm: get zbs config file param error（获取配置文件参数失败）

        // for ceph iscsi
        'KVM_XSKY_CEPH_LOGIN_GET_TOKENID_ERROR',		// Kvm Xsky Ceph Error: get xsky ceph token id from json value error（获取ceph token错误）
        'KVM_XSKY_CEPH_CLONE_SNAP_TO_VOLUME_TIME_OUT',	// Kvm Xsky Ceph Error: clone snap to volume time out（克隆快照到卷超时）
        'KVM_XSKY_CEPH_CLONE_SNAP_TO_VOLUME_ERROR',	// Kvm Xsky Ceph Error: clone snap to volume error（克隆快照到卷错误）
        'KVM_XSKY_CEPH_DELETE_VOLUME_TIME_OUT',		// Kvm Xsky Ceph Error: delete volume time out（删除卷超时）
        'KVM_XSKY_CEPH_CREATE_ACCESS_PATH_TIME_OUT',	// Kvm Xsky Ceph Error: create access path time out（创建访问路径超时）
        'KVM_XSKY_CEPH_CREATE_ACCESS_PATH_ERROR',		// Kvm Xsky Ceph Error: create access path error（创建访问路径错误）
        'KVM_XSKY_CEPH_DELETE_ACCESS_PATH_TIME_OUT',	// Kvm Xsky Ceph Error: delete access path time out（删除访问路径超时）

        'VM_ICS_KVM_NFS_NOT_SUPPORT_CBT_SNAPSHOTS',  //ics api error:nfs storage does not support cbt snapshots 

        // vmware
        'VMWARE_GET_HOST_MOUNT_INFO_ERROR',						// vmware get host mount info error

        'VMWARE_RENAME_OBJECT_ERROR', 				//Vmware rename object error
        'VMWARE_MOVE_VM_TO_NEW_LOCATION_ERROR', 	//Vmware move vm to new location error

        // Smartx kvm error code
        'KVM_SMARTX_STORAGE_POLICY_GET_ERROR',					// smartx kvm: get storage policy error（获取存储策略失败）
        'KVM_SMARTX_VM_FOLDER_GET_ERROR',						// smartx kvm: get vm folder error（获取虚拟机放置组失败）
        'KVM_SMARTX_ISCSI_TARGET_GET_ERROR',						// smartx kvm: get iscsi target error（获取iscsi target失败）
        'KVM_SMARTX_VDS_GET_ERROR',							// smartx kvm: get vds error（获取vds失败）
        'KVM_SMARTX_API_VERSION_ERROR',							// smartx kvm: get api version error（获取API版本失败）

        // sangfor kvm
        'KVM_SANGFOR_SNAPSHOT_COUNT_EXCEED_ERROR',				// sangfor kvm: excessive number of snapshots（快照数量过多）

        //openstack kvm
        'KVM_OPENSTACK_GET_CINDER_API_VERSION_ERROR',				// KvmOpenStack Error: openstack get cinder api version error(获取cinder api版本错误)
        'KVM_OPENSTACK_GET_GROUP_TYPES_ERROR',						// KvmOpenStack Error: openstack get group types error(获取组类型错误)
        'KVM_OPENSTACK_GET_VOLUME_GROUPS_ERROR',					// KvmOpenStack Error: openstack get volume groups error(获取卷组信息错误)
        'KVM_OPENSTACK_CREATE_VOLUME_GROUP_ERROR',					// KvmOpenStack Error: openstack create volume group error(创建卷组错误)
        'KVM_OPENSTACK_CREATE_VOLUME_GROUP_TIMEOUT',				// KvmOpenStack Error: openstack create volume group time out(创建卷组超时)
        'KVM_OPENSTACK_DELETE_VOLUME_GROUP_ERROR',					// KvmOpenStack Error: openstack delete volume group error(删除卷组错误)
        'KVM_OPENSTACK_UPDATE_VOLUME_GROUP_ERROR',					// KvmOpenStack Error: openstack update volume group error(更新卷组错误)
        'KVM_OPENSTACK_GET_GROUP_SNAPS_ERROR',						// KvmOpenStack Error: openstack get group snaps error(获取组快照错误)
        'KVM_OPENSTACK_CREATE_GROUP_SNAP_ERROR',					// KvmOpenStack Error: openstack create group snap error(创建组快照错误)
        'KVM_OPENSTACK_DELETE_GROUP_SNAP_ERROR',					// KvmOpenStack Error: openstack delete group snap error(删除组快照错误)
        'KVM_OPENSTACK_CREATE_GROUP_SNAP_TIMEOUT',					// KvmOpenStack Error: openstack create group snap time out(创建组快照超时)
        'KVM_OPENSTACK_DELETE_GROUP_SNAP_TIMEOUT',					// KvmOpenStack Error: openstack delete group snap time out(删除组快照超时)
        'KVM_OPENSTACK_UPDATE_VOLUMES_IN_VOLUME_GROUP_ERROR',		// KvmOpenStack Error: openstack update volumes in volume group(更新卷组超时)

        //ics_vvdk error code
        'KVM_ICS_VVDK_INIT_HANDLE_ERROR', //KvmIcsVvdk Error: ics_vvdk init handle error
        'KVM_ICS_VVDK_CREATE_CONNECTION_ERROR', //KvmIcsVvdk Error : ics_vvdk create connect to iCenter error
        'KVM_ICS_VVDK_OPEN_VIRTUAL_DISK_FILE_ERROR', //KvmIcsVvdk Error : ics_vvdk open virtual disk file error
        'KVM_ICS_VVDK_CLOSE_VIRTUAL_DISK_FILE_ERROR', //KvmIcsVvdk Error : ics_vvdk close virtual disk file error

        //proxmox error code
        'KVM_PROXMOX_API_VERSION_GET_ERROR', //proxmox: get api version error
        'KVM_PROXMOX_RESOURCE_GET_ERROR', //proxmox: get resource error
        'KVM_PROXMOX_RESOURCE_POOL_GET_ERROR', //proxmox: get resource pool error
        'KVM_PROXMOX_CLUSTER_GET_ERROR', //proxmox: get cluster error
        'KVM_PROXMOX_CLUSTER_STATUS_GET_ERROR', //proxmox: get cluster status error
        'KVM_PROXMOX_NODE_GET_ERROR', //proxmox: get node error
        'KVM_PROXMOX_NODE_STATUS_GET_ERROR', //proxmox: get node status error
        'KVM_PROXMOX_BRIDGE_GET_ERROR', //proxmox: get bridge error
        'KVM_PROXMOX_VM_GET_ERROR', //proxmox: get vm info error
        'KVM_PROXMOX_VM_CREATE_ERROR', //proxmox: create vm error
        'KVM_PROXMOX_VM_DELETE_ERROR', //proxmox: delete vm error
        'KVM_PROXMOX_VM_UPDATE_ERROR', //proxmox: update vm error
        'KVM_PROXMOX_VM_POWEROFF_ERROR', //proxmox: the vm powers off error
        'KVM_PROXMOX_VM_POWERON_ERROR', //proxmox: the vm powers on error
        'KVM_PROXMOX_VM_CUR_STATUS_GET_ERROR', //proxmox: get vm current status error
        'KVM_PROXMOX_VM_NEXT_ID_GET_ERROR', //proxmox: get next vm id error
        'KVM_PROXMOX_SNAPSHOT_CREATE_ERROR', //proxmox: create snapshot error
        'KVM_PROXMOX_SNAPSHOT_DELETE_ERROR', //proxmox: delete snapshot error
        'KVM_PROXMOX_SNAPSHOT_GET_ERROR', //proxmox: get snapshot error
        'KVM_PROXMOX_STORAGE_GET_ERROR', //proxmox: get storage error
        'KVM_PROXMOX_STORAGE_SCAN_ERROR', //proxmox: scan storage error
        'KVM_PROXMOX_STORAGE_CREATE_ERROR', //proxmox: create storage error
        'KVM_PROXMOX_STORAGE_DELETE_ERROR', //proxmox: delete storage error
        'KVM_PROXMOX_STORAGE_VOLUME_GET_ERROR', //proxmox: get storage volume error
        'KVM_PROXMOX_STORAGE_VOLUME_CREATE_ERROR', //proxmox: create storage volume error
        'KVM_PROXMOX_STORAGE_VOLUME_DELETE_ERROR', //proxmox: delete storage volume error
        'KVM_PROXMOX_STORAGE_CONTENT_GET_ERROR', //proxmox: get storage content error
        'KVM_PROXMOX_CEPH_CONFIG_NOT_EXIST_ERROR', //proxmox: the ceph config does not exist error
        'KVM_PROXMOX_VIRT_AGENT_CONNECT_ERROR', //proxmox: connect virt agent error
        'KVM_PROXMOX_TASK_GET_ERROR', //proxmox: get task info error
        'KVM_PROXMOX_LOGIN_NODE_ERROR', //proxmox: login to proxmox node error
        'HYPERV_HAS_DIFF_TIMEPOINT_EXIST_ERROR',     // hyperv: has diff timepoint exist error
        'VM_FC_KVM_STORAGE_CONFIG_PARAM_GET_ERROR',	// fusion compute kvm: get storage config param error
        'VM_FC_KVM_STORAGE_RESOURCE_CLEAN_ERROR', // fusion compute kvm: clean resource error
        'VM_FC_KVM_STORAGE_VOLUME_GET_ERROR', // fusion compute kvm: get volume info error
        'VM_FC_KVM_STORAGE_VOLUME_CREATE_ERROR', // fusion compute kvm: create volume error
        'VM_FC_KVM_STORAGE_VOLUME_DELETE_ERROR', // fusion compute kvm: delete volume error
        'VM_FC_KVM_STORAGE_VOLUME_MOUNT_ERROR', // fusion compute kvm: volume mount error
        'VM_FC_KVM_STORAGE_VOLUME_UNMOUNT_ERROR', // fusion compute kvm: volume unmount error
        'VM_FC_KVM_STORAGE_VOLUME_MOUNT_INFO_GET_ERROR', // fusion compute kvm: get volume mount info error
        'VM_FC_KVM_CONNECT_VIRT_AGENT_ERROR', // fusion compute kvm: connect virt agent error

        // ICS KVM async task error code definition
        'VM_ICS_KVM_TASK_20027_ERROR',
        'VM_ICS_KVM_TASK_102002_ERROR',
        'VM_ICS_KVM_TASK_102033_ERROR',
        'VM_ICS_KVM_TASK_201014_ERROR',
        'VM_ICS_KVM_TASK_201040_ERROR',
        'VM_ICS_KVM_TASK_201049_ERROR',
        'VM_ICS_KVM_TASK_201077_ERROR',
        'VM_ICS_KVM_TASK_201096_ERROR',
        'VM_ICS_KVM_TASK_210033_ERROR',
        'VM_ICS_KVM_TASK_210508_ERROR',
        'VM_ICS_KVM_TASK_210520_ERROR',
        'VM_ICS_KVM_TASK_210559_ERROR',
        'VM_ICS_KVM_TASK_220021_ERROR',
        'VM_ICS_KVM_TASK_220048_ERROR',
        'VM_ICS_KVM_TASK_220084_ERROR',
        'VM_ICS_KVM_TASK_220266_ERROR',
        'VM_ICS_KVM_TASK_220271_ERROR',
        'VM_ICS_KVM_TASK_300301_ERROR',
        'VM_ICS_KVM_TASK_300306_ERROR',

        //add for vmware instant recovery
        'VMWARE_LONG_TIME_RUNNING_INSTANT_WARN',					//long time runing instant vm

        // XHERE error code definition
        'XHERE_CREATE_SNAPSHOT_FAILED_ERROR',						// XHERE create snapshot failed
        'XHERE_CREATE_IMAGE_FAILED_ERROR',						// XHERE create image failed
        'XHERE_CREATE_VM_FAILED_ERROR',							// XHERE create vm failed
        'XHERE_DELETE_CDROM_FAILED_ERROR',						// XHERE delete cdrom failed
        'XHERE_CREATE_VOLUME_FAILED_ERROR',						// XHERE create volume failed
        'XHERE_ATTACH_VOLUME_FAILED_ERROR',						// XHERE attach volume failed

        // public cloud error (5131-5157)
        'PUBLIC_CLOUD_SCAN_VCENTER_ERROR',						// public cloud scan vcenter error
        'PUBLIC_CLOUD_VM_NOT_FIND_VM_ERROR',					// instance is not find
        'PUBLIC_CLOUD_BUILD_RECOVERY_VM_LIST_ERROR',			// public cloud  build recovery list error
        'PUBLIC_CLOUD_NOT_SUPPORT_BACKUP_LEVEL_ERROR',			// puclic cloud backup level error
        'PUBLIC_CLOUD_SAVE_SELF_EXPLAN_FILE_ERROR',				// public cloud save self explan file error
        'PUBLIC_CLOUD_BUILD_BACKUP_VM_LIST_ERROR',				// public cloud build backup vm list error
        'PUBLIC_CLOUD_BACKUP_VM_IS_TERMINATED',					// the instance is Terminated
        'PUBLIC_CLOUD_WRITE_BITMAP_FILE_ERROR',					// public cloud write bitmap error
        'PUBLIC_CLOUD_READ_REMOTE_DISK_ERROR',					// public cloud read remote disk error
        'PUBLIC_CLOUD_WRITE_REMOTE_DISK_ERROR',					// public cloud write remote disk error
        'PUBLIC_CLOUD_CLOSE_DISK_ERROR',						// public cloud close disk error
        'PUBLIC_CLOUD_READ_LATEST_BITMAP_FILE_ERROR',			// public cloud read latest bitmap file error
        'PUBLIC_CLOUD_RECOVERY_TIMEPOINT_NOT_EXIST_ERROR',		// public cloud recovery timepoint not exist error
        'PUBLIC_CLOUD_PARSER_VM_CONFIG_ERROR',					// public cloud parse vm config error
        'PUBLIC_CLOUD_GET_RECOVERY_VALID_DATA_SIZE_ERROR',		// public cloud get recovery valid data size error
        'PUBLIC_CLOUD_FIND_BACKUP_FILE_ID_ERROR',				// public cloud find backup file id error
        'PUBLIC_CLOUD_LATEST_TIMEPOINT_NOT_EXIST_ERROR',		// public cloud latest timepoint not exist error
        'PUBLIC_CLOUD_DISK_NUM_CHANGED_ERROR',					// public cloud ebs number changed error
        'PUBLIC_CLOUD_DISK_CHANGED_ERROR',						// public cloud disk change error
        'PUBLIC_CLOUD_DEDUPE_BLOCK_SIZE_ERROR',					// public cloud dedupe block size error
        'PUBLIC_CLOUD_DIFF_AND_INC_CAN_NOT_COEXIST_ERROR',		// public cloud diff and inc can not coexist error
        'PUBLIC_CLOUD_VM_ALREADY_POWEROFF_ERROR',				// public cloud vm already power off error
        'PUBLIC_CLOUD_VM_ALREADY_POWERON_ERROR',				// public cloud vm already power on error
        'PUBLIC_CLOUD_VM_NOT_SUSPEND_ERROR',					// public cloud vm not suspend error
        'PUBLIC_CLOUD_VM_NOT_POWERON_ERROR',					// public cloud vm not power on error
        'PUBLIC_CLOUD_HAS_DIFF_TIMEPOINT_EXIST_ERROR',			// public cloud has diff timepoint exist error
        'PUBLIC_CLOUD_CREATE_BACKUP_DIR_ERROR',					// public cloud create backup dir error

        // public cloud error (5158-5198)
        'PUBLIC_CLOUD_GET_AVALID_DATE_FILE_ERROR',				// public cloud get avalid file error
        'PUBLIC_CLOUD_WRITE_METADATA_FILE_ERROR',				// public cloud write metadata file error
        'PUBLIC_CLOUD_GET_TOTAL_BACKUP_SIZE_ERROR',				// public cloud get total backup size error
        'PUBLIC_CLOUD_API_ERROR',											// public cloud: aws api error
        'PUBLIC_CLOUD_REGION_VM_EMPTY',										// public cloud: region not have instance
        'PUBLIC_CLOUD_EBS_DISK_EMPTY',										// public cloud: the ebs is empty ,it not avlid data
        'PUBLIC_CLOUD_EBS_CHANGE_DISK_EMPTY',									// public cloud: the snapshot change date is empty
        'PUBLIC_CLOUD_INIT_API_ERROR',										// public cloud: init aws api error
        'PUBLIC_CLOUD_GET_XML_INFO_ERROR',									// public cloud: in aws get xml info error ,not find element
        'PUBLIC_CLOUD_AMI_NOT_EXIST',											// public cloud: the ami is not exist
        'PUBLIC_CLOUD_SECURITY_EXIST',										// public cloud: agent use security is exist
        'PUBLIC_CLOUD_DESCRIBE_INSTANCE_ERROR',								// public cloud: description  instances info error
        'PUBLIC_CLOUD_GET_AVALID_DATA_BY_SNAPSHOT_ERROR',						// public cloud: get avalid block by snapshot error
        'PUBLIC_CLOUD_CREATE_SNAPSHOT_ERROR',									// public cloud: api to create snapshot error
        'PUBLIC_CLOUD_DELETE_SNAPSHOT_ERROR',									// public cloud: api to delete snapshot error
        'PUBLIC_CLOUD_DESCRIBE_SNAPSHOT_INFO_ERROR',							// public cloud: get snapshot info error
        'PUBLIC_CLOUD_GET_SOURCE_STATUS_ERROR',								// public cloud: get public cloud source status error
        'PUBLIC_CLOUD_ATTACH_VOLUME_ERROR',									// public cloud: api to attach volume to vm error
        'PUBLIC_CLOUD_CREATE_VOLUME_ERROR',									// public cloud: api to create volume error
        'PUBLIC_CLOUD_DELETE_VOLUME_ERROR',									// public cloud: api to delete volume error
        'PUBLIC_CLOUD_DESCRIBE_VOLUME_INFO_ERROR',							// public cloud: get the volumes info error
        'PUBLIC_CLOUD_DEATCH_VOLUME_ERROR',									// public cloud: detach volume from vm error
        'PUBLIC_CLOUD_DESCRIBE_AMI_INFO_ERROR',								// public cloud: describe ami info error
        'PUBLIC_CLOUD_GET_AMI_ERROR',											// public cloud: get public ami error
        'PUBLIC_CLOUD_GET_AVAILABOLITY_ZONE_ERROR',							// public cloud: get availability zone error
        'PUBLIC_CLOUD_GET_NETWORK_INFO_ERROR',								// public cloud: get network info error
        'PUBLIC_CLOUD_START_INSTANCE_ERROR',									// public cloud: start/run instance error
        'PUBLIC_CLOUD_STOP_INSTANCE_ERROR',									// public cloud: stop instance error
        'PUBLIC_CLOUD_TERMINATE_INSTANCE_ERROR',								// public cloud: terminater the instance error
        'PUBLIC_CLOUD_GET_INSTANCE_TYPE_ERROR',								// public cloud: get instance type error
        'PUBLIC_CLOUD_GET_KET_PAIR_INFO_ERROR',								// public cloud: get key pair info error
        'PUBLIC_CLOUD_DESCRIBE_SECURITY_ERROR',								// public cloud: describe the security error
        'PUBLIC_CLOUD_CREATE_SECURITY_ERROR',									// public cloud: create security error
        'PUBLIC_CLOUD_DELETE_SECURITY_ERROR',									// public cloud: delete security error
        'PUBLIC_CLOUD_AUTHORIZE_SECURITY_GROUP_INGRESS_ERROR',				// public cloud: authorize security group ingress error
        'PUBLIC_CLOUD_GET_ACCOUNT_ID_ERROR',									// public cloud: get account id error
        'PUBLIC_CLOUD_GET_DISK_ENCRYPT_INFO_ERROR',							// public cloud: get disk encrypt info error
        //.public cloud task error
        'PUBLIC_CLOUD_RECOVERY_NOT_FIND_ROOT_DISK_ERROR',						// public cloud: not find the root disk in recovery task error
        'PUBLIC_CLOUD_GET_RECOVERY_DISK_SIZE_ERROR',							// public cloud: get recovery disk size error
        'PUBLIC_CLOUD_VM_STATUS_NOT_NEED_TO_BACKUP_ERROR',					// public cloud: the vm status is can't to backup error
        'PUBLIC_CLLOUD_BACKUP_VM_DISK_OUT_LIMIT_ERROR',						// public cloud: the backup vm disk number out limit error

        // add for monitor sureback instant
        'VMWARE_VIRTUAL_ENVIRMENT_OFFLINE_ERROR',				// VMWARE virtual envirment offline
        'VM_DISK_IS_CHANGED_ERROR',

        // ics api error
        'VM_ICS_KVM_SYSTEM_20055_ERROR',						// ics api error:System error, please retry later.
        'VM_ICS_KVM_NM_20102_ERROR', 							// ics api error:No consume with host agent service
        'VM_ICS_KVM_TASK_201001_ERROR', 						// ics api error:Time out to get the target datastore, or it is not mounted to any host, please check and retry.
        'VM_ICS_KVM_TASK_201093_ERROR', 						// ics api error:lun virtual volume policy and datastore policy not match
        'VM_ICS_KVM_TASK_210565_ERROR', 						// ics api error:The vm is in operation, please wait and retry
        'VM_ICS_KVM_TASK_322024_ERROR', 						// ics api error:Volume operate failed because volume and snap total num too large, so delete some volume to free some offset.

        'VMWARE_CLOSE_DISK_ERROR',
        'KVM_OPENSTACK_NOT_SUPPORT_CONSISTENCY_SNAPSHOT_ERROR', // KvmOpenStack Error: openstack not support consistency snapshot error


        'VM_HUAWEI_CBR_OP_NOT_FOUND_ERROR',
        'VM_HUAWEI_CBR_SYNC_LIST_IS_EMPTY_ERROR',
        'VM_HUAWEI_CBR_SYNC_OBJECT_NOT_EXIST_ERROR',
        'VM_HUAWEI_CBR_SYNC_GRAIN_TYPE_NOT_SUPPORT_ERROR',
        'VM_HUAWEI_CBR_SYNC_SOME_TIMEPOINT_ERROR',

        'VM_ICS_KVM_TASK_210213_ERROR', 						// ics api error : Fail to get status of vm, please try again later
        'VM_ICS_KVM_TASK_109013_ERROR', 						// ics api error:Query host task timeout or not found
        'VM_ICS_KVM_TASK_210025_ERROR', 						// ics api error:Current state of vm or template is not allowed to do this operation
        'VM_ICS_KVM_TASK_20020_ERROR', 							// ics api error : Unknown exception
        'VM_ICS_KVM_TASK_107012_ERROR', 						// ics api error : This operation failed, please check if you have the authorization for this operation

        'KVM_SANGFOR_GET_API_VERSION_ERROR',					// sangfor kvm: get api version error
        'KVM_SANGFOR_GET_TASK_ERROR',								// sangfor kvm: get task error
        'KVM_SANGFOR_GET_RESOURE_POOL_ERROR',						// sangfor kvm: get resource pool error
        'KVM_SANGFOR_GET_HOST_ERROR',								// sangfor kvm: get host error
        'KVM_SANGFOR_GET_VM_ERROR',								// sangfor kvm: get vm error
        'KVM_SANGFOR_GET_SNAPSHOT_ERROR',							// sangfor kvm: get snapshot error
        'KVM_SANGFOR_GET_VM_GROUP_ERROR',							// sangfor kvm: get vm group error
        'KVM_SANGFOR_GET_STORAGE_ERROR',							// sangfor kvm: get storage error
        'KVM_SANGFOR_DELETE_STORAGE_ERROR',						// sangfor kvm: delete storage error
        'KVM_SANGFOR_GET_NETWORK_ERROR',							// sangfor kvm: get network error
        'KVM_SANGFOR_NEED_CLEAN_ALL_SNAPSHOT_ERROR',				// sangfor kvm: need to clean all snapshots
        'KVM_SANGFOR_CREATE_SNAPSHOT_ERROR',						// sangfor kvm: create snapshot error
        'KVM_SANGFOR_DELETE_SNAPSHOT_ERROR',						// sangfor kvm: delete snapshot error
        'KVM_SANGFOR_DELETE_VM_ERROR',							// sangfor kvm: delete vm error
        'KVM_SANGFOR_POWEROFF_VM_ERROR',							// sangfor kvm: poweroff vm error
        'KVM_SANGFOR_POWERON_VM_ERROR',							// sangfor kvm: poweron vm error
        'KVM_SANGFOR_CREATE_STORAGE_ERROR',						// sangfor kvm: create storage error
        'KVM_SANGFOR_CREATE_VM_ERROR',							// sangfor kvm: create vm error
        'KVM_SANGFOR_VM_IN_DISASTER_RECOVERY_PLAN_ERROR',			// sangfor kvm: the vm is in the disaster recovery plan
        'KVM_SANGFOR_CHECK_IS_SUPPORT_CBT_ERROR',					// sangfor kvm: check is support cbt error
        'KVM_SANGFOR_NOT_SUPPORT_CBT_ERROR',						// sangfor kvm: not support cbt error
        'KVM_SANGFOR_GET_CBT_STATUS_ERROR',						// sangfor kvm: get cbt status error
        'KVM_SANGFOR_UPDATE_CBT_STATUS_ERROR',					// sangfor kvm: update cbt status error
        'KVM_SANGFOR_CREATE_CBT_RESOURCE_ERROR',					// sangfor kvm: create cbt resource error
        'KVM_SANGFOR_GET_CBT_RESOURCE_ERROR',						// sangfor kvm: get cbt resource error
        'KVM_SANGFOR_DELETE_CBT_RESOURCE_ERROR',					// sangfor kvm: delete cbt resource error
        'KVM_SANGFOR_UPDATE_CBT_RESOURCE_ERROR',					// sangfor kvm: update cbt resource error
        'KVM_SANGFOR_INIT_SDK_ERROR',								// sangfor kvm: init sdk error
        'KVM_SANGFOR_CONNECT_PLATFORM_ERROR',						// sangfor kvm: connect platform error
        'KVM_SANGFOR_DISCONNECT_PLATFORM_ERROR',					// sangfor kvm: disconnect platform error
        'KVM_SANGFOR_OPEN_DISK_ERROR',							// sangfor kvm: open disk error
        'KVM_SANGFOR_GET_CBT_DIFF_BITMAP_ERROR',					// sangfor kvm: get cbt diff bitmap error
        'KVM_SANGFOR_CBT_INVALID_ERROR',							// sangfor kvm: cbt invalid error
        'KVM_SANGFOR_NOT_SUITABLE_STORAGE_ERROR',					// sangfor kvm: there is no suitable storage error
        'KVM_SANGFOR_GET_CLUSTER_ERROR',							// sangfor kvm: get cluster error
        'KVM_SANGFOR_REBASE_BACKING_FILE_ERROR',					// sangfor kvm: rebase backing file error
        'KVM_SANGFOR_MERGE_BACKUP_DATA_ERROR',					// sangfor kvm: merge backup data error
        'KVM_SANGFOR_UPDATE_VM_CONFIG_ERROR',						// sangfor kvm: update vm config error

        // vm common
        'VM_NOT_SUPPORT_AUTH_TYPE_ERROR',							// vm: not support the auth type error
        'VM_NOT_SUPPORT_PLATFORM_TYPE_ERROR',						// vm: not support the platform type error
        'VM_CLOSE_DISK_ERROR',									// vm: close disk error

        // sangfor error code
        'KVM_SANGFOR_CALCULATE_HMAC_ERROR',								// sangfor kvm: calculating hmac failed
        'KVM_SANGFOR_DISK_NUM_REACHED_MAX_ERROR',						// sangfor kvm: the number of disks has reached the maximum
        'KVM_SANGFOR_CBT_CHANGE_RANGE_EXCEED_BOUND_ERROR',				// sangfor kvm: cbt change range out of bounds error
        'KVM_SANGFOR_CBT_INVALID_HANDLE_COUNT_REACHED_MAX_ERROR',		// sangfor kvm: the number of times cbt failure has been processed has reached the maximum
        'KVM_SANGFOR_VM_TYPE_NOT_SUPPORT_FOR_BACKUP_ERROR',				// sangfor kvm: the type of virtual machine is not supported for backup

        // ics vvdk error code v1.2
        'VM_ICS_KVM_TASK_210126_ERROR',				// VM Tools is not running or installed inside the virtual machine

        // add for v2v driver option
        'VMWARE_SET_VM_BOOT_OPTION_ERROR',				// vmware set boot option for vm error

        // 5267
        'PUBLIC_CLOUD_DEPENDENT_SNAPSHOT_DOES_NOT_EXIST',						// public cloud: The dependent snapshot does not exist

        //vm_server_error
        'PUBLIC_CLOUD_LOCAL_TIME_ERROR',						// public cloud local time and ntp server time difference  more than 15 min //5268
        'PUBLIC_CLOUD_TRY_TO_GET_NTPTIME_ERROR',						// public cloud try to get ntpdate error  //5269
        'PUBLIC_CLOUD_RECOVERY_NOT_ROOT_DISK_ERROR',				// public cloud recovery not root disk error //5270
        'PUBLIC_CLOUD_BACKUP_VM_NOT_CHOSE_DISKS',					// public cloud backup vm is not have disk or not chose disk error //5271
        'PUBLIC_CLOUD_RECOVERY_INSTACE_TYPE_NOT_SUPPORT_THE_CONFIG',	// public cloud  recovery instance is not support the AMI or Availability Zone //5272
        'VM_VIRTUAL_LAB_PARAM_NOT_CHANGE_WARN', //add for modify virtual lab param not change
        'KVM_SANGFOR_SCP_IMAGE_ID_EMPTY_ERROR', 					// the platform image file is empty, please upload the image file first 5274
        5275 => 'PUBLIC_CLOUD_BACKUP_LEVEL_CHANGE',							// backup level change // 5275
        5276 => 'KVM_H3C_CAS_CVD_ENABLE_VM_OPERATION_ERROR',
        5277 => 'KVM_H3C_CAS_CVD_FORBID_VM_OPERATION_ERROR',
        //        'KVM_SANGFOR_SCP_VM_IS_POWER_ON_ERROR',				//vm is powered on //5281

        'VM_DEPLOY_SURE_BACKUP_NETWORK_ERROR',
        'VM_ADD_IP_ROUTE_ERROR',
        'VM_DEL_IP_ROUTE_ERROR',
        'VM_GET_VIRTUAL_LAB_INFO_ERROR',
        'PUBLIC_CLOUD_REGION_FORBIDDENT',
        'PUBLIC_CLOUD_INSTANCETYPE_NOT_AVALIABLE',
        'KVM_H3C_CAS_CVD_CREATE_CONNECTION_ERROR',
        'PUBLIC_CLOUD_DB_VCENTER_ALREADY_EXIST_ERROR',				//public cloud platfrom exits //5285
        'PUBLIC_CLOUD_LOGIN_AK_SK_ERROR',							//public cloud add vcenter ak sk error //5286
        5287 => 'PUBLIC_CLOUD_SHARING_IMAGE_QUOTA_FULL_ERROR',
        'KVM_LENOVO_AIO_GET_VERSION_ERROR',
        'KVM_LENOVO_AIO_GET_VM_CONFIG_ERROR',
        'KVM_LENOVO_AIO_GET_VM_STATUS_ERROR',
        'KVM_LENOVO_AIO_CREATE_SNAPSHOT_ERROR',
        'KVM_LENOVO_AIO_UPDATE_SNAPSHOT_PATH_ERROR',
        'KVM_LENOVO_AIO_DELETE_SNAPSHOT_ERROR',
        'KVM_LENOVO_AIO_POWEROFF_VM_ERROR',
        'KVM_LENOVO_AIO_POWERON_VM_ERROR',
        'KVM_LENOVO_AIO_CREATE_VM_ERROR',
        'KVM_LENOVO_AIO_DELETE_VM_ERROR',
        'KVM_LENOVO_AIO_GET_VM_SNAPSHOT_LIST_ERROR',
        'KVM_LENOVO_AIO_CBT_CHANGE_RANGE_EXCEED_BOUND_ERROR',
        'KVM_LENOVO_AIO_CTREATE_NFS_STORAGE_ERROR',
        'KVM_LENOVO_AIO_GET_HOST_LIST_ERROR',
        'KVM_LENOVO_GET_CLUSTER_ERROR',
        'KVM_LENOVO_GET_VM_HARDWARE_INFO_ERROR',
        'KVM_LENOVO_GET_VM_DISKS_ERROR',
        'KVM_LENOVO_GET_VM_LIST_ERROR',
        'KVM_LENOVO_CREATE_VM_SNAPSHOT_BITMAP_ERROR',
        'KVM_LENOVO_DELETE_VM_SNAPSHOT_BITMAP_ERROR',
        'KVM_LENOVO_GET_CBT_DIFF_BITMAP_ERROR',
        'KVM_LENOVO_UMOUNT_STORAGE_IN_HOST_ERROR',
        'KVM_LENOVO_MOUNT_STORAGE_IN_HOST_ERROR',
        'KVM_LENOVO_GET_TASK_ERROR',
        'KVM_LENOVO_GET_CLUSTER_PORT_GROUP_ERROR',
        'KVM_LENOVO_GET_STORAGE_ERROR',
        'KVM_LENOVO_DELETE_STORAGE_ERROR',
        'KVM_LENOVO_CREATE_NBD_DEVICE_ERROR',
        'KVM_LENOVO_DELETE_NBD_DEVICE_ERROR',
        'KVM_LENOVO_CREATE_NBD_KIT_ERROR',
        5318 => 'KVM_LENOVO_DELETE_NBD_KIT_ERROR',
        'KVM_ICS_VVDK_GET_LUKS_DISK_HEADER_ERROR',
        'KVM_ICS_VVDK_SET_LUKS_DISK_HEADER_ERROR',
        'KVM_H3C_CAS_CVD_CBT_CHANGE_RANGE_EXCEED_BOUND_ERROR',
        'KVM_H3C_CAS_CVD_DELETE_CBT_RESOURCE_ERROR',
        'KVM_H3C_CAS_CVD_GET_CBT_STATUS_ERROR',
        'KVM_H3C_CAS_CVD_CHECK_CBT_INVALID_ERROR',
        'KVM_H3C_CAS_CVD_CHECK_VM_DISK_BASE_IMAGE_ERROR',
        'KVM_H3C_CAS_CVD_GET_CBT_CHANEGE_BLOCK_ERROR',
        5327 => 'KVM_H3C_CAS_CVD_GET_CBT_VM_BITMAP_ERROR',
        'VMWARE_ASYNC_READ_REMOTE_DISK_ERROR',
        'VMWARE_ASYNC_WRITE_REMOTE_DISK_ERROR',
        'VMWARE_SCAN_VCENTER_SKIP_WARN',
        'VM_CONTAIN_INSTANT_RECOVERY_AND_OTHER_DISKS',
        'VM_ICS_KVM_TASK_210526_ERROR',
        'VM_ICS_KVM_TASK_210765_ERROR',
        'VM_FC_KVM_PUB_10100110_ERROR',
        'KVM_OPENSTACK_GET_PRODUCT_ID_ERROR',
        'KVM_OPENSTACK_GET_ORDER_RESOURCE_ERROR',
        'KVM_OPENSTACK_SUBSCRIPTION_TIMEOUT_ERROR',
        'KVM_OPENSTACK_SUBSCRIPTION_FAILED_ERROR',
        'VM_THREAD_NUM_INFO_INVALID_ERROR',
        'KVM_VOLCANO_VESTACK_GET_CLUSTER_ERROR',
        'KVM_VOLCANO_VESTACK_GET_HOST_ERROR',
        'KVM_VOLCANO_VESTACK_GET_INSTANCE_ERROR',
        'KVM_VOLCANO_VESTACK_DELETE_INSTANCE_ERROR',
        'KVM_VOLCANO_VESTACK_STOP_INSTANCE_ERROR',
        'KVM_VOLCANO_VESTACK_START_INSTANCE_ERROR',
        'KVM_VOLCANO_VESTACK_GET_SNAPSHOT_ERROR',
        'KVM_VOLCANO_VESTACK_CREATE_SNAPSHOT_GROUP_ERROR',
        'KVM_VOLCANO_VESTACK_DELETE_SNAPSHOT_GROUP_ERROR',
        'KVM_VOLCANO_VESTACK_GET_SNAPSHOT_GROUP_ERROR',
        'KVM_VOLCANO_VESTACK_DELETE_SNAPSHOT_ERROR',
        'KVM_VOLCANO_VESTACK_CREATE_SNAPSHOT_ERROR',
        'KVM_VOLCANO_VESTACK_CREATE_VOLUME_ERROR',
        'KVM_VOLCANO_VESTACK_MODIFY_VOLUME_ERROR',
        'KVM_VOLCANO_VESTACK_DELETE_VOLUME_ERROR',
        'KVM_VOLCANO_VESTACK_ATTACH_VOLUME_ERROR',
        'KVM_VOLCANO_VESTACK_DETACH_VOLUME_ERROR',
        'KVM_VOLCANO_VESTACK_CREATE_VOLUME_FROM_SNAPSHOT_ERROR',
        'KVM_VOLCANO_VESTACK_GET_IMAGE_ERROR',
        'KVM_VOLCANO_VESTACK_CREATE_INSTANCE_ERROR',
        'KVM_VOLCANO_VESTACK_GET_VOLUME_ERROR',
        'KVM_VOLCANO_VESTACK_NOT_VALID_VOLUME_ERROR',
        'KVM_VOLCANO_VESTACK_GET_VPC_ERROR',
        'KVM_VOLCANO_VESTACK_GET_SUBNET_ERROR',
        'KVM_VOLCANO_VESTACK_GET_INSTANCE_TYPE_ERROR',
        'KVM_VOLCANO_VESTACK_GET_PROJECT_ERROR',
        'KVM_VOLCANO_VESTACK_GET_REGION_ERROR',
        'KVM_VOLCANO_VESTACK_GET_ZONE_ERROR',
        'KVM_VOLCANO_VESTACK_NOT_FIND_AGENT_VM_ERROR',
        'KVM_VOLCANO_VESTACK_GET_VOLUME_ATTACH_PATH_ERROR',
        'KVM_VOLCANO_VESTACK_GET_ACCOUNT_ERROR',
        'KVM_VOLCANO_VESTACK_RESOURCE_NOT_EXIST_ERROR',
        'KVM_VOLCANO_VESTACK_CALCULATE_HMAC_ERROR',
        'KVM_VOLCANO_VESTACK_IMPORT_IAMGE_ERROR',
        'KVM_VOLCANO_VESTACK_CREATE_MULTIPART_UPLOAD_ERROR',
        'KVM_VOLCANO_VESTACK_DELETE_MULTIPART_UPLOAD_ERROR',
        'KVM_VOLCANO_VESTACK_UPLOAD_SHARD_DATA_ERROR',
        'KVM_VOLCANO_VESTACK_MERGE_SHARD_DATA_ERROR',
        'KVM_VOLCANO_VESTACK_IMPORT_IMAGE_ERROR',
        'KVM_VOLCANO_VESTACK_DELETE_IMAGE_ERROR',
        'KVM_VOLCANO_VESTACK_DELETE_OBJECT_ERROR',
        'VM_INCREMENTAL_MODE_IS_CLOSED_ERROR',
        'VM_GET_CPU_ARCH_ERROR',
        'VM_GET_NETWORK_ADAPTER_ERROR',
        'VM_DISABLE_NETWORK_ADAPTER_ERROR',
        'VM_GET_OS_INFO_LIST_ERROR',
        'VM_SYSTEM_REBOOT_ABNORMAL_OR_NODE_TRANSFER',
        'VM_BACKUP_INFO_NOT_EXIST_ERROR',                       // backup info does not exist error，虚拟机信息不存在
        'VM_DISK_INFO_NOT_EXIST_ERROR',                         // disk info does not exist error，虚拟机磁盘信息不存在
        'VM_BACKUP_INFO_HANDLE_FINISH',                         // backup info handle finish，虚拟机备份已完成
        'VM_BACKUP_INFO_HANDLE_NO_FINISH',                      // backup info handle no finish，虚拟机正在进行备份
        'VM_WEIGHT_LIST_IS_EMPTY',                              // weight list is empty，权重列表为空
        'VM_WEIGHT_IS_INVALID',                                 // weight is invalid，权重信息不存在
        5393 => 'VM_IS_REMOVED_FROM_TASK',                      // vm is removed from task，虚拟机已从任务中移除
        'VM_SNAPSHOT_IS_OCCUPIED',
        'VM_FC_KVM_VM_10430041_ERROR',
        5404 => 'KVM_SANGFOR_SYSTEM_BUSY_TRY_AGAIN_ERROR',

        //********节点错误定义*********//
        10000 => 'NODE_NOT_SUPPORT_STORAGE_ERROR',
        'NODE_SERVER_TIME_CONVERT_ERROR',						// convert time string error
        'NODE_SERVER_QUERY_ALL_STRATEGY_ERROR',		    		// query all time strategy error
        'NODE_SERVER_STRATEGY_NULL_ERROR',			    		// there is no strategy in database
        'NODE_SERVER_STRATEGY_ALREADY_START_ERROR',	   		 	// strategy already started in time window
        'NODE_SERVER_STRATEGY_TIME_CONFIG_ERROR',				// time strategy configuration error
        'NODE_SERVER_NAS_EXIST_ERROR',    // nas already insert into sql
        'NODE_SERVER_NAS_PARAM_ERROR',    // user input error
        'NODE_SERVER_SPACE_NOT_ENOUGH_TO_UPDATE_ERROR', 	//space not enough to do update

        10030 => 'NODE_SERVER_REMOTE_NODE_NOT_MASTER_NODE',
        10100 => 'NODE_SERVER_TAPE_CARRIAGE_ALREADY_IN_GROUP',
        10101 => 'NODE_SERVER_TAPE_CARRIAGE_CANNOT_DO_ANY_OPERATION',

        10110 => 'NODE_SERVER_USER_RESOURCE_TRANSFERRING_ERROR', // user resource are transferring
        10111 => 'NODE_SERVER_REMOTE_NODE_CONNECT_ERROR',
        10112 => 'NODE_SERVER_ALARM_PUSH_STRATEGY_NOT_FOUND_OR_DISABLED',
        10113 => 'NODE_SERVER_NODE_NETWORK_ACCESS_ERROR',

        11000 => 'NODE_MANAGER_CANNOT_OPERATE_MASTER_NODE_ERROR',  // 不能操作主节点
        11001 => 'NODE_MANAGER_TARGET_NODE_EXIST_ERROR',  // 节点已经在备份系统中了
        11002 => 'NODE_MANAGER_TARGET_NODE_ALREADY_USED_BY_OTHER_SYSTEM_ERROR',  // 节点已经被其他节点使用了
        11003 => 'NODE_MANAGER_CANNOT_ADD_MASTER_NODE_ERROR',
        11004 => 'NODE_MANAGER_BACKUP_NODE_MAXIMIZING',
        11008 => 'NODE_SERVER_STORAGE_IS_ALREADY_SYNCING_ERROR',
        11009 => 'NODE_NOT_SUPPORT_WORM_AS_VDP_NOT_LOADED',
        14001 => 'DATA_STORAGE_INVALID_DEVICE_TYPE',
        'DATA_STORAGE_UNSUPPORTED_NETWORK_TYPE',
        'DATA_STORAGE_UNSUPPORTED_DEVICE_TYPE',
        'DATA_STORAGE_DEVICE_TYPE_NOT_MATCH',
        'DATA_STORAGE_FILE_ALREADY_OPEN',
        'DATA_STORAGE_INITIALIZER_SESSION_FAILED',
        'DATA_STORAGE_NOT_SUPPORT_OPERATION',
        'DATA_STORAGE_CONTEXT_UNINITIALIZED',
        'DATA_STORAGE_NOT_FOUND_WORK_PROCESS',
        'DATA_STORAGE_CREATE_WORK_PROCESS_FAILED',
        14011 => 'DATA_STORAGE_INCOMPATIBLE_NETWORK_AND_DEVICE_TYPE',
        14015 => 'DATA_STORAGE_TAPE_NOT_FOUND_AVAILABLE_CARRIAGE',
        'DATA_STORAGE_TAPE_NOT_FOUND_IDLE_DRIVER',
        'DATA_STORAGE_TAPE_OPEN_CARRIAGE_FAILED',
        'DATA_STORAGE_TAPE_WRITE_STORAGE_HEAD_FAILED',
        'DATA_STORAGE_TAPE_WRITE_FILE_HEAD_FAILED',
        'DATA_STORAGE_TAPE_WRITE_DATA_FAILED',
        'DATA_STORAGE_TAPE_READ_STORAGE_HEAD_FAILED',
        'DATA_STORAGE_TAPE_READ_FILE_HEAD_FAILED',
        'DATA_STORAGE_TAPE_READ_FILE_TAIL_FAILED',
        'DATA_STORAGE_TAPE_READ_DATA_FAILED',
        'DATA_STORAGE_TAPE_CARRIAGE_ALREADY_USING',
        'DATA_STORAGE_TAPE_NOT_FOUND_AVAILABLE_CARRIAGE_TO_WRITE',
        'DATA_STORAGE_TAPE_SPACE_NOT_ENOUGH',
        'DATA_STORAGE_TAPE_CANNOT_REWRITE_AND_NEED_ADD_IDLE',
        14100 => 'DATA_STORAGE_OPERATION_ABORTED',

        15001 => 'BD_TAPE_NOT_FOUND_LIB',
        'BD_TAPE_NOT_FOUND_DRIVER',
        'BD_TAPE_NOT_FOUND_IDLE_DRIVER',
        'BD_TAPE_NOT_FOUND_CARRIAGE',
        'BD_TAPE_NOT_FOUND_AVAILABLE_CARRIAGE',
        'BD_TAPE_LOAD_TAPE_CARRIAGE_FAILED',
        'BD_TAPE_UNLOAD_TAPE_CARRIAGE_FAILED',
        'BD_TAPE_OPEN_DRIVE_DEVICE_FAILED',
        'BD_TAPE_DRIVER_BUSY',
        'BD_TAPE_NOT_AVAILABLE',
        'BD_TAPE_IO_ERROR',
        'BD_TAPE_CAPACITY_NOT_ENOUGH',
        'BD_TAPE_DRIVER_FAILURE',
        'BD_TAPE_MEDIA_MISSING',
        'BD_TAPE_MEDIA_DAMAGED',
        'BD_TAPE_TRANSFER_ERROR',
        'BD_TAPE_CARRIAGE_ALREADY_OFFLINE',
        'BD_TAPE_NOT_FOUND_IDLE_IE_SLOT',
        'BD_TAPE_NOT_FOUND_IDLE_SLOT',
        'BD_TAPE_MT_REWIND_FAILED',
        'BD_TAPE_MT_WRITE_EOF_FAILED',
        'BD_TAPE_MT_FORWARD_FSF_FAILED',
        'BD_TAPE_MT_BACKWARD_BSF_FAILED',
        'BD_TAPE_MT_BACKWARD_BSFM_FAILED',
        'BD_TAPE_WRITE_TAPE_HEAD_ERROR',
        'BD_TAPE_READ_TAPE_HEAD_ERROR',
        'BD_TAPE_WRITE_FILE_HEAD_ERROR',
        'BD_TAPE_READ_FILE_HEAD_ERROR',
        'BD_TAPE_WRITE_STORAGE_TAIL_FAILED',
        'BD_TAPE_READ_STORAGE_TAIL_FAILED',
        'BD_TAPE_WRITE_FILE_TAIL_FAILED',
        'BD_TAPE_READ_FILE_TAIL_FAILED',
        'BD_TAPE_RECORD_DB_INFO_OFFSET_FAILED',
        'BD_TAPE_READ_DB_INFO_OFFSET_FAILED',
        'BD_TAPE_INIT_TAPE_TAIL_CONTEXT_FAILED',
        'BD_TAPE_WRITE_DATA_FAILED',
        'BD_TAPE_READ_DATA_FAILED',
        'BD_TAPE_MT_FORWARD_FSR_FAILED',
        'BD_TAPE_MT_FORWARD_BSR_FAILED',


        16000 => 'COPY_SERVER_GET_COPY_INFO_ERROR',
        'COPY_SERVER_SOURCE_TIMEPOINT_CAN_NOT_COPIED',
        'COPY_SERVER_COPY_ITEM_NOT_FOUND_TIMEPOINT',
        'COPY_SERVER_CHAIN_LENGTH_LIMIT',
        'COPY_SERVER_SRC_TIMEPOINT_NOT_FOUND',
        'COPY_SERVER_DEPEND_TIMEPOINT_NOT_FOUND',
        'COPY_SERVER_PASSWORD_CHANGED',
        'COPY_SERVER_COMPRESSED_INFO_CHANGED',
        'COPY_SERVER_CONTAINER_FULL',
        'COPY_SERVER_WRITE_BITMAP_FILE_ERROR',
        'COPY_SERVER_RPC_INIT_ERROR',
        'COPY_SERVER_DEVICE_INFO_CHANGED',
        'COPY_SERVER_TIMEPOINT_READER_NOT_INIT',
        'COPY_SERVER_TIMEPOINT_READER_INIT_ERROR',
        'COPY_SERVER_MERGING_ERROR',
        'COPY_SERVER_FILE_REOPEN_ERROR',
        'COPY_SERVER_BITMAP_INFO_ERROR',
        'COPY_SERVER_HANDLE_TRANSPORT_DATA_ERROR',
        'COPY_SERVER_HASH_INC_NOT_VALID_ERROR',
        'COPY_SERVER_WORM_FLAG_CHANGED',

        20001 => 'EMD_VM_ISOLATE_NETWORK_CONFIG_OVER_RANGE',
        'EMD_VM_SERVER_RESOURCE_INIT_ERROR',
        'EMD_VM_SERVER_CANT_CREATE_BRIDGE_BY_NIC_WITHOUT_IP',
        'EMD_VM_ISOLATE_NET_RELOCATE_CONFLICT',
        'EMD_VM_VIRTUAL_NETWORK_EXISTS_ON_NODE',


        21000 => 'STRATEGY_SERVER_GET_STRATEGY_ERROR',
        'STRATEGY_SERVER_GET_TASK_PEIOEITY_ERROR',
        21100 => 'STRATEGY_SERVER_TIME_CONVERT_ERROR',
        'STRATEGY_SERVER_QUERY_ALL_STRATEGY_ERROR',
        'STRATEGY_SERVER_STRATEGY_NULL_ERROR',
        'STRATEGY_SERVER_STRATEGY_ALREADY_START_ERROR',
        'STRATEGY_SERVER_TIME_STRATEGY_CONFIG_ERROR',
        21200 => 'STRATEGY_SERVER_SECTION_NOT_FOUND',
        'STRATEGY_SERVER_TASK_GROUP_NOT_FOUND',
        'STRATEGY_SERVER_PLAN_STOP',
        'STRATEGY_SERVER_NOT_INITIAL_SECTION',
        'STRATEGY_SERVER_INCONSECUTIVE_SECTION',
        'STRATEGY_SERVER_DRIVE_TASK_ERROR',
        'STRATEGY_SERVER_SECTION_TRIGGER_CONDITION_NOT_MET',

        22000 => 'BD_CLUSTER_GENERIC_ERROR',
        'BD_CLUSTER_MULTIPLE_MASTER_NODE_ALIVE',
        'BD_CLUSTER_NODE_IS_NOT_MASTER',
        'BD_CLUSTER_QUERY_NODE_CONFIG_FAILED',
        'BD_CLUSTER_NODE_VERSION_INCONSISTENCY',
        'BD_CLUSTER_NGINX_NO_FOUND_SIGN',
        'BD_CLUSTER_CLUSTER_IS_NOT_RUNNING',
        'BD_CLUSTER_CLUSTER_IS_NOT_STOPPED',
        'BD_CLUSTER_NODE_ROLE_ERROR',
        'BD_CLUSTER_CLUSTER_PRIORITY_ERROR',
        'BD_CLUSTER_VIP_DRIFT_ERROR',

        22500 => 'BD_CLUSTER_TASK_CONFIG_SINGLE_NODE',
        'BD_CLUSTER_TASK_CONFIG_SINGLE_STORATE',
        'BD_CLUSTER_TASK_CONFIG_SINGLE_APPLIANCE',

        23000 => 'BD_CLUSTER_LOAD_BALANCE_TIME_ERROR',
        'BD_CLUSTER_LOAD_BALANCE_NODE_POOL_NO_ONLINE_NODE_ERROR',
        'BD_CLUSTER_LOAD_BALANCE_STORAGE_POOL_NO_ONLINE_STORAGE_ERROR',
        'BD_CLUSTER_LOAD_BALANCE_APPLIANCE_POOL_NO_ONLINE_APPLIANCE_ERROR' .

        23500 => 'BD_CLUSTER_NODE_IS_RESOURCE_LIMITED_STATUS',
        'BD_CLUSTER_ALL_NODE_IS_RESOURCE_LIMITED_STATUS',
        'BD_CLUSTER_TASK_NODE_TRANSFER_SUCCESS',
        'BD_CLUSTER_NODE_NOT_CONFIG_RESOURCE_LIMITED',

        24000 => 'BD_CLUSTER_MASTER_NODE_ABNORMAL',

        25000 => 'TOOL_SERVER_DRIVER_DIR_EMPTY_ERROR',
        'TOOL_SERVER_MULTIPLE_DRIVERS_ERROR',
        'TOOL_SERVER_LACK_DRIVER_FILE_ERROR',
        'TOOL_SERVER_PARSE_DRIVER_FILE_ERROR',
        'TOOL_SERVER_TARGET_MACHINE_HARDWARE_INFO_EMPTY_ERROR',
        'TOOL_SERVER_OS_VERSION_INVALID_ERROR',
        'TOOL_SERVER_CPU_ARCH_INVALID_ERROR',
        'TOOL_SERVER_DISK_BUS_EMPTY_ERROR',
        'TOOL_SERVER_NET_BUS_EMPTY_ERROR',
        'TOOL_SERVER_UNSUPPORT_CONTROLLER_TYPE_ERROR',
        25010 => 'TOOL_SERVER_UNKNOWN_CONTROLLER_TYPE_ERROR',
        'TOOL_SERVER_FILE_FORMAT_ERROR',
        'TOOL_SERVER_FILE_CONTENT_ERROR',
        'TOOL_SERVER_FILE_VERSION_ERROR',



        26000 => 'LIBVIRT_LOGIN_ERROR',
        'LIBVIRT_CREATE_VM_ERROR',
        'LIBVIRT_DEFINE_VM_CONFIG_XML_ERROR',
        'LIBVIRT_SET_VM_VNC_PORT_ERROR',
        'LIBVIRT_START_VM_ERROR',
        'LIBVIRT_STOP_VM_ERROR',
        'LIBVIRT_DELETE_VM_ERROR',
        'LIBVIRT_GET_VM_STATUS_ERROR',
        'LIBVIRT_DEFINE_SWITCH_CONFIG_XML_ERROR',
        'LIBVIRT_CREATE_SWITCH_ERROR',
        'LIBVIRT_ACTIVE_SWITCH_ERROR',
        'LIBVIRT_SHUTDOWN_SWITCH_ERROR',
        'LIBVIRT_DELETE_SWITCH_ERROR',
        'LIBVIRT_QUERY_SWITCH_ERROR',
        'LIBVIRT_DEFINE_PORT_GROUP_XML_ERROR',
        'LIBVIRT_ADD_PORT_GROUP_ERROR',
        'LIBVIRT_GENERIC_XML_ERROR',
        'LIBVIRT_ADD_DEVICE_ERROR',
        'LIBVIRT_DEL_DEVICE_ERROR',
        'LIBVIRT_PARSE_XML_ERROR',
        'LIBVIRT_QUERY_VM_CONFIG_ERROR',
        'LIBVIRT_RECONFIG_VM_CONFIG_ERROR',
        'LIBVIRT_ALLOCATE_DEVICE_TARGET_ERROR',
        'LIBVIRT_RECONFIG_VM_OS_ERROR',
        'LIBVIRT_SCREEN_SHOT_ERROR',

        26450 => 'EMD_PREFIX_SUCCESS',
        'EMD_PREFIX_TIMEOUT',
        'EMD_PREFIX_CANT_FIND_PARA_DISK',
        'EMD_PREFIX_CANT_PARSE_FIX_MSG_INFO',
        'EMD_PREFIX_CANT_DO_BOOT_FIX_FAILD',
        'EMD_PREFIX_CANT_DO_AGENT_FIX_FAILD',
        'EMD_PREFIX_UNKNOWN_OS_TYPE',
        'EMD_PREFIX_ADD_PARA_DISK_FAILED',
        'EMD_PREFIX_ADD_FIX_IMG_FAILED',
        'EMD_PREFIX_RECONFIG_VM_BOOT_BY_CDROM_FAILED',
        'EMD_PREFIX_START_VM_BOOT_BY_CDROM_FAILED',
        'EMD_PREFIX_STOP_VM_BOOT_BY_CDROM_FAILED',
        'EMD_PREFIX_RECONFIG_VM_BOOT_BY_DISK_FAILED',
        26463 => 'EMD_PREFIX_START_VM_BOOT_BY_DISK_FAILED',

        26500 => 'DB_CDP_GENERIC_SUCCESS',
        'DB_CDP_GENERIC_ERROR',
        'DB_CDP_UNKNOWN_ERROR',
        'DB_CDP_ERROR_PACKET_ORDER',
        'DB_CDP_ERROR_PACKET_ABNORMAL',
        'DB_CDP_ERROR_UNKNOWN_OP_CODE',
        'DB_CDP_ERROR_TASK_NOT_EXIST',
        'DB_CDP_ERROR_LOAD_TASK_INFO_FAILED',
        'DB_CDP_ERROR_TASK_NOT_IN_TAKEOVER_STAGE',
        'DB_CDP_ERROR_TASK_NOT_IN_RUNNING_STATUS',
        'DB_CDP_ERROR_TASK_NOT_IN_FAULT_RESUME_STAGE',
        'DB_CDP_ERROR_NOT_FIND_RUNNING_TASK',
        'DB_CDP_ERROR_AGENT_HANDSHAKE_FAILED',
        'DB_CDP_ERROR_NOTIFY_AGENT_PERFORM_ACTION',
        'DB_CDP_ERROR_WAIT_AGENT_PERFORM_ACTION',
        'DB_CDP_ERROR_WAIT_ACK_TIMEOUT',
        'DB_CDP_ERROR_CONTROL_SENDER_NOT_INIT',
        'DB_CDP_ERROR_AGENT_LINK_LOST',
        'DB_CDP_ERROR_FIND_TASK_CHILD_PROCESS',
        'DB_CDP_ERROR_AGENT_NET_FAULT',
        'DB_CDP_ERROR_REALTIME_BACKUP_LICENSE_EXHAUST',
        'DB_CDP_ERROR_AUTO_TAKEOVER_LICENSE_EXHAUST',
        'DB_CDP_ERROR_ASSOCIATED_TASK_EXIST_ON_THE_HOST',
        'DB_CDP_ERROR_AUTHORIZED_CAPACITY_INSUFFICIENT',
        'DB_CDP_ERROR_AUTHORIZED_CAPACITY_INSUFFICIENT_TO_BACKUP',
        'DB_CDP_ERROR_BACKUP_STORAGE_ROOT_PATH_NOT_EXIST',
        'DB_CDP_ERROR_UNSUPPORTED_DATABASE_TYPE',
        'DB_CDP_ERROR_TASK_ALREADY_RUNNING',
        'DB_CDP_ERROR_AGENT_CLIENT_INTERNAL',
        'DB_CDP_ERROR_APP_SERVICE_NOT_RUNNING',
        'DB_CDP_ERROR_TASK_NOT_IN_LOG_SYNC',
        'DB_CDP_ERROR_TASK_NOT_IN_LOG_SYNC_OR_TAKEOVER',
        'DB_CDP_ERROR_TASK_NOT_IN_FAILBACK_LOG_SYNC',
        'DB_CDP_ERROR_AGENT_APP_STATUS_ABNORMAL',
        'DB_CDP_ERROR_APP_LOG_REPLAY_COMPLEX_NEST_TYPE_NOT_SUPPORTED',
        'DB_CDP_ERROR_APP_IMPORT_TABLE_DATA_BUFFER_UNDERRUN',
        'DB_CDP_ERROR_AGENT_APP_TIME_INCONSISTENT',
        'DB_CDP_ERROR_RECOVERY_TARGET_DATETIME_INVALID',
        'DB_CDP_ERROR_AGENT_NIC_CONFIG_ERROR',
        'DB_CDP_ERROR_AGENT_FAILBACK_NIC_CONIFG_ERROR',
        'DB_CDP_ERROR_AGENT_APP_TIME_ZONE_INCONSISTENT',
        'DB_CDP_ERROR_APP_LOG_MODE_NOARCHIVELOG',
        'DB_CDP_ERROR_APP_SUPPLEMENTAL_LOG_CONFIG_ERROR',
        'DB_CDP_ERROR_APP_ASM_CONFIG_ERROR',
        'DB_CDP_ERROR_AGENT_CACHE_USED_SPACE_EXCESS',
        'DB_CDP_ERROR_AGENT_ENABLED_SIZE_NOT_ENOUGH',
        'DB_CDP_ERROR_APP_TABLESPACE_DIR_PERMISSION_ERROR',
        'DB_CDP_ERROR_AGENT_CLUSTER_CONFIG_ERROR',
        'DB_CDP_ERROR_AGENT_SELECT_COPY_USERS_ERROR',
        'DB_CDP_ERROR_AGENT_USER_TABLESPACE_STATUS_ERROR',
        'DB_CDP_ERROR_AGENT_PDB_OPEN_MODE_ERROR',
        'DB_CDP_ERROR_AGENT_PDB_CONFIG_ERROR',
        'DB_CDP_ERROR_LOAD_BALANCING_CHANGE_NODE',
        'DB_CDP_ERROR_AGENT_PORT_OPEN_STATUS_ERROR',
        'DB_CDP_ERROR_AGENT_MEMORY_EXCESS_THRESHOLD',
        'DB_CDP_ERROR_RECOVERY_TARGET_SCN_INVALID',
        'DB_CDP_ERROR_RECOVERY_BACKUP_INFO_INVALID',
        'DB_CDP_ERROR_APP_TABLESPACE_SIZE_NOT_ENOUGH',
        'DB_CDP_ERROR_AGENT_CACHE_ENABLE_SIZE_BELOW_THRESHOLD',

        27000 => 'DB_CDP_ERROR_APP_GENERIC_ERROR',
        'DB_CDP_ERROR_APP_EXPORT_DICTIONARY_ERROR',
        'DB_CDP_ERROR_APP_IMPORT_DICTIONARY_ERROR',
        'DB_CDP_ERROR_APP_EXPORT_TABLE_DATA_ERROR',
        'DB_CDP_ERROR_APP_IMPORT_TABLE_DATA_ERROR',
        'DB_CDP_ERROR_APP_IMPORT_CONSTRAINTS_ERROR',
        'DB_CDP_ERROR_APP_DISABLE_JOBS_AND_TRIGGER_ERROR',
        'DB_CDP_ERROR_APP_IMPORT_SEQUENCES_ERROR',
        'DB_CDP_ERROR_APP_ENABLE_JOBS_AND_TRIGGER_ERROR',
        'DB_CDP_ERROR_APP_EXPORT_DICTIONARY_SUCCESS',
        'DB_CDP_ERROR_APP_IMPORT_DICTIONARY_SUCCESS',
        'DB_CDP_ERROR_APP_EXPORT_DATA_SUCCESS',
        'DB_CDP_ERROR_APP_IMPORT_DATA_SUCCESS',
        'DB_CDP_ERROR_APP_IMPORT_CONSTRAINT_SUCCESS',
        'DB_CDP_ERROR_APP_IMPORT_SEQUENCE_SUCCESS',
        'DB_CDP_ERROR_APP_ENABLE_JOB_AND_TRIGGER_SUCCESS',
        'DB_CDP_ERROR_APP_DISABLE_JOB_AND_TRIGGER_SUCCESS',
        'DB_CDP_ERROR_APP_TAKEOVER_SUCCESS',
        'DB_CDP_ERROR_APP_RECOVERY_SUCCESS',
        'DB_CDP_ERROR_APP_TRANSACTION_REPLAY_ERROR',
        'DB_CDP_ERROR_APP_GET_REDO_LOG_INFO_ERROR',
        'DB_CDP_ERROR_APP_LOG_CAPTURE_ERROR',
        'DB_CDP_ERROR_APP_LOAD_LOG_ANALYSIS_LIB_ERROR',
        'DB_CDP_ERROR_APP_REDO_LOG_ANALYSIS_ERROR',
        'DB_CDP_ERROR_APP_REDO_LOG_CACHE_THREAD_ERROR',
        'DB_CDP_ERROR_APP_INTI_LOG_REPLAY_THREAD_ERROR',
        'DB_CDP_ERROR_APP_REDO_LOG_WRITE_THREAD_ERROR',
        27027 => 'DB_CDP_ERROR_APP_RECEIVE_DICTIONARY_DATA_SUCCESS',
        'DB_CDP_ERROR_APP_AGENT_TO_AGENT_CONNECT_ERROR',

        27030 => 'DB_CDP_ERROR_APP_REDO_LOG_ANALYSIS_NOT_RESPONDED_ERROR',
        'DB_CDP_ERROR_APP_SEND_RECOVERY_CACHE_SUCCESS',
        'DB_CDP_ERROR_APP_SEND_RECOVERY_CACHE_ERROR',
        'DB_CDP_ERROR_APP_RECEIVE_RECOVERY_CACHE_SUCCESS',
        'DB_CDP_ERROR_APP_RECEIVE_RECOVERY_CACHE_ERROR',
        'DB_CDP_ERROR_APP_RECOVERY_CACHE_REMAP_SUCCESS',
        'DB_CDP_ERROR_APP_RECOVERY_CACHE_REMAP_ERROR',
        'DB_CDP_ERROR_APP_EXPORT_OLR_SCHEMA_SUCCESS',
        'DB_CDP_ERROR_APP_EXPORT_OLR_SCHEMA_ERROR',
        'DB_CDP_ERROR_APP_SEND_OLR_SCHEMA_SUCCESS',
        'DB_CDP_ERROR_APP_SEND_OLR_SCHEMA_ERROR',
        'DB_CDP_ERROR_APP_RECEIVE_OLR_SCHEMA_SUCCESS',
        'DB_CDP_ERROR_APP_RECEIVE_OLR_SCHEMA_ERROR',
        'DB_CDP_ERROR_APP_TRANSMIT_OFFLINE_RESIDUAL_DATA_ERROR',
        'DB_CDP_ERROR_APP_TRANSMIT_OFFLINE_RESIDUAL_DATA_SUCCESS',

        28500 => 'ELITE_RCVY_INIT_ERROR',
        'ELITE_RCVY_PARSE_CONFIG_ERROR',
        'ELITE_RCVY_MAGIC_ERROR',
        'ELITE_RCVY_OPCODE_INVALID_ERROR',
        'ELITE_RCVY_NO_DETECTED_CONNECTION_ERROR',
        'ELITE_RCVY_CREATE_WORKER_PROCESS_ERROR',
        'ELITE_RCVY_CHECK_WORKER_PROCESS_LISTEN_ERROR',
        'ELITE_RCVY_LOAD_AGENT_CONF_ERROR',
        'ELITE_RCVY_START_FUSE_THREAD_ERROR',
        'ELITE_RCVY_RESOURCE_NOT_EXIST_ERROR',
        'ELITE_RCVY_BUILD_CACHE_PATH_ERROR',
        'ELITE_RCVY_OPERATION_TIMEOUT_ERROR',
        'ELITE_RCVY_FUSE_UNEXPECTED_READ_REQUEST_ERROR',
        'ELITE_RCVY_MOUNT_POINT_ALREADY_EXIST_ERROR',
        'ELITE_RCVY_CREATE_SMB_USER_ERROR',
        'ELITE_RCVY_DELETE_SMB_USER_ERROR',
        'ELITE_RCVY_COPY_CONFIG_FILE_ERROR',
        'ELITE_RCVY_FILE_PROTOCOL_NOT_SUPPORT_ERROR',
        'ELITE_RCVY_NO_FILES_SCANNED_ERROR',
        'ELITE_RCVY_EMBED_RESOURCE_INSUFFICIENT_ERROR',
        'ELITE_RCVY_TWO_LIST_SIZE_MISMATCH_ERROR',
        'ELITE_RCVY_CATCH_EXCEPTION_ERROR',
        'ELITE_RCVY_CLUSTER_SIZE_NOT_DIVISIBLE_ERROR',
        'ELITE_RCVY_CLUSTER_SIZE_NOT_EQUAL_ERROR',
        'ELITE_RCVY_DISK_TYPE_NOT_SUPPORT_ERROR',
        'ELITE_RCVY_FUSE_DATA_MAPPING_TYPE_NOT_SUPPORT_ERROR',
        'ELITE_RCVY_MODULE_TYPE_NOT_SUPPORT_ERROR',
        'ELITE_RCVY_VM_HYPERVISOR_TYPE_NOT_SUPPORT_ERROR',
        'ELITE_RCVY_CLUSTER_SIZE_INVALID_ERROR',
        'ELITE_RCVY_MAP_KEY_NOT_FOUND_ERROR',
        'ELITE_RCVY_RESOURCE_RESIDUE_ERROR',
        'ELITE_RCVY_ADD_DISK_TO_EMBED_VM_ERROR',
        'ELITE_RCVY_CONVERT_PTR_ERROR',
        'ELITE_RCVY_CONNECT_EMBED_VM_AGENT_ERROR',
        'ELITE_RCVY_RESOURCE_CROSSED_ERROR',
        'ELITE_RCVY_MOUNT_DISK_PARTITION_ERROR',
        'ELITE_RCVY_UMOUNT_ERROR',
        'ELITE_RCVY_COMPRESS_FILES_ERROR',
        'ELITE_RCVY_SMB_USER_ALREADY_EXIST_ERROR',
        'ELITE_RCVY_TASK_IS_NON_RUNNING_ERROR',
        'ELITE_RCVY_RESIDUAL_WORKER_NO_CLEAN_FINISH_ERROR',
        'ELITE_RCVY_CHECK_WORKER_NETWORK_LISTEN_ERROR',
        'ELITE_RCVY_KEY_REPET_ERROR',

        30500 => 'TIMEPOINT_INIT_ERROR',
        'TIMEPOINT_PARSE_CONFIG_ERROR',
        'TIMEPOINT_MAGIC_ERROR',
        'TIMEPOINT_OPCODE_INVALID_ERROR',
        'TIMEPOINT_NO_DETECTED_CONNECTION_ERROR',
        'TIMEPOINT_CREATE_WORKER_PROCESS_ERROR',
        'TIMEPOINT_CHECK_WORKER_PROCESS_LISTEN_ERROR',
        'TIMEPOINT_LOAD_AGENT_CONF_ERROR',
        'TIMEPOINT_RESOURCE_NOT_EXIST_ERROR',
        'TIMEPOINT_UPDATE_OPERATION_STATUS_ERROR',
        'TIMEPOINT_UNSUPPORTED_MODULE_TYPE_ERROR',
        'TIMEPOINT_UNSUPPORTED_TIMEPOINT_OPERATION_ERROR',
        'TIMEPOINT_DELETE_TIMEPOINT_HAS_DIFF_TIMEPOINT_EXIST_ERROR',
        'TIMEPOINT_EXTEND_WORM_FOR_BACKUP_CHAIN_ERROR',
        'TIMEPOINT_MERGE_OPERATION_STATUS_RESIDUE',
        'TIMEPOINT_BACKUP_POINT_SET_WORM_CANNOT_DELETE',
        'TIMEPOINT_RESIDUAL_WORKER_NO_CLEAN_FINISH_ERROR',
        'TIMEPOINT_CHECK_WORKER_NETWORK_LISTEN_ERROR',
        'TIMEPOINT_GET_GLOBAL_LOCK_TIMEOUT_ERROR',
        'TIMEPOINT_BACKUP_POINT_IS_MERGING_CANNOT_UPDATE_OPERATION_STATUS_ERROR',
        'TIMEPOINT_BACKUP_POINT_IS_DELETING_CANNOT_UPDATE_OPERATION_STATUS_ERROR',
        'TIMEPOINT_BACKUP_POINT_IS_SCANNING_CANNOT_UPDATE_OPERATION_STATUS_ERROR',
        'TIMEPOINT_BACKUP_POINT_IS_CHECKING_CANNOT_UPDATE_OPERATION_STATUS_ERROR',
        'TIMEPOINT_ROLL_BACK_OF_MERGE_ERROR',
        'TIMEPOIN_NO_NODE_CAN_DELETE_TIMEPOINT_ERROR',
        'TIMEPOINT_GLOBAL_RESERVED_STRATEGY_ALREADY_EXIST_ERROR',
        32500 => 'SR_DEPLOY_VIRTUAL_LAB_ERROR',
        'SR_GET_VIRTUAL_LAB_INFO_ERROR',
        'SR_GET_HOST_CLUSTER_ERROR',
        'SR_PING_TEST_VM_ERROR',
        'SR_BUILD_SURE_BACKUP_ITEM_LIST_ERROR',
        'SR_DEPLOY_SURE_BACKUP_NETWORK_ERROR',
        'SR_DELETE_NETWORK_MAP_ERROR',
        'SR_REMOVE_FOLDER_ERROR',
        'SR_DELETE_VIRTUAL_LAB_ERROR',
        'SR_DELETE_APPLICATION_GROUP_ERROR',
        'SR_EDIT_APPLICATION_GROUP_ERROR',
        'SR_GET_ITEM_MODULE_TYPE_ERROR',
        'SR_UNKNOW_SUREBACKUP_TYPE_ERROR',
        'SR_INTEGRITY_CHECK_VM_ERROR',
        'SR_VIRUS_SCAN_VM_ERROR',
        32515 => 'SR_HEARTBEAT_TEST_VM_ERROR',
        'SR_DOWNLOAD_SCREEN_SHOT_ERROR',
        'SR_DOCUMENT_CONSISTENCY_VM_ERROR',
        'SR_CACULATE_ISOLATED_IP_ADDRESS_ERROR',
        'SR_VM_NOT_GENERATE_TIMEPOINT_ERROR',
        'SR_OS_NOT_GENERATE_TIMEPOINT_ERROR',
        'SR_FS_NOT_GENERATE_TIMEPOINT_ERROR',
        'SR_DB_NOT_GENERATE_TIMEPOINT_ERROR',
        'SR_INSTANT_RECOVERY_NFS_SERVER_IP_NOT_MATCH_ERROR',
        'SR_VIRTUAL_ENVIRMENT_OFFLINE_ERROR',
        'SR_VIRTUAL_LAB_PROXY_CREATE_FLOPPY_FILE_ERROR',
        'SR_ADD_IP_ROUTE_ERROR',
        'SR_DEL_IP_ROUTE_ERROR',
        'SR_NOT_SUPPORT_HYPERVISOR_ERROR',
        'SR_NOT_SUPPORT_MODULE_TYPE_ERROR',



        40000 => 'VINFS_INIT_ERROR',
        'VINFS_FILTER_FILE_NOT_EXIST',
        'VINFS_FILTER_FILE_ALREADY_EXIST',
        'VINFS_OPEN_NEW_BITMAP_FILE_ERROR',
        'VINFS_READ_NEW_BITMAP_FILE_ERROR',
        'VINFS_WRITE_NEW_BITMAP_FILE_ERROR',
        'VINFS_UNKNOWN_OPCODE_ERROR',
        'VINFS_CREATE_NEW_BACKUP_PATH_ERROR',
        'VINFS_OPEN_BITMAP_FILE_ERROR',
        'VINFS_READ_BITMAP_FILE_ERROR',
        'VINFS_WRITE_BITMAP_FILE_ERROR',
        'VINFS_OPEN_BACKUP_FILE_ERROR',
        'VINFS_CREATE_VM_INSTANT_DIR_ERROR',
        'VINFS_GET_NFS_STATUS_ERROR',
        'VINFS_START_NFS_ERROR',
        'VINFS_STOP_NFS_ERROR',
        'VINFS_RESTART_NFS_ERROR',
        'VINFS_RESTART_RPCBIND_ERROR',
        'VINFS_UMOUNT_VINFS_ERROR',
        'VINFS_NOT_FOUND_TASK_ERROR',
        'VINFS_NOT_FOUND_DISK_ERROR',

        'VINFS_UNEXPORT_NFS_TABLE_ITEM_ERROR',
        'VINFS_GET_NFS_TABLE_LIST_ERROR',



        45000 => 'VXEFS_INIT_ERROR',
        'VXEFS_MOUNT_VXEFS_ERROR',
        'VXEFS_UMOUNT_VXEFS_ERROR',
        'VXEFS_TASK_NOT_FOUND_ERROR',
        'VXEFS_UNKNOWN_OPCODE_ERROR',
        'VXEFS_FILTER_DISK_ALREADY_EXIST_ERROR',
        'VXEFS_FILTER_DISK_NOT_EXIST_ERROR',
        'VXEFS_CREATE_CACHE_DIR_ERROR',
        'VXEFS_ORIGINAL_BAT_INDEX_NOT_FOUND_ERROR',
        'VXEFS_FUSE_UNEXPECTED_READ_REQUEST_ERROR',



        //********************//
        //******WEB错误定义******//
        //******50000开始******//
        //********************//

        //******平台错误定义******//
        50100 => 'PF_USER_USER_PASS_ERROR',      //用户名或密码错误
        'PF_USER_LOGIN_LOCK_ERROR',

        50200 => 'PF_JOB_ADD_JOB_ERROR',        //任务
        50300 => 'PF_DATA_DELETE_DATA_ERROR',   //数据中心
        50400 => 'PF_LOG_DELETE_LOG_ERROR',     //日志
        50500 => 'PF_SETTING_TIME_ERROR',       //系统配置
        'PF_SETTING_NOTICE_SMSMODEM_CONNECT_DB_ERROR',
        'PF_SETTING_NOTICE_SMSMODEM_INSERT_DB_ERROR',

        50600 => 'PF_AGENT_ADD_AGENT_ERROR',    //代理
        50700 => 'PF_STORAGE_GET_STORAGE_ERROR',//存储

        50800 => 'PF_ALARM_DELETE_ALARM_ERROR', //告警
        50900 => 'PF_ARCHIVE_ADD_ARCHIVE_ERROR',//归档
        51000 => 'PF_REPORT_EXCEL_REPORT_ERROR',//报表
        51100 => 'PF_MANOEUVRE_UNKNOWN',        //灾难演练
        51500 => 'PF_SOCKET_CREATE_ERROR',            //其他,如socket etc..
        'PF_SOCKET_CONNECT_ERROR',
        'PF_SOCKET_SEND_ERROR',
        'PF_SOCKET_RECV_ERROR',
        'PF_SOCKET_GET_OP_STATUS_ERROR',
        'PF_SOCKET_GET_OP_TIMEOUT',
        'PF_SOCKET_OP_STATUS_ERROR',
        'PF_SOCKET_SYSTEM_SERVICE_ERROR',
        'PF_SOCKET_CURL_ERROR',

        //******虚拟机模块错误定义******//
        52000 => 'VM_VCENTER_GET_SUB_MODULE_ERROR',
        'VM_VCENTER_GET_VCENTER_ID_ERROR',
        'VM_VCENTER_GET_HOST_ID_ERROR',
        'VM_VCENTER_GET_ID_ERROR',


        //******文件模块错误定义******//
        53000 => 'FS_',

        //******数据库模块错误定义******//
        54000 => 'DB_',

        //******在线升级相关错误******//
        55000 => 'UPDATE_PRESERVE',
        'UPDATE_PARAMS_ERROR',
        'UPDATE_PARAMS_VERSION_ERROR',
        'UPDATE_BLACK_LIST_ERROR',
        'UPDATE_NETWORK_ERROR',
        'UPDATE_REQUEST_ERROR',

        //******60000 - 69999 全部为OEM数据库定时预留******//
        60000 => '',

        //******文件模块错误定义******//

        100000 => 'FS_TARGET_NOT_FOUND_ERROR',     // server target not found error
        'FS_TASK_BACKUP_LIST_EMPTY_ERROR',		// backup list empty
        'FS_BACKUP_CONTAINER_FULL_ERROR',		// backup container is reach the limit
        'FS_QUERY_TIMEPOINT_BACKUP_LIST_ERROR',	// query timepoint backup list error
        'FS_LOAD_MD5_INDEX_FILE_ERROR',			// load timepoint md5 file error
        'FS_OPEN_BACKUP_INDEX_FILE_ERROR',		// open backup index file error
        'FS_CONVERT_BACKUP_PATH_MD5_ERROR',		// covert md5 error
        'FS_NAS_NOT_MOUNT_ERROR',       // nas not mount error
        'FS_CHECK_FILE_FAILED',
        'FS_CHECK_FILE_STOP',
        'FS_CREATE_LINUX_SNAPSHOT_ERROR',
        'FS_DELETE_LINUX_SNAPSHOT_ERROR',
        'FS_FILE_CHANGED',
        'FS_FILE_BUFFER_CHECK_ERROR',
        'FS_DIR_NOT_EMPTY_ERROR',
        'FS_ACL_GET_ERROR',
        'FS_ALC_TO_STRING_ERROR',
        'FS_GET_FILE_LINK_ERROR',
        'FS_SHORTCUT_CREATE_ERROR',
        'FS_ACL_RECOVERY_ERROR',
        'FS_FILE_TIME_SET_ERROR',
        'FS_FILE_MODE_SET_ERROR',
        'FS_USER_AND_GROUP_SET_ERROR',
        'FS_USER_OR_GROUP_NOT_EXIST',
        'FS_USER_OR_GROUP_STRING_SAVE_ERROR',
        'FS_SYMBOL_LINK_FILE_CREATE_ERROR',
        'FS_HARD_LINK_FILE_CREATE_ERROR',
        'FS_READ_PACKET_NOT_USEFUL_ERROR',
        'FS_READ_USER_OLD_ID_ERROR',
        'FS_SET_FILE_PERMISSION_ERROR',
        'FS_PERMISSION_CANNOT_SET',
        'FS_ACL_CANNOT_SET',
        'FS_SCAN_DIR_ERROR',
        'FS_GET_FILE_MODE_ERROR',
        'FS_ERROR_TASK_NOT_FINISH',
        'FS_ERROR_METABASE_WAS_LOCKED',
        'FS_ERROR_CONTAINER_WAS_LOCKED',
        102000 => 'OBS_INVAILD_APPID', // create cos-bucket with a invaild APPID
        'OBS_EXIST_ERROR', // the obs to create has already existed
        'OBS_NOT_EXIST_ERROR', // the obs to create has already existed
        'OBS_IS_BUSY_ERROR', // the obs to access is not exist
        'OBS_CONNECT_ERROR',
        'OBS_EMPTY_PREFIX_ERROR',
        'OBS_IS_NOT_AUTH',
        'OBS_OBJECT_HAS_BEEN_MODIFIED',
        'OBS_S3_NOT_INITIALIZED',
        'OBS_S3_ALREADY_INITIALIZED',
        'OBS_S3_HAS_BEEN_DESTROYED',
        104000 => 'FS_HADOOP_CLUSTER_TASK_BUSY_ERROR',
        'FS_HADOOP_CLUSTER_ALREADY_EXIST_ERROR',
        'FS_HADOOP_NAMENODES_OFFLINE_ERROR',
        'FS_HADOOP_NAMENODES_ALREADY_EXIST_ERROR',
        'FS_HADOOP_NAMENODE_CONNECT_HDFS_ERROR',
        'FS_HADOOP_NAMENODE_NOT_MATCH_CLUSTER_ERROR',
        'FS_HADOOP_NAMENODE_USERNAME_INVALID_ERROR',
        'FS_HADOOP_HDFS_STORAGE_SPACE_NOT_ENOUGH_ERROR',
        'FS_HADOOP_NEED_ONE_ONLINE_NAMENODE_ERROR',
        //******hadoop错误定义******//
        120000 => 'HADOOP_CURL_GETINFO_RESPONSE_CODE_ERROR',
        'HADOOP_CURL_GETINFO_REDIRECT_URL_ERROR',
        'HADOOP_CURL_PERFORM_HTTP_PUT_ERROR',
        'HADOOP_CURL_PERFORM_HTTP_DELETE_ERROR',
        'HADOOP_CURL_PERFORM_HTTP_HEAD_ERROR',
        'HADOOP_HADOOP_USER_SNAPSHOT_PATH_EXIST_ERROR',
        'HADOOP_HADOOP_GET_SNAPSHOT_DIFF_REPORT_ERROR',
        'HADOOP_CLUSTER_UUID_NOT_EXIST_ERROR',
        'HADOOP_NAMENODE_NOT_MATCH_CLUSTER_ERROR',
        'HADOOP_NAMENODE_CONNECT_HDFS_ERROR',
        'HADOOP_NAMENODE_IP_NOT_EXIST_ERROR',
        'HADOOP_NAMENODE_IP_INVALID_ERROR',
        'HADOOP_NAMENODE_NOT_AVAILABLE_ERROR',
        'HADOOP_HDFS_DOWNLOAD_FILE_ERROR',
        'HADOOP_HDFS_UPLOAD_FILE_ERROR',
        'HADOOP_HDFS_CREATE_FILE_ERROR',
        'HADOOP_HDFS_CREATE_DIRECTORY_ERROR',
        'HADOOP_HDFS_SET_PERMISSION_ERROR',
        'HADOOP_HDFS_GET_FILE_DIR_STATUS_ERROR',
        'HADOOP_HDFS_GET_FILE_ACL_STATUS_ERROR',
        'HADOOP_HDFS_LIST_FILE_DIR_STATUS_ERROR',
        'HADOOP_HDFS_GET_DISPLAY_DIR_LIST_ERROR',
        'HADOOP_HDFS_ALLOW_SNAPSHOT_ERROR',
        'HADOOP_HDFS_DISALLOW_SNAPSHOT_ERROR',
        'HADOOP_HDFS_CREATE_SNAPSHOT_ERROR',
        'HADOOP_HDFS_DELETE_SNAPSHOT_ERROR',
        'HADOOP_HDFS_RENAME_SNAPSHOT_ERROR',
        'HADOOP_HDFS_SNAPSHOT_NOT_EXIST_ERROR',
        'HADOOP_HDFS_SNAPSHOT_ALREADY_EXIST_ERROR',
        'HADOOP_HDFS_LIST_SNAPSHOT_NAME_ERROR',
        'HADOOP_HDFS_GET_SNAPSHOT_DIR_LIST_ERROR',
        'HADOOP_HDFS_SCAN_DIR_CHILD_ITEM_ERROR',
        'HADOOP_HDFS_GET_CLUSTER_ID_ERROR',
        'HADOOP_TASK_CLUSTER_BUSY_ERROR',
        'HADOOP_TASK_NAMENODE_BUSY_ERROR',
        'HADOOP_TASK_QUERY_FS_INDEX_ERROR',
        'HADOOP_SET_RECORD_TO_HOSTS_ERROR',
        'HADOOP_SET_RECORD_TO_KERBEROS_ERROR',
        'HADOOP_DELETE_RECORD_FROM_HOSTS_ERROR',
        'HADOOP_DELETE_RECORD_FROM_KERBEROS_ERROR',
        'HADOOP_EXECUTE_CMD_ERROR',
        120041 => 'HADOOP_KERBEROS_KINIT_ERROR',
        'HADOOP_NOT_FIND_AIM_SNAPSHOT_ERROR',
        'HADOOP_SET_FILE_TIME_ERROR',
        'HADOOP_UNKNOWN_INC_FILE_TYPE',
        'HADOOP_ERROR_MSG_EMPTY_ERROR',
        'HADOOP_DATANODE_ERROR',
        130000 => 'FS_FAST_INC_HEAD_HASH_MAP_ERROR',
        'FS_FAST_INC_HASH_DIR_EMPTY_POINT_ERROR',
        'FS_FAST_INC_ERROR_NO_FATHER_NAME_CAN_CUT',
        'FS_FAST_INC_ERROR_ILLEGAL_DIFF_TYPE',
        'FS_FAST_INC_NOT_FIND_DEPEND_SNAPSHOT',
        'FS_FAST_INC_HEAD_DIR_NOT_PUT_INTO_MAP_FIRST',
        'FS_FAST_INC_TAKE_DIFF_RESULT_ABNORMAL',
        'FS_FAST_INC_DIFF_WILDCARD',
        'FS_FAST_INC_DIFF_BACKUP_OBJECT',
        'FS_FAST_INC_NAS_UNSUPPORT_SNAPSHOT',
        'FS_FAST_INC_NAS_SHARE_INFO_NOT_EXIST',
        'FS_FAST_INC_SNAPDIFF_NOT_OPEN',
        'FS_FAST_INC_OCEANSTOR_UNDEFINE_ERROR_CODE',
        // 文件复制错误码定义
        150000 => 'SYNC_ERROR_UNKNOWN',
        'SYNC_ERROR_LOAD_SCAN_FILE_PATHNAME_ERROR',
        'SYNC_ERROR_CHECK_SCAN_FILE_ERROR',
        'SYNC_ERROR_SEND_THREAD_START_ERROR',
        'SYNC_ERROR_TRANSRPOT_READ_PACEKT_ERROR',
        'SYNC_ERROR_GET_FILE_SIZE_ERROR',
        'SYNC_ERROR_PACKET_RECACHE_ERROR',
        'SYNC_ERROR_ITEM_NOT_FOUND',
        'SYNC_ERROR_BUFFER_FULL_ERROR',
        'SYNC_ERROR_MD5_OBJECT_CHANGE',
        'SYNC_ERROR_MD5_EMPTY_RESULT',
        'SYNC_ERROR_UPDATE_STRUCT_ERROR',
        'SYNC_ERROR_TASK_MODE_ERROR',
        'SYNC_ERROR_SCAN_NOT_FINISH',
        'SYNC_ERROR_META_SAVE_NOT_INIT_ERROR',
        'SYNC_ERROR_COMPARE_SAVE_DIR_PATH_INVAILD',
        'SYNC_ERROR_THREAD_NOT_RUNNING',
        'SYNC_ERROR_TASK_NOT_FINISH',
        'SYNC_ERROR_META_FILE_UNCOMPLETE',
        'SYNC_ERROR_OFFSET_OVER_FILE_SIZE',
        'SYNC_ERROR_FILE_NOT_SAME_TPYE',
        'SYNC_ERROR_FILE_NOT_SAME_SIZE',
        'SYNC_ERROR_BIG_FILE_HANDLE_BE_DELETE',
        'SYNC_ERROR_PACKET_PUT_INTO_NET_LIST_ERROR',
        'SYNC_ERROR_FILE_RENAME_ERROR',
        'SYNC_ERROR_SURE_HEAD_NOT_BE_FOUND',
        'SYNC_ERROR_SURE_HEAD_NOT_USEFUL',
        'SYNC_ERROR_PACKET_NOT_BELONG_TO_SELF',
        'SYNC_ERROR_NO_CACHE_PLACE',
        'SYNC_ERROR_DIR_META_NOT_USEFUL',
        'SYNC_ERROR_PACKET_NOT_PUSH',
        'SYNC_ERRPR_EMPTY_POINT',
        'SYNC_ERROR_INVALIED_QUEUE_ERROR',
        'SYNC_ERROR_INVALID_FILE_TYPE',
        'SYNC_ERROR_INIT_WINDOWS_CREATE_INSTANCE',
        'SYNC_ERROR_INIT_WINDOWS_SHELL_QUERY_INTER_FACE',
        'SYNC_ERROR_NOT_SUPPORT_CREATE_SNAPSHOT',
        'SYNC_ERROR_SPEED_OPERATOR_NOT_EXIST',
        'SYNC_ERROR_COMPARE_RESULT_UNAVAILABLE',
        //******数据库模块错误定义******//
        300000 => 'DATABASE_UNKNOWN_ERROR', 					// database unknown error
        'DATABASE_DB_INSTANCE_NOT_EXIST_ERROR',			// the db instance is not exist error
        'DATABASE_DB_LIST_NOT_EXIST_ERROR',				// the db list is not exist error
        'DATABASE_FIND_BACKUP_FILE_ID_ERROR',				// find backup file id error 
        'DATABASE_NOT_SUPPORT_DB_TYPE_ERROR',				// not support database type
        'DATABASE_CREATE_BACKUP_DIR_ERROR',				// create backup directory error
        'DATABASE_BUILD_BACKUP_DB_LIST_ERROR',			// build backup db list error
        'DATABASE_BUILD_RECOVERY_DB_LIST_ERROR',			// build recovery db list error
        'DATABASE_LATEST_TIMEPOINT_NOT_EXIST_ERROR',		// the latest timepoint is not exist error
        'DATABASE_BACKUP_CHAIN_IS_USING_ERROR',			// the backup chain is using error
        'DATABASE_SAVE_SELF_EXPLAN_FILE_ERROR',			// save self explan file error
        'DATABASE_OPEN_BACKUP_FILE_ERROR',				// open backup file error
        'DATABASE_OPEN_BITMAP_FILE_ERROR',				// open bitmap file error
        'DATABASE_WRITE_BACKUP_FILE_ERROR',				// write backup file error
        'DATABASE_WRITE_BITMAP_FILE_ERROR',				// write bitmap file error
        'DATABASE_READ_BACKUP_FILE_ERROR',				// read backup file error
        'DATABASE_READ_BITMAP_FILE_ERROR',				// read bitmap file error
        'DATABASE_COMPRESS_ERROR',						// compress error
        'DATABASE_DECOMPRESS_ERROR',						// decompress error
        'DATABASE_CONTAINER_FULL_ERROR',					// the container is full error
        'DATABASE_GET_RECOVERY_TOTAL_SIZE_ERROR',			// database get recovery total size error


        'DATABASE_SCAN_INSTANCE_ERROR',					// scan instance error
        'DATABASE_CONNECT_DB_ERROR',						// connect to database error
        'DATABASE_GET_DB_VERSION_ERROR',					// get db version error
        'DATABASE_GET_DBID_ERROR',						// get dbid error
        'DATABASE_DB_NOT_EXIST_ERROR',					// the database not exist error
        'DATABASE_SCAN_DB_ERROR',							// scan db error
        'DATABASE_SCAN_TABLE_SPACE_ERROR',				// scan table space error
        'DATABASE_SCAN_PDB_ERROR',						// scan pdb error
        'DATABASE_CHECK_DB_ERROR',						// check db error
        'DATABASE_GET_RECOVERY_MODE_ERROR',				// get recovery mode error
        'DATABASE_SET_RECOVERY_MODE_ERROR',				// set recovery mode error
        'DATABASE_CHECK_DB_NAME_EXIST_ERROR',				// check db name exist error
        'DATABASE_CREATE_DB_ERROR',						// create db error
        'DATABASE_DELETE_DB_ERROR',						// delete db error
        'DATABASE_SET_SINGLE_USER_ERROR',					// set single user error
        'DATABASE_SET_MULTI_USER_ERROR',					// set multi user error
        'DATABASE_CHECK_ARCHIVE_LOG_OPEN_ERROR',			// check archive log open error
        'DATABASE_CHECK_ARCHIVE_LOG_IN_SHARE_STORAGE_ERROR',	//check archive log in share storage error
        'DATABASE_CHECK_BLOCK_CHANGE_TRACKING_ENABLE_ERROR',	//check blcok change tracking enable error
        'DATABASE_OPEN_BLOCK_CHANGE_TRACKING_ERROR',		//open block change tracking error
        'DATABASE_GET_CURRENT_INCARNATION_ERROR',			//get current incarnation error
        'DATABASE_GET_CURRENT_SCN_ERROR',					//get current scn error
        'DATABASE_GET_ARCHIVE_LOG_NEWEST_SEQUENCE_ERROR', //get archive log newest sequence error
        'DATABASE_SQL_ALLOCATE_HANDLE_ERROR',			    // sql allocate handle error
        'DATABASE_SQL_SET_ENV_ATTR_ERROR',				// sql set env attr error
        'DATABASE_OCI_ENV_CREATE_ERROR',					// oci env create error
        'DATABASE_OCI_HANDLE_ALLOC_ERROR',				// oci handle allocate error
        'DATABASE_MASTER_DATABASE_NOT_SUPPORT_DIFF_OR_LOG_BACKUP',		// master database not support diff or log backup
        'DATABASE_SIMPLE_MODE_NOT_SUPPORT_LOG_BACKUP',					// simple mode not support log backup
        'DATABASE_INCLUDE_UNKONWN_BACKUPSET_ERROR',						// include other unknown backupset error
        'DATABASE_GET_DB_BACKUPSET_INFO_ERROR',							// get database 's backupset info error
        'DATABASE_GET_DB_BACKUPSET_BACKUP_START_DATE_ERROR',			    // get db backupset backup start date error
        'DATABASE_VDI_NOT_INIT_ERROR',									// vdi not init error
        'DATABASE_NOT_OPEN_ARCHIVE_LOG_ERROR',							// not open archive log error
        'DATABASE_GET_RMAN_BACKUP_JOB_ERROR',								// get rman backup job information error
        'DATABASE_INCLUDE_UNKONWN_RMAN_BACKUP_JOB_ERROR',					// include unknown rman backup job error

        'DATABASE_GET_RMAN_BACKUP_JOB_START_TIME_ERROR',					// get rman backup job start time error
        'DATABASE_GET_SQL_DB_CONFIG_ERROR',												// get sql db config error


        //mysql error code
        'DATABASE_CONNECT_SERVER_ERROR',						//connect to mysql server failed
        'DATABASE_DO_QUERY_ERROR',							//mysql do query error
        'DATABASE_INFO_NOT_EXIST_ERROR',						//xtrabackup_info file not exists
        'DATABASE_ITEM_NOT_FOUND_ERROR',						//not found item in file
        'DATABASE_LAST_TIMEPOINT_NOT_EXIST',					//last timepoint not exist
        'DATABASE_BIN_LOG_NOT_OPEN',							//bin log is off
        'DATABASE_UNKNOWN_BACKUP_MODE',						//unknown backup mode
        'DATABASE_XTRABACKUP_ERROR',							//xtrabackup backup error
        'DATABASE_GET_RECOVERY_INFO_ERROR',					//get db recovery info from db
        'DATABASE_DIR_NOT_EMPTY_ERROR',						//dir not empty error
        'DATABASE_PREPARE_BACKUP_DATA_ERROR',					//xtrabackup prepare backup data error
        'DATABASE_GET_BACKUP_INFO_ERROR',						//get info which mysql backup need error

        //add for dm
        'DATABASE_INSTANCE_IS_RUNNING_ERROR',					//instance is running (实例正在运行) 
        'DATABASE_GET_SQL_RESULT_ERROR',						//get sql result error (获取数据库执行语句结果错误)
        'DATABASE_INVALID_INSTALL_DB_USER',					//invalid install database user (无效的安装数据库用户名)
        'DATABASE_FIND_SBT_PATH_ERROR',						//find sbt path error (获取sbt 的路径错误)
        'DATABASE_INVALID_USERNAME_PASSWD_ERROR',				//invalid user name or password error (无效的用户名/密码) 
        'DATABASE_HAVE_NO_INSTANCE_WERE_SCANED',				//have no instance were scaned (没有获取到实例)
        'DATABASE_NOT_RUNNING_ERROR',							//database not running (数据库没有运行)

        //add for psql
        'DATABASE_OPEN_PIPE_ERROR',							//open pipe error (打开管道文件失败)
        'DATABASE_INIT_OR_ALLOC_DCI_ERROR',					//init or alloc dci error (初始化/分配DCI句柄失败)
        'DATABASE_SET_SAFELY_CONFIG_ERROR',					//set safely config error (设置安全配置失败)
        'DATABASE_BEGIN_CONNECT_ERROR',						//begin connect error (开始连接数据库失败)
        'DATABASE_EXEC_CMD_ERROR',							//exec cmd error (执行数据库语句失败)
        'DATABASE_OPEN_SYS_SERVICE_CONF_ERROR',				//open service config (打开服务配置文件失败)
        'DATABASE_GET_USER_OF_CLUSTER_PATH_ERROR',			//get user of cluster path error (获取实例集簇路径的用户名失败)
        'DATABASE_GET_GROUP_OF_CLUSTER_PATH_ERROR',			//get group of cluster path error (获取实例集簇路径的归属组失败)
        'DATABASE_ARCHIVE_COMMAND_IS_DISABLE_ERROR',			//archive command is disable (归档命令没有开启)
        'DATABASE_WAL_LEVEL_TOO_LOW_ERROR',					//wal level too low error (wal 级别太低)
        'DATABASE_CREATE_PIPE_ERROR',							//create pipe error (创建管道失败)
        'DATABASE_TAR_DATABASE_CLUSTER_ERROR',				//tar datbase cluster error (打包实例集簇文件失败)
        'DATABASE_DELETE_PIPE_ERROR',							//delete pipe error (删除管道失败)
        'DATABASE_FIND_ARCHIVE_PATH_BY_CLUSTER_ERROR',		//find archive path by cluster error (通过集簇路径获取归档路径失败)
        'DATABASE_ANALYSIS_ARCHIVE_PATH_ERROR',				//analysis archive path error (解析归档路径失败)
        'DATABASE_CHECK_OR_CREATE_RESTORE_DESTINE_PATH_ERROR',//check or create restore destin path error (检测/创建指定恢复路径失败)
        'DATABASE_PORT_IS_OCCUPIED',							//port is occupied (端口被占用)
        'DATABASE_CHECK_LATESE_WAL_LOG_ERROR',				//check latest wal log error (检测最近的归档日志错误)

        'DATABASE_DESTIN_FOLDER_IS_NOT_EMPTY_ERROR',			//destin path is not empty error (指定恢复的文件夹不为空)
        'DATABASE_CHECK_CURRENT_WAL_LOG_STAT_ERROR',			//check current wal log stat error (检测当前的wal 日志状态错误)
        'DATABASE_DESTIN_ARCHIVE_PATH_IS_NOT_EMPTY_ERROR',	//destin archive path is not empty (指定的归档路径不为空)
        'DATABASE_PARTITION_INSUFFICIENT_MARGIN',				//partion insufficient margin (分区余量不足)
        'DATABASE_GET_STORE_ALARM_ERROR',						//get store alarm error (获取存储告警提示失败)	
        'DATABASE_CHECK_INSTALL_DB_PATH_ERROR',                 //check install db path error (检测数据库安装目录失败) 

        'DATABASE_VERSION_NOT_SUPPORT_ERROR',     	//database version not support(不支持的数据库版本)
        'DATABASE_LISTEN_IP_INVALID_ERROR',     	//database listen ip invalid error
        'DATABASE_GET_LISTEN_PORT_ERROR',      		//get database listen port error
        'DATABASE_GET_INSTALL_DB_USERNAME_ERROR',   //get install db username error
        'DATABASE_IS_ALREADY_RECOVERD_ERROR',     //database is already recoverd error

        'DATABASE_SPECIFIED_TIME_IS_AFTER_THE_VALID_LOG',   //the specified time is after the valid log(指定恢复时间在有效日志之后)
        'DATABASE_START_INSTANCE_ERROR',                        //start db instance error(启动数据库实例失败)
        'DATABASE_BINLOG_FILE_NOT_EXIST', //binlog file not exist(日志文件不存在)
        'DATABASE_WITHOUT_SYSDBA_OR_SYSBACKUP_USER_PERMISION_ERROR', //without sysdba or sysbackup user permision(没有sysdba或sysbackup用户权限)
        'DATABASE_WITHOUT_DBA_USER_PERMISION_ERROR',    //without dba user permision(没有dba用户权限)
        'DATABASE_ROLE_NOT_BELONG_CLUSTER',         //role not belong this cluster
        'DATABASE_WITHOUT_SYSADMIN_USER_PERMISION_ERROR',		// with out sysadmin user permision(没有sysadmin用户权限)
        'DATABASE_NODES_LESS_THAN_CLUSTER',     //the selected nodes are less than that in the cluster(选择认证的节点少于集群内的节点)
        'DATABASE_RECOVERY_PATH_HAVE_NO_PERMISSION', //the selected path have no permission to recovery(选择的路径没有恢复权限)
        'DATABASE_NEED_RERUN_RECOVERY', //the recovery need rerun(需要重新调用recovery)

        'DATABASE_INSTANCE_SERVICE_NOT_RUNNING_ERROR',
        'DATABASE_DB_NOT_RUNNING_ERROR',
        'DATABASE_CONFIGURE_DB_USING_BACKINT_ERROR',
        'DATABASE_CONFIGURE_DB_BACKINT_CHANNELS_ERROR',
        'DATABASE_CONFIGURE_DB_AUTO_LOG_BACKUP_INTERVAL_ERROR',
        'DATABASE_NOT_FIND_MASTER_NODE_ERROR',
        'DATABASE_SNAPSHOT_CONTROLFILE_NOT_IN_SHARE_STORAGE',
        'DATABASE_ARCHIVE_LOG_LOST',
        'DATABASE_DATAPATH_HAS_CHANGED_ERROR',
        300126 => 'DATABASE_CLUSTER_SERVER_NOT_RUNNING',
        'DATABASE_INCARNATION_CHNAGED_ERROR',
        'DATABASE_LAST_BACKUP_WAS_NON_BACKINT',
        'DATABASE_LOG_CANNOT_BE_DOWNGRADED_TO_COMPLETE',
        // add for tidb
        'DATABASE_TIKV_SERVER_NOT_WORKING_ERROR',			// one of the tikv servers not working
        'DATABASE_GET_BACKUP_TIMESTAMP_ERROR',			// get backup timestamp error
        'DATABASE_BR_TASK_FAILED_ERROR',					// br task failed
        'DATABASE_RESULT_OF_QUERY_IS_EMPTY_ERROR',			// the result of the query is empty(查询的返回结果为空)
        'DATABASE_INITIALIZE_CLIENT_ERROR',					// initialize client error(初始化数据库实例连接失败)
        'DATABASE_QUERRY_MESSAGE_ERROR',						// querry message error with mongoc func(使用mongoc接口函数查询信息错误)
        'DATABASE_INSTANCE_DEPENDENT_PORT_NOT_EXIST',			// instance port is not exist(实例相关端口不存在)
        'DATABASE_NOT_SUPPORT_TO_BACKUP_LOG',					// not support to backup log(不支持备份日志)
        'DATABASE_RUNNING_INSTANCE_HAS_CHANGED',				// the running instance has changed(运行的实例已经改变)
        'DATABASE_JOURNAL_IS_NOT_ENABLED',					// the journal is not enabled(journal没有开启)
        'DATABASE_BACKUP_PARTITION_HAS_CHANGED',				// the backup partition has changed(备份的分区已经改变)
        'DATABASE_STOP_BALANCER_ERROR',						// stop balancer error(停止balancer失败)
        'DATABASE_START_BALANCER_ERROR',						// start balancer error(启动balancer失败)
        'DATABASE_FIND_KEY_IN_JSON_ERROR',					// find key in json error(从获取的结果json中没找到对应的key)
        'DATABASE_SPLIT_STRING_ERROR',						// split the string by pattren error(分离字符串错误)
        'DATABASE_JOURNAL_PATH_IS_NOT_EXIST',					// the journal path is not exist(journal日志路径不存在)
        'DATABASE_TIMESTAMP_IS_NOT_EXIST',					// the timestamp is not exist(时间戳不存在)
        'DATABASE_SPECIFIED_PATH_IS_NOT_EXIST',				// the specified path is not exist(指定的路径不存在)
        'DATABASE_GET_FILE_SYSTEM_TYPE_ERROR',				// get file system type error(获取文件系统类别错误)
        'DATABASE_FIND_RECOVERY_OPLOG_FILE_ERROR',			// find the oplog file needed to recovery error(未找到需要恢复的oplog文件)
        'DATABASE_NOT_FIND_THE_AVAILABLE_COMPLETE_INSTANCE',	// not find the avaliable complete instance(未发现可用的完整实例)
        'DATABASE_BACKUP_CHAIN_IS_MERGING_ERROR',				// database backup chain is merging(备份链正在进行合并)
        'DATABASE_COPY_BITMAP_FILE_ERROR',					// database copy the bitmap file error(拷贝bitmap文件失败)
        'DATABASE_MERGE_TIMEPOINT_ERROR',						// database meger timepoint error(合并时间失败)
        'DATABASE_DEDUPE_BLOCK_SIZE_ERROR',					// database dedupe block size error(重删块大小错误)
        'DATABASE_UPDATE_BACKUP_TOTAL_SIZE_ERROR',			// database update backup total size error(更新备份总大小失败)
        'DATABASE_INC_AND_DIFF_BACKUP_POINT_IN_BACKUP_CHAIN', // inc and diff backup point in backup chain(增备点和差备点同时存在备份链中)
        'DATABASE_BACKUP_CLUSTER_NODE_CHANGED',				// backup cluster node changed(备份的数据库集群节点发生变化)
        'DATABASE_HANA_DESTROY_TRANSFER_CHANNEL_ERROR',
        'DATABASE_LOAD_LIBRARY_ERROR',
        'DATABASE_CURRENT_TIMEPOINT_HAS_NO_DEPEND_TIMEPOINT_ERROR',
        'DATABASE_TARGET_PATH_IS_NOT_EXIST_ERROR',
        'DATABASE_TARGET_PATH_IS_EMPTY_ERROR',
        //主机操作系统模块
        400000 => 'OS_SERVER_GET_BACKUP_INFO_ERROR',								//get backup info for backup error
        'OS_SERVER_GET_RECOVERY_INFO_ERROR',										//get recovery info for recovery error
        'OS_SERVER_OS_NOT_EXIST_ERROR',											//not found info in os machine list error
        'OS_SERVER_BD_AGENT_NOT_EXIST_ERROR',										//not found info in bd agent
        'OS_SERVER_CREATE_BACKUP_DIR_ERROR',										//create timepoint dir in backup storage error
        'OS_SERVER_CREATE_SNAPSHOT_ERROR',										//create snapshot error
        'OS_SERVER_FIND_BACKUP_FILE_ID_ERROR',									//get backup file id for timepoint error
        'OS_SERVER_MACHINE_REBOOT_ERROR',											//machine rebooted after last full backup
        'OS_SERVER_STORAGE_CHANGED_ERROR',										//storage changed error
        'OS_SERVER_LIST_DISK_ERROR',												//list disk in machine error
        'OS_SERVER_SAVE_SELF_EXPLAN_FILE_ERROR',									//save self-description file error
        'OS_SERVER_CONTAINER_FULL_ERROR',											//container file is too large
        'OS_SERVER_READ_REMOTE_VOLUME_ERROR',										//read volume data from os client error
        'OS_SERVER_WRITE_BITMAP_FILE_ERROR',										//write bitmap file error
        'OS_SERVER_GET_VOLUME_BITMAP_ERROR',										//get volume bitmap error
        'OS_SERVER_BACKUP_MODE_ERROR',											//backup mode is different from last timepoint backup mode
        'OS_SERVER_READ_PARTITION_TABLE_ERROR',									//read partition table from os client error
        'OS_SERVER_GET_RECOVERY_TOTAL_SIZE_ERROR',								//os get recovery total size error
        'OS_SERVER_REPARTED_FLAG_ERROR',											//os reparted flag error
        'OS_SERVER_VOLUME_NOT_EXIST_ERROR',										//volume not exist error
        'OS_SERVER_WRITE_VOLUME_ERROR',											//20write data to volume error
        'OS_SERVER_VOLUME_CLUSTER_SIZE_UNABLE_DIVIDE_ERROR',						//cluster size can't be divided
        'OS_SERVER_LOCK_VOLUME_ERROR',											//unmount volume error
        'OS_SERVER_RELOCKED_VOLUME_ERROR',										//volume has been locked, relocked error
        'OS_SERVER_FORMATTING_VOLUME_ERROR',										//formatting volume error
        'OS_SERVER_SPACE_NOT_ENOUGH',												//disk space not enough
        'OS_SERVER_REPARTED_ERROR',												//reparted disk error
        'OS_VOLUME_NUM_CHANGED_ERROR',											//volume which we need backup num changed
        'OS_DISK_CHANGED_ERROR',													//volume which we need backup changed
        'OS_SERVER_STRATEGY_ALLOC_ERROR',											//alloc startegy error
        'OS_SERVER_DELETE_SNAPSHOT_ERROR',										//delete snapshot error
        'OS_SERVER_UNKNOWN_OS_TYPE',												//unknown os type
        'OS_SERVER_CREATE_PARTITION_ERROR',										//create partition error
        'OS_SERVER_GRUB_INSTALL_ERROR',											//install grub into device error
        'OS_SERVER_REPAIR_FSTAB_FILE_ERROR',										//rewrite /etc/fstab file for new os error
        'OS_SERVER_REPAIR_GRUB_CFG_ERROR',										//rewrite grub.cfg file for new os error
        'OS_SERVER_ENCRYPT_INFO_CHANGED_ERROR',									//加密信息改变
        'OS_SERVER_WINDOWS_BOOT_VOLUME_SAME_AS_SYSTEM_VOLUME_ERROR',				//windows系统引导分区和系统分区相同,找不到引导分区可恢复的位置,建议打开重建分区进行恢复
        'OS_SERVER_BOOT_VOLUME_NOT_EXIST_IN_TARGET_MACHINE_ERROR',				//在目标磁盘上找不到可用的引导分区用于恢复,请开启重建分区功能恢复
        'OS_SERVER_TIMEPOINT_NOT_EXIST_ERROR',									//所依赖的备份时间点不存在
        'OS_SERVER_HAS_DIFF_TIMEPOINT_EXIST_ERROR',        //备份链上存在差备点,需要先删除差备点
        'OS_SERVER_VOLUME_NOT_UNMOUNT_ERROR',          //卷未解挂载错误,需要手动解挂设备
        'OS_SERVER_OVERWRITE_SYSTEM_VOLUME_ERROR',        //无法覆盖写入正在使用的系统分区
        'OS_SERVER_AGENT_IN_USING_ERROR',			//代理正在被备份任务或恢复任务使用
        'OS_SERVER_VALID_BACKUP_FLAG_CHANGED_ERROR',  //获取有效数据标记变化降级
        'OS_SERVER_EMPTY_BACKUP_VOLUME_ERROR', //无可备份分区
        'OS_CLIENT_DISK_BAD_SECTOR',// 检测到磁盘坏道，读写数据失败
        'OS_SERVER_CBT_INVALID',
        'OS_SERVER_CBT_DOWNGRAGE', //cbt降级为普通增量模式
        'OS_LIVECD_VERSION_DISCREPANCY', //livecd版本过低
        'OS_SERVER_UNKNOWN_BOOT_TYPE',//未知引导模式
        'OS_RESULT_IS_NULL',
        //操作系统瞬时恢复
        'OS_OSVOLVINFS_CONNECTION_INIT_FAILED', //Initialize the connection failed which connect to the osvolvinfs
        'OS_CONNECT_OSVOLVINFS_FAILED', //Connect to the osvolvinfs failed when start the instantaneous recovery
        'OS_OSVOLVINFS_CREATE_DISK_FILE_FAILED',//osvol_vinfs create disk file failed for task
        'OS_OSVOLVINFS_FILTER_DISK_ALREADY_EXIST', //filter disk alreay exist in map
        'OS_OSVOLVINFS_FILTER_DISK_NOT_EXIST', //filter disk not exist
        'OS_OSVOLVINFS_TASK_NOT_FOUND', //task not find in filter map
        'OS_INST_TASK_HAS_BEEN_CREATED', //instantaneous task has been created in osvolvinfs
        'OS_OPEN_BACKUP_FILE_ERROR', //open backup file erro
        'OS_OPEN_BITMAP_FILE_ERROR', //open bitmap file error
        'OS_CONNECT_DISK_LIB_ERROR', //connect to disk lib error
        'OS_READ_BITMAP_FILE_ERROR', //read bitmap file error
        'OS_TASK_TYPE_ERROR', //task type error
        'OS_SERVER_OPERATION_NO_CONDITION', //can not do this operation, task type or status error
        'OS_SERVER_STOP_MIGRATION_ERROR', //can not stop migration, task type or status error
        'OS_SERVER_DELETE_INSTANTANEOUS_RECOVERY_TASK_ERROR', //delete instantaneous recovery task error
        'OS_READ_BACKUP_FILE_ERROR', //read backup file error
        'OS_INST_CREATE_TASK_INVALID_INFO', //the agent in another instantaneous task already
        'OS_SERVER_UNSUPPORT_VOLUME_TYPE',


        'OS_SERVER_COMPRESS_MECHOD_CHANGED_ERROR',                                // backup task compress method has been changed, need to degrade
        'OS_GET_HARDWARE_INFO_FAILED',                                            // get hardware info failed
        'OS_NOT_HAVE_DRIVER_ERROR',                                               // os not have driver error
        'OS_GET_LINUX_MODULE_INFO_FAILED',                                        // os get linux module info error
        'OS_ANALYSIS_MODULE_ALIAS_ERROR',                                         // os analysis module alias error
        'OS_NOT_SUPPORT_OS_TYPE_ERROR',                                           // os not support os type error
        'OS_NOT_SUPPORT_BUS_TYPE_ERROR',                                          // os not support bus type error
        'OS_DRIVERS_POOL_LACK_DRIVER_ERROR',                                      // os drivers pool lack driver error
        'OS_ADD_DRIVER_FAILED',                                                   // os add driver failed
        'OS_BACKUP_AGENT_IS_RUNNING_INST_OR_MIGRATION_TASK',                      // the backup agent is running instantaneous or migration task
        'OS_RECOVERY_AGENT_IS_RUNNING_INST_OR_MIGRATION_TASK',                    // the recovery agent is running instantaneous or migration task
        'OS_INST_TARGET_AGENT_IS_RUNNING_BACKUP_OR_RECCOVERY_TASK',               // the instantaneous recovery target agent is running backup or recovery task
        'OS_AGENT_ISCSI_HAS_BEEN_LOGGED_OUT',
        'OS_PARTITION_BIOSBOOT_OR_BOISGPT_ERROR',
        'OS_INST_TASK_TARGET_AGENT_OFFLINE_OR_POWEROFF',
        'OS_CBT_FAILED_AND_STORED_AS_TAPE',
        'OS_CBT_NOT_OPENED_AND_STORED_AS_TAPE',
        'OS_SYSTEM_AND_RESERVED_NOT_ON_THE_SAME_DISK',
        'OS_CBT_FAILED_AND_BACKUP_MODE_DIFFERNTIAL',
        'OS_BTRFS_UUID_CONFLICT',
        'OS_SERVER_EMPTY_BACKUP_DISK_ERROR',
        'OS_SERVER_DISK_NOT_EXIST_ERROR',
        'OS_SERVER_WRITE_DEVICE_ERROR',
        'OS_STOP_CBT_MONITOR_ERROR',
        'OS_EXIST_REMOVABLEDEV',
        'OS_INST_TASK_VOLUME_MOUNT_ERROR',
        'OS_INST_TASK_AGENT_BEFORE_ISCSI_OP_SCRIPTS_RUN_ERROR',
        'OS_INST_TASK_AGENT_AFTER_ISCSI_OP_SCRIPTS_RUN_ERROR',
        'OS_INST_TASK_AGENT_ISCSI_OP_SCRIPTS_RUN_ERROR',
        'OS_RECOVERY_MEDIA_AND_SOURE_BACKUP_SYSTEM_INCONSISTENT',
        'OS_STORAGE_STRATEGY_HAS_BEEN_CHANGED',
        'OS_LAST_BACKUP_ERROR_INCOMPLETE_MONITORING_DATA',
        'OS_MEMORY_NOT_ENOUGH_WHEN_CREATE_SNAPSHOT',
        'OS_BACKUP_DISK_SIZE_HAS_BEEN_CHANGED',
        'OS_RESTART_AGENT_DRIVER_ERROR',

        //********卷CDP模块错误码定义*********//
        500000 => 'VOL_CDP_GENERIC_SUCCESS',  //VolCDP Generic success
        'VOL_CDP_ERROR_PACKET_ORDER',  //VolCDP packet sequence exception detected
        'VOL_CDP_ERROR_PACKET_ABNORMAL',  //VolCDP abnormal package structure
        'VOL_CDP_ERROR_UNKNOWN_OP_CODE',  //unknown opcode
        'VOL_CDP_ERROR_TASK_NOT_EXIST',  //task does not exist
        'VOL_CDP_ERROR_LOAD_TASK_INFO_FAILED',  //failed to load task information
        'VOL_CDP_ERROR_TASK_NOT_IN_TAKEOVER_STATUS',  //task is not in takeover state
        'VOL_CDP_ERROR_TASK_NOT_IN_RUNNING_STATUS',  // task is not in running status
        'VOL_CDP_ERROR_TASK_NOT_IN_FAULT_RESUME_STAGE',  //task is not in fault resume stage
        'VOL_CDP_ERROR_NOT_FIND_RUNNING_TASK',  //not find running task
        'VOL_CDP_ERROR_INVALID_VOL_ID',  //5010  invalid volume id
        'VOL_CDP_ERROR_AGENT_FILE_CACHE_NO_SPACE',  //file cache has no free space
        'VOL_CDP_ERROR_AGENT_MEMORY_CACHE_NO_SPACE',  //memory cache has no free space
        'VOL_CDP_ERROR_AGENT_WRITE_CACHE_FAILED',  //write cache failed
        'VOL_CDP_ERROR_AGENT_FIND_CACHE_FILE_FAILED',  //failed to find cache file
        'VOL_CDP_ERROR_AGENT_MAP_CACHE_MDL_FAILED',  //failed to map cache space
        'VOL_CDP_ERROR_AGENT_LOAD_CACHE_CONFIG_FAILED',  //failed to load cache configuration file
        'VOL_CDP_ERROR_AGENT_CREATE_CACHE_DIR_FAILED',  //failed to create data cache directory
        'VOL_CDP_ERROR_AGENT_OPEN_CACHE_FILE_FAILED',  //failed to open the volume where the cache file is located
        'VOL_CDP_ERROR_AGENT_QUERY_CACHE_VOL_FAILED',  //failed to query cache volume
        'VOL_CDP_ERROR_AGENT_CREATE_DATA_MONITOR_FAILED',  //500020  ailed to start data monitoring
        'VOL_CDP_ERROR_AGENT_UNMOUNT_VOL_FAILED',  //unmount volume failed
        'VOL_CDP_ERROR_AGENT_HANDSHAKE_FAILED',  //failed to handshake with agent
        'VOL_CDP_ERROR_OPEN_SNAPSHOT_VOL_FAILED',  //failed to open snapshot volume
        'VOL_CDP_ERROR_OPEN_BACKUP_VOL_FAILED',  //failed to open backup volume
        'VOL_CDP_ERROR_OPEN_RESTORE_VOL_FAILED',  //failed to open restore volume
        'VOL_CDP_ERROR_FORMAT_VOL_FAILED',  //failed to format volume
        'VOL_CDP_ERROR_CREATE_VOL_CACHE_STORAGE_FAILED',  //failed to create cache storage unit
        'VOL_CDP_ERROR_BITMAP_READ_FINISH',  //read bitmap data finish
        'VOL_CDP_ERROR_LOAD_FS_BITMAP_FAILED',  //failed to load file system bitmap
        'VOL_CDP_ERROR_NOTIFY_AGENT_PERFORM_ACTION',  //500030  notify the agent perform operation failed
        'VOL_CDP_ERROR_READ_INVALID_CACHE_META',  //read invalid cache metadata
        'VOL_CDP_ERROR_READ_INVALID_CACHE_DATA',  //read invalid cache data
        'VOL_CDP_ERROR_WAIT_ACK_TIMEOUT',  //timeout waiting for reply
        'VOL_CDP_ERROR_CONTROL_SENDER_NOT_INIT',  //control send instance not initialized
        'VOL_CDP_ERROR_AGENT_LINK_LOST',  //detected agent link lost
        'VOL_CDP_ERROR_FIND_TASK_CHILD_PROCESS',  //find task child process failed
        'VOL_CDP_ERROR_CONNECT_IO_MAPPPING_ROUTINE',  //connect io mapping routine failed
        'VOL_CDP_ERROR_GET_IO_BITMAP',  //VolCDP get io bitmap error
        'VOL_CDP_ERROR_BACKUP_IMAGE_IN_MERGE',  //VolCDP current backup data is being merged
        'VOL_CDP_ERROR_REBUILD_PARTITION',  //VolCDP rebuild partition failed

        505500 => 'VOL_CDP_ERROR_APP_GENERIC_ERROR',  //app generic error
        'VOL_CDP_ERROR_APP_SERVICE_NOT_RUNNING',  //app service not runnig
        'VOL_CDP_ERROR_PREPARE_APP_SERVICE_FAILED',  //Prepare app service failed
        'VOL_CDP_ERROR_PREPARE_APP_SERVICE_TIMEOUT',  //prepare app service timeout
        'VOL_CDP_ERROR_APP_MODULE_LIST_SIZE_INVALID',  //app module list size invalid
        'VOL_CDP_ERROR_PARSE_APP_MODULE_FILE_INFO_FAILED',  //parse app module file info failed
        'VOL_CDP_ERROR_PARSE_VOL_MOUNT_RELATION_FAILED',  //parse vol mount relation failed
        'VOL_CDP_ERROR_RESTORE_APP_CONTROL_FILE_FAILED',  //restore app control file failed
        'VOL_CDP_ERROR_RESTORE_APP_FILE_PATH_FAILED',  //restore app file path failed
        'VOL_CDP_ERROR_GET_STANDBY_APP_CONTROLFILE_FAILED',  //get standby app controlfile path failed
        'VOL_CDP_ERROR_EXECUTE_CMD_SCRIPT_FAILED',  //505510   execute command script failed
        'VOL_CDP_ERROR_DETACH_APP_MODULE_FAILED',  //detach app module failed
        'VOL_CDP_ERROR_ATTACH_APP_MODULE_FAILED',  //attach app module failed
        'VOL_CDP_ERROR_MONITOR_APP_STATUS_IS_ABNORMAL',  //monitor app status is abnormal
        'VOL_CDP_ERROR_TAKEOVER_APP_FAILED',  //takeover app failed
        'VOL_CDP_ERROR_TAKEOVER_APP_NOT_FOUND_CONTROL_FILES',  //takeover app not found control files
        'VOL_CDP_ERROR_EXECUTE_CMD_SCRIPT_NOT_EXIST',  //execute command script not exist
        'VOL_CDP_ERROR_SCAN_APP_COMPLETED_BUT_SOME_FAILED',  //scan app info completed but had some app scan faild
        'VOL_CDP_ERROR_CHANGE_APP_FILE_OWNER_FAILED',
        'VOL_CDP_ERROR_REALTIME_BACKUP_LICENSE_EXHAUST',
        'VOL_CDP_ERROR_AUTO_TAKEOVER_LICENSE_EXHAUST',  //505520

        'VOL_CDP_ERROR_ASSOCIATED_TASK_EXIST_ON_THE_HOST',
        'VOL_CDP_ERROR_AGENT_NET_FAULT',
        'VOL_CDP_ERROR_AGENT_APP_STATUS_ABNORMAL',
        'VOL_CDP_ERROR_AGENT_MONITORING_SCRIPT_EXECUTE_FAILED',
        'VOL_CDP_ERROR_AUTHORIZED_CAPACITY_INSUFFICIENT',
        'VOL_CDP_ERROR_AUTHORIZED_CAPACITY_INSUFFICIENT_TO_BACKUP',

        'VOL_CDP_ERROR_BACKUP_STORAGE_ROOT_PATH_NOT_EXIST',
        'VOL_CDP_ERROR_READ_CACHE_DATA_INDEX_NOT_CONTINUITY',

        'VOL_CDP_ERROR_OPEN_VOL_FILTER_DRIVER_CONTROL_DEVICE_FAILED',
        'VOL_CDP_ERROR_INITIALIZER_CACHE_FAILED',
        'VOL_CDP_ERROR_MMAP_CACHE_FAILED',
        'VOL_CDP_ERROR_DETECT_UNCONFIGURED_FILE_CACHE',
        'VOL_CDP_ERROR_CACHE_READ_OFFSET_ABNORMAL',
        'VOL_CDP_ERROR_ALLOC_MEMORY_FOR_COPY_IO_UNIT_FAILED',
        'VOL_CDP_ERROR_CACHE_VOL_SPACE_NOT_ENOUGH',
        'VOL_CDP_ERROR_DETECT_HAVE_VOL_NOT_IN_MONITOR_STATE',
        'VOL_CDP_ERROR_FAILED_TO_CREATE_BACKUP_STORAGE_ROOT_PATH',
        'VOL_CDP_ERROR_SERVER_OPEN_CACHE_FILE_FAILED',
        'VOL_CDP_ERROR_SERVER_WRITE_CACHE_FAILED',
        'VOL_CDP_ERROR_CACHE_INSUFFICIENT_COVERT_TO_CBT', //505540  缓存不足，切换为CBT
        'VOL_CDP_ERROR_HOST_SYSTEM_VOLUME_IS_DYNAMIC',  //系统卷为动态磁盘
        'VOL_CDP_ERROR_DETECT_DATA_PACK_TIMESTAMP_EXCEPTION',
        'VOL_CDP_ERROR_VOL_FILTER_DEV_NOT_EXIST',
        505544 => 'VOL_CDP_ERROR_AGENT_DATA_SEND_STOPPED',
        'VOL_CDP_ERROR_TAKEOVER_VM_PREFIX_FAILED',
        'VOL_CDP_ERROR_TAKEOVER_VM_PREFIX_TIMEOUT',
        'VOL_CDP_ERROR_TAKEOVER_VM_HANDSHAKE_TIMEOUT',
        'VOL_CDP_ERROR_MASTER_AGENT_IS_ONLINE_BEFORE_TAKEOVER',
        'VOL_CDP_ERROR_CREATE_IMAGE_SNAP_FAILED',
        'VOL_CDP_ERROR_LOAD_TAKEOVER_VM_BASE_CONFIG',
        'VOL_CDP_ERROR_SET_TAKEOVER_VM_DISK_CONFIG',
        'VOL_CDP_ERROR_ADD_INTER_NETCARD_TO_TAKEOVER_VM',
        'VOL_CDP_ERROR_CREATE_TAKEOVER_VM',
        'VOL_CDP_ERROR_DELETE_TAKEOVER_VM',
        'VOL_CDP_ERROR_START_TAKEOVER_VM',
        'VOL_CDP_ERROR_STOP_TAKEOVER_VM',
        'VOL_CDP_ERROR_GET_TAKEOVER_VM_STATUS',
        'VOL_CDP_ERROR_FILL_PARAMETER_FOR_PREFIX',
        'VOL_CDP_ERROR_MEM_STANDBY_HANDSHAKE_TIMEOUT',
        'VOL_CDP_SERVER_OPERATION_MAGIC_ERROR',
        'VOL_CDP_SERVER_OPERATION_OPCODE_INVALID_ERROR',
        'VOL_CDP_ERROR_SKIP_SCAN_VIRUS',
        'VOL_CDP_ERROR_MONITOR_EXCEPTION_OCCURRED',
        'VOL_CDP_AGENT_WRITE_DATA_ERROR',
        505565 => 'VOL_CDP_LOAD_BALANCING_CHANGE_NODE',
        'VOL_CDP_ERROR_WAIT_MEM_STANDBY_REBOOT_TIMEOUT',
        'VOL_CDP_ERROR_GET_DISK_SIGN_ERROR',
        'VOL_CDP_ERROR_THERE_ARE_NO_PART_NO_FS_DISK',
        'VOL_CDP_ERROR_TAKEOVER_STANDBY_HANDSHAKE_TIMEOUT',
        'VOL_CDP_ERROR_FAILBACK_STANDBY_HANDSHAKE_TIMEOUT',
        'VOL_CDP_ERROR_FIND_VIRUS_AND_STOP_TASK_BY_STRATEGY',
        'VOL_CDP_ERROR_HANDSHAKE_TO_REBOOTED_AGENT_TIMEOUT',
        'VOL_CDP_ERROR_VOLUME_GROUP_NAME_CONFLICTED',
        'VOL_CDP_ERROR_DISK_FILTER_DRIVER_NOT_LOADED',
        'VOL_CDP_ERROR_IMAGE_SNAPS_ALREADY_EXIST',
        'VOL_CDP_ERROR_CLIENT_REPLICATION_LICENSE_EXHAUST',
        'VOL_CDP_ERROR_EMD_TAKEOVER_LICENSE_EXHAUST',


        // add m365 common error
        600000 => 'M365_SERVER_ORGANIZATION_HAS_BEEN_ADDED',                     // m365 organization has been added(m365组织已经被添加)
        'M365_SERVER_CHECK_ORGANIZATION_ERROR',                                 // check organization error(检查m365组织环境失败)
        'M365_SERVER_GENERATE_PUBLIC_CERT_ERROR',                               // generate public cert error（生成证书失败）
        'M365_SERVER_GET_AZURE_CLI_OUTPUT_ERROR',                               // get azure cli cmd output info error（获取azure cli命令输出信息失败）
        'M365_SERVER_GET_CMD_OUTPUT_ERROR',                                     // get cmd output info error（获取shell命令输出失败）
        'M365_SERVER_AZURE_AD_LOGIN_TOKEN_EXPIRED',                             // azure ad login token expired（azure ad登录超时）
        'M365_SERVER_CREATE_AZURE_AD_APP_ERROR',                                // create azure ad app error（创建azure ad应用失败）
        'M365_SERVER_SET_AZURE_AD_APP_SECRET_ERROR',                            // set azure ad app secret error（设置azure ad应用密码失败）
        'M365_SERVER_SET_AZURE_AD_APP_CERT_ERROR',                              // set azure ad app cert error（设置azure ad应用证书失败）
        'M365_SERVER_SET_AZURE_AD_APP_PERMISSION_ERROR',                        // set azure ad app permission error（设置azure ad应用权限失败）
        'M365_SERVER_GET_ORGANIZATION_INFO_ERROR',                              // get organization info from m365_organization error（获取m365组织信息失败）
        'M365_SERVER_DELETE_AGENT_APP_ERROR',                                   // delete agent app error（删除m365组织绑定的应用失败）
        'M365_SERVER_DELETE_AZURE_AD_APP_ERROR',                                // delete azure ad app error（删除azure ad应用失败）
        'M365_SERVER_ADD_M365_ORGANIZATION_ERROR',                              // add m365 organization error（添加m365组织失败）
        'M365_SERVER_EDIT_M365_ORGANIZATION_ERROR',                             // edit m365 organization error（修改m365组织失败）
        'M365_SERVER_DELETE_M365_ORGANIZATION_ERROR',                           // delete m365 organization error（删除m365组织失败）
        'M365_SERVER_GET_BACKUP_INFO_ERROR',               					  // get backup info for backup error（获取备份信息失败）
        'M365_SERVER_GET_RECOVERY_INFO_ERROR',								  // get recovery info for recovery error（获取恢复信息失败）
        'M365_SERVER_BD_AGENT_NOT_EXIST_ERROR',								  // not found info in bd agent（获取m365组织的客户端信息失败）
        'M365_SERVER_NOT_FIND_AVAILABLE_RPC_CLIENT_ERROR',					  // not found available rpc client（获取可用的rpc client失败）
        'M365_SERVER_CREATE_BACKUP_DIR_ERROR',								  // create timepoint dir in backup storage error（创建备份时间点目录失败）
        'M365_SERVER_FIND_BACKUP_FILE_ID_ERROR',								  // get backup file id for timepoint error（获取可用的备份容器编号失败）
        'M365_SERVER_FIND_BACKUP_TIMEPOINT_ERROR',							  // get timepoint error（获取备份时间点失败）
        'M365_SERVER_SAVE_SELF_EXPLAN_FILE_ERROR',							  // save self-description file error （创建备份点自描述信息文件失败）
        'M365_SERVER_CONTAINER_FULL_ERROR',									  // container file is too large（备份容器文件太大）
        'M365_SERVER_GET_RECOVERY_TOTAL_SIZE_ERROR',							  // M365 get recovery total size error（获取恢复的总大小失败）
        'M365_SERVER_ENCRYPT_INFO_CHANGED_ERROR',								  // encrypt info change error(加密信息改变)
        'M365_SERVER_FIND_BACKUP_OBJECT_ERROR',                                 // can not find backup object（获取备份对象失败）
        'M365_SERVER_START_PROXY_ERROR',                                        // start m365 proxy error（启动m365代理失败）
        'M365_SERVER_FIND_PROXY_PROCESS_ERROR',                                 // find m365 proxy process error（获取m365代理运行状态失败）
        'M365_SERVER_CONNECT_PROXY_PROCESS_ERROR',                              // connect m365 proxy process error（连接m365代理失败）
        'M365_SERVER_STOP_PROXY_PROCESS_ERROR',                                 // stop m365 proxy process error（停止m365代理失败）
        'M365_SERVER_FORCE_KILL_PROXY_PROCESS_ERROR',                           // force kill m365 proxy process error（强制结束m365代理失败）
        'M365_SERVER_NOT_CONNECT_PROXY_PROCESS_ERROR',                          // not connect m365 proxy process error（未连接m365代理）
        'M365_SERVER_SEND_PACEKT_TO_PROXY_PROCESS_ERROR',                       // send packet to m365 proxy process error（发送数据到m365代理失败）
        'M365_SERVER_RECV_PACEKT_FROM_PROXY_PROCESS_ERROR',                     // recv packet from m365 proxy process error（从m365代理接收数据失败）
        'M365_SERVER_UNKNOWN_RPC_PRIVATE_OPCODE_TYPE',                          // unknown rpc private opcode type（未知的m365模块操作码）
        'M365_SERVER_USER_CAN_NOT_BACKUP',                                      // user can not backup（m365用户不支持备份）
        'M365_SERVER_TRANSPORT_THREAD_EXIT_BY_USER_SCANNER_THREAD_ERROR',       // transport thread exit because of user scanner thread error（传输线程因为扫描线程错误结束）
        'M365_SERVER_CONNECT_USER_ERROR',                                       // connect m365 user error（连接用户失败）
        'M365_SERVER_TIMEPOINT_NOT_EXIST_ERROR',                                // timepoint not exist（备份时间点不存在）
        'M365_SERVER_STORAGE_CHANGED_ERROR',                                    // backup storage changed（备份存储被更改）
        'M365_SERVER_UPDATE_USER_DELTA_TOKEN_ERROR',                            // update user delta token error（更新用户增量信息失败）
        'M365_SERVER_UPDATE_GROUP_DELTA_TOKEN_ERROR',                           // update group delta token error（更新用户组增量信息失败）
        'M365_SERVER_USER_NOT_EXISTS_ERROR',                                    // user not exists error（用户不存在）
        'M365_SERVER_COMPRESS_INFO_CHANGED_ERROR',								//45
        'M365_SERVER_REFRESH_ORGANIZATION_ERROR',							      // resfresh organization error(刷新组织失败)
        'M365_SERVER_AUTH_IS_FULL_ERROR',							              // auth is full error(授权已满)
        'M365_SERVER_USER_IS_IN_OTHER_TASKS_ERROR',							  // user is in other tasks error(用户存在其他任务中)
        'M365_SERVER_META_FILE_EXIST_ERROR',                                    // meta file exist(因为索引文件存在，同步索引数据失败)
        'M365_SERVER_NODE_CHANGED_ERROR',                                       // node changed(节点被改变)
        'M365_SERVER_UNKNOWN_EXCH_DATA_TYPE',                                   // unknown exch data type（未知的Exchange数据类型）
        'M365_SERVER_GET_EXCH_DATA_ERROR',                                      // get exchange data error（获取Exchange数据失败）
        'M365_CREATE_USER_INDEX_TABLE_IN_SQLITE_ERROR',                         // create user index table in sqlite error（创建索引表失败）
        'M365_SERVER_INSERT_EXCH_INDEX_DATA_TO_SQLITE_ERROR',                   // insert index data to sqlite database error（保存索引数据失败）
        'M365_SERVER_GET_MESSAGE_MIMECONTENT_ERROR',                            // get exch mail mimecontent data error（获取Exchnage邮件mimecontent数据失败）
        'M365_SERVER_GET_MESSAGE_XML_DATA_ERROR',                               // get exch mail xml data error（获取Exchange邮件xml数据失败）
        'M365_SERVER_GET_ITEM_STRUCTCONTENT_DATA_ERROR',                        // get exch item structcontent data error（获取Exchange原始数据失败）
        'M365_SERVER_FIND_INDEX_DB_FILE_ERROR',                                 // find index db file error（找不到索引容器文件）
        'M365_SERVER_SCAN_DB_FILE_ERROR',                                       // scanner db file error（扫描索引容器文件失败）
        'M365_SERVER_MOVE_NEW_DB_FILE_ERROR',                                   // move new db file error（移动索引容器文件失败）
        'M365_SERVER_UNKNOWN_EXCH_RECOVERY_OBJECT_TYPE_ERROR',                  // unknown exch recovery object type error（未知的Exchange恢复对象类型）
        'M365_SERVER_USE_SMTP_SEND_EMAIL_ERROR',                                // use smtp send email error（使用smtp发送邮件失败）
        'M365_SERVER_PARSE_MIMECONTENT_ERROR',                                  // parse mimecontent error（解析mimecontent失败）
        'M365_SERVER_GET_EXCH_BACKUP_DATA_ERROR',                               // get exchange backup data error（获取Exchange备份数据失败）
        'M365_SERVER_GET_SMTP_CONFIG_ERROR',                                    // get smtp config error（获取smtp配置信息失败）
        650000 => 'M365_SERVER_RESOURCE_NOT_EXITS_ERROR',                        // resource not exits error（数据资源不存在）
        'M365_SERVER_GET_RESOURCE_ERROR',                                       // resource not exits error（读取数据资源失败）
        'M365_SERVER_USER_NOT_AUTH',                                            // user not auth（用户没有认证）


        680000 => 'KUBE_CLIENT_CLUSTER_GET_VERSION_ERROR',                             //get cluster version error
        'KUBE_CLIENT_CLUSTER_API_RESPONSE_EMPTY_ERROR',                      //cluster api response empty error
        'KUBE_CLIENT_CLUSTER_API_RESPONSE_STATUS_IS_FAILURE_ERROR',          //cluster api response status is failure error
        'KUBE_CLIENT_CLUSTER_API_RESPONSE_HTTP_CODE_400_BAD_REQUEST_ERROR',  //cluster api response http code is 400 bad request error
        'KUBE_CLIENT_CLUSTER_API_RESPONSE_HTTP_CODE_401_UNAUTHORIZED_ERROR', //cluster api response http code is 401 unauthorized error
        'KUBE_CLIENT_CLUSTER_API_RESPONSE_HTTP_CODE_403_FORBIDDEN_ERROR',    //cluster api response http code is 403 forbidden error
        'KUBE_CLIENT_CLUSTER_API_RESPONSE_HTTP_CODE_404_NOT_FOUND_ERROR',    //cluster api response http code is 404 not found error
        'KUBE_CLIENT_CLUSTER_API_RESPONSE_HTTP_CODE_409_CONFLICT_ERROR',     //cluster api response http code is 409 conflict error
        'KUBE_CLIENT_CLUSTER_API_RESPONSE_HTTP_CODE_4XX_FAILED_ERROR',       //cluster api response http code is 4xx failed error
        'KUBE_CLIENT_CLUSTER_API_RESPONSE_HTTP_CODE_5XX_SERVER_ERROR',       //cluster api response http code is 5xx server error
        'KUBE_CLIENT_CLUSTER_API_RESPONSE_HTTP_CODE_GENERIC_ERROR',          //cluster api response http code is generic error
        'KUBE_CLIENT_CLUSTER_LOAD_KUBE_CONFIG_FAILED_ERROR',                 //load kube config failed
        'KUBE_CLIENT_CLUSTER_CONNECTION_FAILED_ERROR',                       //cluster connection failed error
        'KUBE_CLIENT_CLUSTER_RESOURCE_NOT_EXIST_ERROR',                      //resource not exist error
        'KUBE_CLIENT_CLUSTER_GET_NODES_ERROR',                               //get nodes error
        'KUBE_CLIENT_CLUSTER_GET_NAMESPACES_ERROR',                          //get namespaces error
        'KUBE_CLIENT_CLUSTER_GET_PVCS_ERROR',                                //get pvcs error
        'KUBE_CLIENT_CLUSTER_GET_GROUP_VERSIONS_ERROR',                      //get group versions error
        'KUBE_CLIENT_CLUSTER_GET_CRD_RESOURCE_TYPES_ERROR',                  //get crd resource types error
        'KUBE_CLIENT_CLUSTER_GET_CORE_RESOURCE_TYPES_ERROR',                 //get core resource types error
        'KUBE_CLIENT_CLUSTER_EXEC_FAILED_ERROR',                             //exec failed error
        'KUBE_CLIENT_CLUSTER_RESOURCES_LIST_FAILED_ERROR',                   //resources list failed error
        'KUBE_CLIENT_INVALID_RESOURCE_JSON_RESPONSE_ERROR',                  //invalid resource json response error
        'KUBE_CLIENT_MISSING_KEY_IN_JSON_RESOURCE_RESPONSE_ERROR',           //missing key in json resource response error
        'KUBE_CLIENT_VERSION_IS_EMPTY_ERROR',                                //version is empty error
        'KUBE_CLIENT_KIND_IS_EMPTY_ERROR',                                   //kind is empty error
        'KUBE_CLIENT_GET_RESOURCES_LIST_ERROR',                              //get resources list error
        'KUBE_CLIENT_NO_WORKER_POD_FOUND_ERROR',                             //no worker pod found error
        'KUBE_CLIENT_NO_NAMESPACE_FOUND_ERROR',                              //no namespace found error
        'KUBE_CLIENT_INVALID_RESOURCE_CATEGORY_ERROR',                       //invalid resource category error
        'KUBE_CLIENT_START_PROCESS_FAILED_ERROR',                            //start process failed
        'KUBE_CLIENT_WAIT_PROCESS_FAILED_ERROR',                             //wate process failed
        'KUBE_CLIENT_GET_ROOT_PATH_OF_POD_FAILED_ERROR',                     //get root path of pod failed
        'KUBE_CLIENT_CREATE_TEMP_DIR_FAILED_ERROR',                          //create temp dir failed
        'KUBE_CLIENT_GET_MOUNT_POINT_ERROR',                                 //get mount point error
        'KUBE_CLIENT_GET_FILE_SYSTEM_SCANNER_ERROR',                         //get file system scanner error
        'KUBE_CLIENT_CREATE_DIRECTORY_FAILED_ERROR',                         //create directory failed
        'KUBE_CLIENT_OPEN_FILE_FOR_WRITE_FAILED_ERROR',                      //open file for write failed
        'KUBE_CLIENT_WRITE_FILE_FAILED_ERROR',                               //write file failed
        'KUBE_CLIENT_OPEN_VOLUME_DEV_FAILED_ERROR',                          //open volume dev failed
        'KUBE_CLIENT_GET_VOLUME_DEV_TOTAL_SIZE_FAILED_ERROR',                //get volume dev total size failed
        'KUBE_CLIENT_GET_VOLUME_DEV_BITMAP_FAILED_ERROR',                    //get volume dev bitmap failed
        'KUBE_CLIENT_READ_VOLUME_DEV_DATA_FAILED_ERROR',                     //read volume dev data failed
        'KUBE_CLIENT_WRITE_VOLUME_DEV_DATA_FAILED_ERROR',                    //write volume dev data failed
        'KUBE_CLIENT_READ_FILE_FAILED_ERROR',                                //read file failed
        'KUBE_SERVER_CLIENT_NETWORK_CONNECTION_ERROR',                       //client network connection error
        'KUBE_SERVER_BUFFER_FORMAT_ERROR',                                   //buffer format error
        'KUBE_SERVER_JSON_PARSE_JSON_ERROR',                                 //json parse json error
        'KUBE_SERVER_JSON_KEY_EMPTY_ERROR',                                  //json key empty
        'KUBE_SERVER_JSON_KEY_IS_NOT_EXISTS_ERROR',                          //json key is not exists error
        'KUBE_SERVER_JSON_VALUE_TYPE_INCORRECT_ERROR',                       //json value type incorrect error
        'KUBE_SERVER_JSON_VALUE_TYPE_INCORRECT_OF_KEY_ERROR',                //json value type incorrect of key error
        'KUBE_SERVER_JSON_VALUE_IS_EMPTY_ERROR',                             //json value is empty error
        'KUBE_SERVER_JSON_VALUE_IS_NOT_STRING_OR_CONVERTIBLE_ERROR',         //json value is not string or convertible error
        'KUBE_SERVER_JSON_VALUE_IS_NOT_BOOL_OR_CONVERTIBLE_ERROR',           //json value is not bool or convertible error
        'KUBE_SERVER_JSON_VALUE_IS_NOT_INT_OR_CONVERTIBLE_ERROR',            //json value is not int or convertible error
        'KUBE_SERVER_YAML_CONVERT_JSON_TO_YAML_FAILED_ERROR',                //convert json to yaml failed error
        'KUBE_SERVER_YAML_CONVERT_YAML_TO_JSON_PARSE_FAILED_ERROR',          //convert yaml to json parse failed error
        'KUBE_SERVER_YAML_CONVERT_YAML_TO_JSON_FAILED_ERROR',                //convert yaml to json failed error
        'KUBE_SERVER_PROCESS_CREATE_PIPE_ERROR',                             //process create pipe error
        'KUBE_SERVER_PROCESS_FORK_FAILED_ERROR',                             //process fork failed error
        'KUBE_SERVER_CLUSTER_ALREADY_EXIST_ERROR',                           //cluster already exist
        'KUBE_SERVER_CLUSTER_OBTAIN_CLUSTER_INFO_ERROR',                     //obtain cluster info error
        'KUBE_SERVER_CLUSTER_HAS_TASK_ERROR',                                //cluster has task
        'KUBE_SERVER_CLUSTER_DELETE_CLUSTER_ERROR',                          //delete cluster error
        'KUBE_SERVER_CLUSTER_NOT_SUPPORT_DELETE_ERROR',                      //cluster not support delete error
        'KUBE_DEPLOYMENT_INVALID_KUBE_CONFIG_ERROR',                         //invalid kube config error
        'KUBE_DEPLOYMENT_SYSTEM_COMMAND_EXECUTE_ERROR',                      //system command execute error
        'KUBE_DEPLOYMENT_SYSTEM_COMMAND_EXECUTE_EMPTY_OUTPUT_ERROR',         //system command execute empty output error
        'KUBE_DEPLOYMENT_SSH_CONNECTION_ERROR',                              //ssh connection error
        'KUBE_DEPLOYMENT_SSH_AUTHENTICATION_ERROR',                          //ssh authentication error
        'KUBE_DEPLOYMENT_EXECUTE_SSH_COMMAND_ERROR',                         //execute ssh command error
        'KUBE_DEPLOYMENT_HELM_COMMAND_BIN_NOT_FOUND_ERROR',                  //helm command bin not found error
        'KUBE_DEPLOYMENT_OBTAIN_HELM_COMMAND_VERSION_ERROR',                 //obtain helm command version error
        'KUBE_DEPLOYMENT_CLUSTER_UNREACHABLE_IN_HELM_WITH_KUBE_CONFIG_ERROR', //cluster unreachable in helm with kube config error
        'KUBE_DEPLOYMENT_NO_USABLE_SERVER_IP_FOR_HELM_CHARTS_ERROR',         //no usable server ip for helm charts error
        'KUBE_DEPLOYMENT_HELM_REPO_ADD_FAILED_ERROR',                        //helm repo add failed error
        'KUBE_DEPLOYMENT_HELM_SEARCH_REPO_FAILED_ERROR',                     //helm search repo failed error
        'KUBE_DEPLOYMENT_HELM_REPO_UPDATE_FAILED_ERROR',                     //helm repo update failed error
        'KUBE_DEPLOYMENT_HELM_INSTALL_FAILED_ERROR',                         //helm install failed error
        'KUBE_DEPLOYMENT_CLIENT_AGENT_ALREADY_INSTALLED_BEFORE_ERROR',       //client agent already installed before error
        'KUBE_SERVER_CLUSTER_NOT_EXISTS_ERROR',                              //cluster not exists error
        'KUBE_SERVER_NO_ONLINE_NODE_IN_CLUSTER_ERROR',                       //no online kubernetes node in cluster error
        'KUBE_SERVER_AGENT_IS_NOT_RUNNING_IN_NODE_ERROR',                    //agent is not running in kubernetes node error
        'KUBE_SERVER_GET_BACKUP_INFO_ERROR',                                 //get backup info for backup error
        'KUBE_SERVER_GET_RECOVERY_INFO_ERROR',                               //get recovery info for recovery error
        'KUBE_SERVER_PARSE_RESOURCE_RAW_JSON_FAILED_ERROR',                  //parse resource raw json failed error
        'KUBE_SERVER_COMMAND_EXEC_FAILED_ERROR',                             //command exec failed error
        'KUBE_SERVER_COMMAND_EXEC_IN_POD_FAILED_ERROR',                      //command exec in pod failed
        'KUBE_SERVER_CREATE_BACKUP_DIR_ERROR',                               //create timepoint dir in backup storage error
        'KUBE_SERVER_GET_CLUSTER_INFO_OF_TASK_ERROR',                        //get cluster info of task error
        'KUBE_SERVER_SERIALIZE_RESOURCES_ERROR',                             //serialize resources error
        'KUBE_SERVER_UNSERIALIZE_RESOURCES_ERROR',                           //unserialize resources error
        'KUBE_SERVER_STORAGE_RESOURCE_NOT_FOUND_ERROR',                      //storage resource not found error
        'KUBE_SERVER_READ_RESOURCE_FROM_STORAGE_ERROR',                      //read resource from storage error
        'KUBE_SERVER_READ_INCORRECT_SIZE_RESOURCE_FROM_STORAGE_ERROR',       //read incorrect size resource from storage error
        'KUBE_SERVER_INCORRECT_RESOURCE_IN_STORAGE_INDEX',                   //incorrect resource in storage index
        'KUBE_SERVER_CREATE_RESOURCE_TIMEOUT_ERROR',                         //create resource timeout error
        'KUBE_SERVER_DELETE_RESOURCE_TIMEOUT_ERROR',                         //delete resource timeout error
        'KUBE_SERVER_INVALID_WORKLOAD_TYPE_ERROR',                           //invalid workload type error
        'KUBE_SERVER_DIG_RESOURCES_OF_APP_FAILED_ERROR',                     //dig resources of app failed
        'KUBE_SERVER_TRANSFER_BLOCK_WITH_BITMAP_FAILED_ERROR',               //transfer block with bitmap failed
        'KUBE_SERVER_STORAGE_PVC_DIR_NOT_EXISTS_ERROR',                      //storage pvc dir not exists error
        'KUBE_SERVER_STORAGE_BLOCKS_INDEX_FILE_NOT_EXISTS_ERROR',            //storage blocks index file not exists error
        'KUBE_SERVER_STORAGE_READ_VOLUME_BLOCKS_INDEX_ERROR',                //read volume blocks index error
        'KUBE_SERVER_STORAGE_WRITE_BLOCK_INDEX_BUFFER_ERROR',                //write block index buffer error
        'KUBE_SERVER_STORAGE_PREVIOUS_TIMEPOINT_CORRUPTED_ERROR',            //previous timepoint in storage is corrupted error
        'KUBE_SERVER_DEGRADATION_REASON_PREVIOUS_TIMEPOINT_NOT_EXIST_ERROR', //previous timepoint not exist
        'KUBE_SERVER_DEGRADATION_REASON_BACKUP_NODE_CHANGED_ERROR',          //backup node changed
        'KUBE_SERVER_DEGRADATION_REASON_BACKUP_STORAGE_IS_CHANGED_ERROR',    //backup storage changed
        'KUBE_SERVER_DEGRADATION_REASON_LAST_TIMEPOINT_BROKEN_ERROR',        //last timepoint broken
        'KUBE_SERVER_DEGRADATION_REASON_ENCRYPTION_INFO_CHANGED_ERROR',      //encryption info changed
        'KUBE_SERVER_DEGRADATION_REASON_COMPRESSION_INFO_CHANGED_ERROR',     //compression info changed
        'KUBE_SERVER_DEGRADATION_REASON_MAJOR_TASK_INFO_CHANGED_ERROR',      //major task info changed
        'KUBE_SERVER_BACKED_UP_STORAGE_DATA_IN_TIMEPOINT_IS_BROKEN_ERROR',   //backed up storage data in timepoint is broken error
        'KUBE_SERVER_CREATE_RESOURCE_FAILED_ERROR',                          //create resource failed error
        'KUBE_SERVER_PREPARE_RECOVERY_PVC_INSTANCE_FAILED_ERROR',            //prepare recovery pvc instance failed error
        'KUBE_SERVER_TARGET_PVC_IS_OCCUPIED_AND_CAN_NOT_DELETE_ERROR',       //target pvc is occupied and can not delete error
        'KUBE_SERVER_RECOVER_PVC_DATA_FAILED_ERROR',                         //recover pvc data failed error
        'KUBE_SERVER_PVC_HAVE_NO_BOUND_PV_ERROR',                            //pvc have no bound pv error
        'KUBE_SERVER_TARGET_PVC_RECOVERY_TO_HAVE_DIFFERENT_VOLUME_MODE_ERROR', //target pvc recovery to have different volume mode error
        'KUBE_SERVER_TARGET_PVC_RECOVERY_TO_HAVE_DIFFERENT_FILE_SYSTEM_ERROR', //target pvc recovery to have different file system error

    ),


    'errorCodeDes' => array(
        //平台
        'BD_GENERIC_SUCCESS' => Xphp::$_lang['WEB_ERROR_BD_GENERIC_SUCCESS'],
        'BD_GENERIC_ERROR' => Xphp::$_lang['WEB_ERROR_BD_GENERIC_ERROR'],
        'BD_NOT_INIT_ERROR' => Xphp::$_lang['WEB_ERROR_BD_NOT_INIT_ERROR'],
        'BD_REPEAT_INIT_ERROR' => Xphp::$_lang['WEB_ERROR_BD_REPEAT_INIT_ERROR'],
        'BD_INVALID_PARAM_ERROR' => Xphp::$_lang['WEB_ERROR_BD_INVALID_PARAM_ERROR'],
        'BD_QUEUE_EMPTY_ERROR' => Xphp::$_lang['WEB_ERROR_BD_QUEUE_EMPTY_ERROR'],
        'BD_MEM_FAILED_ERROR' => Xphp::$_lang['WEB_ERROR_BD_MEM_FAILED_ERROR'],

        'BD_DB_INIT_ERROR' => Xphp::$_lang['WEB_ERROR_BD_DB_INIT_ERROR'],
        'BD_DB_NOT_INIT_ERROR' => Xphp::$_lang['WEB_ERROR_BD_DB_NOT_INIT_ERROR'],
        'BD_DB_POOL_INIT_ERROR' => Xphp::$_lang['WEB_ERROR_BD_DB_POOL_INIT_ERROR'],
        'BD_DB_POOL_NOT_INIT_ERROR' => Xphp::$_lang['WEB_ERROR_BD_DB_POOL_NOT_INIT_ERROR'],
        'BD_DB_SENTENCE_INVALID_ERROR' => Xphp::$_lang['WEB_ERROR_BD_DB_SENTENCE_INVALID_ERROR'],
        'BD_DB_DO_QUERY_ERROR' => Xphp::$_lang['WEB_ERROR_BD_DB_DO_QUERY_ERROR'],
        'BD_DB_RESULT_EMPTY_ERROR' => Xphp::$_lang['WEB_ERROR_BD_DB_RESULT_EMPTY_ERROR'],
        'BD_DATABASE_CONNECT_ERROR' => Xphp::$_lang['WEB_ERROR_BD_DATABASE_CONNECT_ERROR'],

        'BD_NET_SEARCH_CONN_ERROR' => Xphp::$_lang['WEB_ERROR_BD_NET_SEARCH_CONN_ERROR'],
        'BD_NET_SEARCH_FD_ERROR' => Xphp::$_lang['WEB_ERROR_BD_NET_SEARCH_FD_ERROR'],
        'BD_NET_TIME_OUT_ERROR' => Xphp::$_lang['WEB_ERROR_BD_NET_TIME_OUT_ERROR'],
        'BD_NET_BIND_ERROR' => Xphp::$_lang['WEB_ERROR_BD_NET_BIND_ERROR'],
        'BD_NET_LISTEN_ERROR' => Xphp::$_lang['WEB_ERROR_BD_NET_LISTEN_ERROR'],
        'BD_NET_CONNECT_ERROR' => Xphp::$_lang['WEB_ERROR_BD_NET_CONNECT_ERROR'],
        'BD_NET_SET_NONBOLCK_ERROR' => Xphp::$_lang['WEB_ERROR_BD_NET_SET_NONBOLCK_ERROR'],
        'BD_NET_PERR_CLOSE_CONN_ERROR' => Xphp::$_lang['WEB_ERROR_BD_NET_PERR_CLOSE_CONN_ERROR'],
        'BD_NET_HEADER_MAGIC_ERROR' => Xphp::$_lang['WEB_ERROR_BD_NET_HEADER_MAGIC_ERROR'],
        'BD_NET_HEADER_NOT_COMPLETE_ERROR' => Xphp::$_lang['WEB_ERROR_BD_NET_HEADER_NOT_COMPLETE_ERROR'],
        'BD_NET_PACKET_INVALID_ERROR' => Xphp::$_lang['WEB_ERROR_BD_NET_PACKET_INVALID_ERROR'],
        'BD_NET_INVALID_WRITE_ITEM_ERROR' => Xphp::$_lang['WEB_ERROR_BD_NET_INVALID_WRITE_ITEM_ERROR'],
        'BD_NET_PT_SERVER_NOT_READY' => Xphp::$_lang['WEB_ERROR_BD_NET_PT_SERVER_NOT_READY'],
        'BD_NET_JSON_KEY_NOT_FOUND_ERROR' => Xphp::$_lang['WEB_ERROR_BD_NET_JSON_KEY_NOT_FOUND_ERROR'],
        'BD_WOULDBLOCK_ERROR' => Xphp::$_lang['WEB_ERROR_BD_WOULDBLOCK_ERROR'],
        'BD_INTR_ERROR' => Xphp::$_lang['WEB_ERROR_BD_INTR_ERROR'],
        'BD_NET_RECV_TIMEOUT_ERROR' => Xphp::$_lang['WEB_ERROR_BD_NET_RECV_TIMEOUT_ERROR'],
        'BD_NET_SOCKET_CLOSED_BY_PEER_ERROR' => Xphp::$_lang['WEB_ERROR_BD_NET_SOCKET_CLOSED_BY_PEER_ERROR'],
        'BD_NET_RECV_DATA_ERROR' => Xphp::$_lang['WEB_ERROR_BD_NET_RECV_DATA_ERROR'],
        'BD_NET_SEND_DATA_ERROR' => Xphp::$_lang['WEB_ERROR_BD_NET_SEND_DATA_ERROR'],

        'BD_WORK_THREAD_NOT_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_BD_WORK_THREAD_NOT_EXIST_ERROR'],
        'BD_WORK_THREADUUID_NOT_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_BD_WORK_THREADUUID_NOT_EXIST_ERROR'],
        'BD_WORK_THRED_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_BD_WORK_THRED_EXIST_ERROR'],
        'BD_WORK_THREADUUID_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_BD_WORK_THREADUUID_EXIST_ERROR'],

        'BD_TASK_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_BD_TASK_EXIST_ERROR'],
        'BD_TASK_ALREADY_RUNNING_ERROR' => Xphp::$_lang['WEB_ERROR_BD_TASK_ALREADY_RUNNING_ERROR'],
        'BD_TASK_NOT_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_BD_TASK_NOT_EXIST_ERROR'],
        'BD_TASK_NOT_STOPPED_ERROR' => Xphp::$_lang['WEB_ERROR_BD_TASK_NOT_STOPPED_ERROR'],
        'BD_TASK_BACKUP_LIST_IS_EMPTY_ERROR' => Xphp::$_lang['WEB_ERROR_BD_TASK_BACKUP_LIST_IS_EMPTY_ERROR'],
        'BD_TASK_RECOVERY_LIST_IS_EMPTY_ERROR' => Xphp::$_lang['WEB_ERROR_BD_TASK_RECOVERY_LIST_IS_EMPTY_ERROR'],
        'BD_TASK_BE_CANCELLED_ERROR' => Xphp::$_lang['WEB_ERROR_BD_TASK_BE_CANCELLED_ERROR'],
        'BD_TASK_FAIL_ERROR' => Xphp::$_lang['WEB_ERROR_BD_TASK_FAIL_ERROR'],
        'BD_TASK_ANBNORMAL_ERROR' => Xphp::$_lang['WEB_ERROR_BD_TASK_ANBNORMAL_ERROR'],

        'BD_HAS_BACKUP_EXISTED_AND_NOT_STOPPED_ERROR' => Xphp::$_lang['WEB_ERROR_BD_HAS_BACKUP_EXISTED_AND_NOT_STOPPED_ERROR'],
        'BD_HAS_RECOVERY_EXISTED_AND_NOT_STOPPED_ERROR' => Xphp::$_lang['WEB_ERROR_BD_HAS_RECOVERY_EXISTED_AND_NOT_STOPPED_ERROR'],

        'BD_TIMEPOINT_NOT_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_BD_TIMEPOINT_NOT_EXIST_ERROR'],
        'BD_TIMEPOINT_NOT_UNIQUE_ERROR' => Xphp::$_lang['WEB_ERROR_BD_TIMEPOINT_NOT_UNIQUE_ERROR'],
        'BD_DELETE_TIMEPOINT_DIR_ERROR' => Xphp::$_lang['WEB_ERROR_BD_DELETE_TIMEPOINT_DIR_ERROR'],
        'BD_SET_TIMEPOINT_DELETE_ERROR' => Xphp::$_lang['WEB_ERROR_BD_SET_TIMEPOINT_DELETE_ERROR'],
        'BD_TIMEPOINT_IN_USE_BY_TASK_ERROR' => Xphp::$_lang['WEB_ERROR_BD_TIMEPOINT_IN_USE_BY_TASK_ERROR'],
        'BD_USER_NOT_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_BD_USER_NOT_EXIST_ERROR'],
        'BD_NETWORK_RECONNECT_ERROR' => Xphp::$_lang['WEB_ERROR_BD_NETWORK_RECONNECT_ERROR'],
        'BD_AGENT_NOT_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_BD_AGENT_NOT_EXIST_ERROR'],

        'BD_STORAGE_SPACE_NOT_ENOUGH_ERROR' => Xphp::$_lang['WEB_ERROR_BD_STORAGE_SPACE_NOT_ENOUGH_ERROR'],
        'BD_SAVE_SELF_EXPLAN_FILE_ERROR' => Xphp::$_lang['WEB_ERROR_BD_SAVE_SELF_EXPLAN_FILE_ERROR'],

        'BD_ENCRYPT_ERROR' => Xphp::$_lang['WEB_ERROR_BD_ENCRYPT_ERROR'],
        'BD_DECRYPT_ERROR' => Xphp::$_lang['WEB_ERROR_BD_DECRYPT_ERROR'],
        'BD_ENCRYPT_SET_KEY_ERROR' => Xphp::$_lang['WEB_ERROR_BD_ENCRYPT_SET_KEY_ERROR'],
        'BD_ENCRYPT_READ_PUBLIC_KEY_ERROR' => Xphp::$_lang['WEB_ERROR_BD_ENCRYPT_READ_PUBLIC_KEY_ERROR'],
        'BD_ENCRYPT_READ_PRIVATE_KEY_ERROR' => Xphp::$_lang['WEB_ERROR_BD_ENCRYPT_READ_PRIVATE_KEY_ERROR'],

        'BD_PRIVILEGE_ERROR' => Xphp::$_lang['WEB_ERROR_BD_PRIVILEGE_ERROR'],
        'BD_CONDITION_VAR_TIMEOUT_ERROR' => Xphp::$_lang['WEB_ERROR_BD_CONDITION_VAR_TIMEOUT_ERROR'],

        'BD_SNAPSHOT_ERROR' => Xphp::$_lang['WEB_ERROR_BD_SNAPSHOT_ERROR'],
        'BD_SNAPSHOT_BACKUP_COMPONENTS_INIT_ERROR' => Xphp::$_lang['WEB_ERROR_BD_SNAPSHOT_BACKUP_COMPONENTS_INIT_ERROR'],
        'BD_SNAPSHOT_ADD_TO_SET_ERROR' => Xphp::$_lang['WEB_ERROR_BD_SNAPSHOT_ADD_TO_SET_ERROR'],
        'BD_SNAPSHOT_CREATE_ERROR' => Xphp::$_lang['WEB_ERROR_BD_SNAPSHOT_CREATE_ERROR'],
        'BD_SNAPSHOT_NOT_EXIST' => Xphp::$_lang['WEB_ERROR_BD_SNAPSHOT_NOT_EXIST'],
        'BD_SNAPSHOT_SET_NOT_EXIST' => Xphp::$_lang['WEB_ERROR_BD_SNAPSHOT_SET_NOT_EXIST'],
        'BD_SNAPSHOT_DELETE_ERROR' => Xphp::$_lang['WEB_ERROR_BD_SNAPSHOT_DELETE_ERROR'],
        'BD_SNAPSHOT_BAD_STATE_ERROR' => Xphp::$_lang['WEB_ERROR_BD_SNAPSHOT_BAD_STATE_ERROR'],
        'BD_SNAPSHOT_MAX_VOL_REACHED_ERROR' => Xphp::$_lang['WEB_ERROR_BD_SNAPSHOT_MAX_VOL_REACHED_ERROR'],
        'BD_SNAPSHOT_MAX_SNAPSHOT_REACHED_ERROR' => Xphp::$_lang['WEB_ERROR_BD_SNAPSHOT_MAX_SNAPSHOT_REACHED_ERROR'],
        'BD_SNAPSHOT_INVALID_XML_ERROR' => Xphp::$_lang['WEB_ERROR_BD_SNAPSHOT_INVALID_XML_ERROR'],

        'BD_RPC_FILE_NOT_EXIST' => Xphp::$_lang['WEB_ERROR_BD_RPC_FILE_NOT_EXIST'],
        'BD_RPC_FILE_ALREADY_EXIST' => Xphp::$_lang['WEB_ERROR_BD_RPC_FILE_ALREADY_EXIST'],
        'BD_RPC_HANDLE_NOT_FOUND' => Xphp::$_lang['WEB_ERROR_BD_RPC_HANDLE_NOT_FOUND'],
        'BD_RPC_NETWORK_ERROR' => Xphp::$_lang['WEB_ERROR_BD_RPC_NETWORK_ERROR'],
        'BD_RPC_MSG_ERROR' => Xphp::$_lang['WEB_ERROR_BD_RPC_MSG_ERROR'],
        'BD_RPC_NOT_SAME_CONNECTION_ERROR' => Xphp::$_lang['WEB_ERROR_BD_RPC_NOT_SAME_CONNECTION_ERROR'],
        'BD_RPC_ADD_DATA_CONN_ERROR' => Xphp::$_lang['WEB_ERROR_BD_RPC_ADD_DATA_CONN_ERROR'],
        'BD_RPC_DATA_CONN_NOT_FOUND_IN_HASH' => Xphp::$_lang['WEB_ERROR_BD_RPC_DATA_CONN_NOT_FOUND_IN_HASH'],

        'BD_FILE_OPEN_ERROR' => Xphp::$_lang['WEB_ERROR_BD_FILE_OPEN_ERROR'],
        'BD_FILE_CREATE_ERROR' => Xphp::$_lang['WEB_ERROR_BD_FILE_CREATE_ERROR'],
        'BD_FILE_WRITE_ERROR' => Xphp::$_lang['WEB_ERROR_BD_FILE_WRITE_ERROR'],
        'BD_FILE_READ_ERROR' => Xphp::$_lang['WEB_ERROR_BD_FILE_READ_ERROR'],
        'BD_FILE_LSEEK_ERROR' => Xphp::$_lang['WEB_ERROR_BD_FILE_LSEEK_ERROR'],
        'BD_FILE_ACCESS_DENIED' => Xphp::$_lang['WEB_ERROR_BD_FILE_ACCESS_DENIED'],
        'BD_FILE_STAT_ERROR' => Xphp::$_lang['WEB_ERROR_BD_FILE_STAT_ERROR'],
        'BD_FILE_DELETE_ERROR' => Xphp::$_lang['WEB_ERROR_BD_FILE_DELETE_ERROR'],
        'BD_FILE_ALREADY_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_BD_FILE_ALREADY_EXIST_ERROR'],
        'BD_FILE_NOT_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_BD_FILE_NOT_EXIST_ERROR'],
        'BD_FILE_RENAME_ERROR' => Xphp::$_lang['WEB_ERROR_BD_FILE_RENAME_ERROR'],

        'BD_AGENT_SPACE_STORAGE_IS_NOT_ENOUGH_ERROR' => Xphp::$_lang['WEB_ERROR_BD_AGENT_SPACE_STORAGE_IS_NOT_ENOUGH_ERROR'],
        'BD_AGENT_IS_OFFLINE_ERROR' => Xphp::$_lang['WEB_ERROR_BD_AGENT_IS_OFFLINE_ERROR'],
        'BD_AGENT_IS_NOT_AUTH_ERROR' => Xphp::$_lang['WEB_ERROR_BD_AGENT_IS_NOT_AUTH_ERROR'],

        'BD_TIMEPOINT_IS_NOT_FULL_ERROR' => Xphp::$_lang['WEB_ERROR_BD_TIMEPOINT_IS_NOT_FULL_ERROR'],
        'BD_TIMEPOINT_IS_DEPEND_BY_OTHERS_ERROR' => Xphp::$_lang['WEB_ERROR_BD_TIMEPOINT_IS_DEPEND_BY_OTHERS_ERROR'],
        'BD_TIMEPOINT_IS_BROKEN_ERROR' => Xphp::$_lang['WEB_ERROR_BD_TIMEPOINT_IS_BROKEN_ERROR'],
        'BD_COMPRESS_COMMON_ERROR' => Xphp::$_lang['WEB_ERROR_BD_COMPRESS_COMMON_ERROR'],
        'BD_COMPRESS_READ_EOF_ERROR' => Xphp::$_lang['WEB_ERROR_BD_COMPRESS_READ_EOF_ERROR'],
        'BD_COMPRESS_INIT_LIB_ERROR' => Xphp::$_lang['WEB_ERROR_BD_COMPRESS_INIT_LIB_ERROR'],
        'BD_COMPRESS_INVALID_DATA_ERROR' => Xphp::$_lang['WEB_ERROR_BD_COMPRESS_INVALID_DATA_ERROR'],
        'BD_COMPRESS_DO_COMPRESS_ERROR' => Xphp::$_lang['WEB_ERROR_BD_COMPRESS_DO_COMPRESS_ERROR'],
        'BD_COMPRESS_DO_UNCOMPRESS_ERROR' => Xphp::$_lang['WEB_ERROR_BD_COMPRESS_DO_UNCOMPRESS_ERROR'],

        'BD_VHD_ALREADY_OPEN_ERROR' => Xphp::$_lang['WEB_ERROR_BD_VHD_ALREADY_OPEN_ERROR'],
        'BD_VHD_NOT_OPEN_ERROR' => Xphp::$_lang['WEB_ERROR_BD_VHD_NOT_OPEN_ERROR'],
        'BD_VHD_NOT_SUPPORT_TYPE_ERROR' => Xphp::$_lang['WEB_ERROR_BD_VHD_NOT_SUPPORT_TYPE_ERROR'],
        'BD_VHD_INVALID_FILE_SIZE_ERROR' => Xphp::$_lang['WEB_ERROR_BD_VHD_INVALID_FILE_SIZE_ERROR'],
        'BD_VHD_FOOTER_INVALID_ERROR' => Xphp::$_lang['WEB_ERROR_BD_VHD_FOOTER_INVALID_ERROR'],
        'BD_VHD_SPARSE_HEADER_INVALID_ERROR' => Xphp::$_lang['WEB_ERROR_BD_VHD_SPARSE_HEADER_INVALID_ERROR'],
        'BD_VHD_BATMAP_HEADER_INVALID_ERROR' => Xphp::$_lang['WEB_ERROR_BD_VHD_BATMAP_HEADER_INVALID_ERROR'],
        'BD_VHD_BATMAP_NOT_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_BD_VHD_BATMAP_NOT_EXIST_ERROR'],
        'BD_VHD_BAT_INVALID_ERROR' => Xphp::$_lang['WEB_ERROR_BD_VHD_BAT_INVALID_ERROR'],
        'BD_VHD_FIND_CONTEXT_BY_PARENT_UUID_ERROR' => Xphp::$_lang['WEB_ERROR_BD_VHD_FIND_CONTEXT_BY_PARENT_UUID_ERROR'],

        'BD_PARSE_JSON_STRING_ERROR' => Xphp::$_lang['WEB_ERROR_BD_PARSE_JSON_STRING_ERROR'],
        'BD_TASK_RECOVERY_POSITION_SPACE_IS_NOT_ENOUGH_ERROR' => Xphp::$_lang['WEB_ERROR_BD_TASK_RECOVERY_POSITION_SPACE_IS_NOT_ENOUGH_ERROR'],
        'BD_POWER_LOSS_OR_PROGRAM_CRASH_ERROR' => Xphp::$_lang['WEB_ERROR_BD_POWER_LOSS_OR_PROGRAM_CRASH_ERROR'],

        'BD_SCAN_RAW_STORAGE_ERROR' => Xphp::$_lang['WEB_ERROR_BD_SCAN_RAW_STORAGE_ERROR'],
        'BD_FILESYSTEM_BUSY_ERROR' => Xphp::$_lang['WEB_ERROR_BD_FILESYSTEM_BUSY_ERROR'],
        'BD_MOUNT_FILESYSTEM_ERROR' => Xphp::$_lang['WEB_ERROR_BD_MOUNT_FILESYSTEM_ERROR'],
        'BD_UMOUNT_FILESYSTEM_ERROR' => Xphp::$_lang['WEB_ERROR_BD_UMOUNT_FILESYSTEM_ERROR'],
        'BD_UNKNOW_STORAGE_TYPE_ERROR' => Xphp::$_lang['WEB_ERROR_BD_UNKNOW_STORAGE_TYPE_ERROR'],
        'BD_STORAGE_IS_TOO_SMALL_ERROR' => Xphp::$_lang['WEB_ERROR_BD_STORAGE_IS_TOO_SMALL_ERROR'],
        'BD_CREATE_PARTITION_FOR_DISK_ERROR' => Xphp::$_lang['WEB_ERROR_BD_CREATE_PARTITION_FOR_DISK_ERROR'],
        'BD_CREATE_FILESYSTEM_ERROR' => Xphp::$_lang['WEB_ERROR_BD_CREATE_FILESYSTEM_ERROR'],
        'BD_STORAGE_IS_NOT_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_BD_STORAGE_IS_NOT_EXIST_ERROR'],
        'BD_STORAGE_NOT_UNIQUE_ERROR' => Xphp::$_lang['WEB_ERROR_BD_STORAGE_NOT_UNIQUE_ERROR'],
        'BD_STORAGE_NEW_PART_NOT_FOUND_ERROR' => Xphp::$_lang['WEB_ERROR_BD_STORAGE_NEW_PART_NOT_FOUND_ERROR'],
        'BD_PARSE_STORAGE_INFO_ERROR' => Xphp::$_lang['WEB_ERROR_BD_PARSE_STORAGE_INFO_ERROR'],
        'BD_STORAGE_SELF_EXPLAN_FILE_NOT_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_BD_STORAGE_SELF_EXPLAN_FILE_NOT_EXIST_ERROR'],
        'BD_STORAGE_PARSE_SELF_EXPLAN_FILE_ERROR' => Xphp::$_lang['WEB_ERROR_BD_STORAGE_PARSE_SELF_EXPLAN_FILE_ERROR'],
        'BD_STORAGE_SAVE_SELF_EXPLAN_FILE_ERROR' => Xphp::$_lang['WEB_ERROR_BD_STORAGE_SAVE_SELF_EXPLAN_FILE_ERROR'],
        'BD_STORAGE_CREATE_MOUNT_DIR_ERROR' => Xphp::$_lang['WEB_ERROR_BD_STORAGE_CREATE_MOUNT_DIR_ERROR'],
        'BD_STORAGE_PRIMARY_KEY_IS_EMPRY_ERROR' => Xphp::$_lang['WEB_ERROR_BD_STORAGE_PRIMARY_KEY_IS_EMPRY_ERROR'],
        'BD_STORAGE_IS_NOT_AVAILABLE_ERROR' => Xphp::$_lang['WEB_ERROR_BD_STORAGE_IS_NOT_AVAILABLE_ERROR'],
        'BD_STORAGE_IS_ALREADY_ADDED_ERROR' => Xphp::$_lang['WEB_ERROR_BD_STORAGE_IS_ALREADY_ADDED_ERROR'],
        'BD_STORAGE_GET_RAW_STORAGE_INFO_ERROR' => Xphp::$_lang['WEB_ERROR_BD_STORAGE_GET_RAW_STORAGE_INFO_ERROR'],
        'BD_STORAGE_GET_FC_WWN_ERROR' => Xphp::$_lang['WEB_ERROR_BD_STORAGE_GET_FC_WWN_ERROR'],
        'BD_STORAGE_SCAN_SCSI_DEVICE_ERROR' => Xphp::$_lang['WEB_ERROR_BD_STORAGE_SCAN_SCSI_DEVICE_ERROR'],
        'BD_STORAGE_NOT_FOUND_FC_DEVICE_ERROR' => Xphp::$_lang['WEB_ERROR_BD_STORAGE_NOT_FOUND_FC_DEVICE_ERROR'],
        'BD_STORAGE_SHARE_NAME_FORMAT_ERROR' => Xphp::$_lang['WEB_ERROR_BD_STORAGE_SHARE_NAME_FORMAT_ERROR'],
        'BD_STORAGE_HOST_UNREACH_ERROR' => Xphp::$_lang['WEB_ERROR_BD_STORAGE_HOST_UNREACH_ERROR'],
        'BD_STORAGE_NFS_STALE_HANDLE_ERROR' => Xphp::$_lang['WEB_ERROR_BD_STORAGE_NFS_STALE_HANDLE_ERROR'],
        'BD_STORAGE_NOT_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_BD_STORAGE_NOT_EXIST_ERROR'],
        'BD_STORAGE_NOT_BELONG_THIS_NODE_ERROR' => Xphp::$_lang['WEB_ERROR_BD_STORAGE_NOT_BELONG_THIS_NODE_ERROR'],
        'BD_STORAGE_HAVE_NO_SR_IN_NODE_ERROR' => Xphp::$_lang['WEB_ERROR_BD_STORAGE_HAVE_NO_SR_IN_NODE_ERROR'],
        'BD_STORAGE_IS_CHANGED_ERROR' => Xphp::$_lang['WEB_ERROR_BD_STORAGE_IS_CHANGED_ERROR'],
        'BD_STORAGE_SCAN_BACKUP_TIMEPOINT_ERROR' => Xphp::$_lang['WEB_ERROR_BD_STORAGE_SCAN_BACKUP_TIMEPOINT_ERROR'],
        'BD_STORAGE_NOT_AVAILABLE_ERROR' => Xphp::$_lang['WEB_ERROR_BD_STORAGE_NOT_AVAILABLE_ERROR'],
        'BD_STORAGE_ISCSI_INITIATOR_NAME_FILE_NOT_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_BD_STORAGE_ISCSI_INITIATOR_NAME_FILE_NOT_EXIST_ERROR'],
        'BD_STORAGE_GET_ISCSI_INITIATOR_NAME_ERROR' => Xphp::$_lang['WEB_ERROR_BD_STORAGE_GET_ISCSI_INITIATOR_NAME_ERROR'],
        'BD_STORAGE_SCAN_TARGET_IQN_ERROR' => Xphp::$_lang['WEB_ERROR_BD_STORAGE_SCAN_TARGET_IQN_ERROR'],
        'BD_STORAGE_LOGIN_ISCSI_TARGET_ERROR' => Xphp::$_lang['WEB_ERROR_BD_STORAGE_LOGIN_ISCSI_TARGET_ERROR'],
        'BD_STORAGE_GET_DEV_REAL_PATH_ERROR' => Xphp::$_lang['WEB_ERROR_BD_STORAGE_GET_DEV_REAL_PATH_ERROR'],
        'BD_STORAGE_GET_DEV_SOURCE_ERROR' => Xphp::$_lang['WEB_ERROR_BD_STORAGE_GET_DEV_SOURCE_ERROR'],
        'BD_STORAGE_MOUNT_POINT_IS_CHANGED_ERROR' => Xphp::$_lang['WEB_ERROR_BD_STORAGE_MOUNT_POINT_IS_CHANGED_ERROR'],
        'BD_NODE_IS_NOT_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_BD_NODE_IS_NOT_EXIST_ERROR'],
        'BD_CURL_GLOBAL_INIT_ERROR' => Xphp::$_lang['WEB_ERROR_BD_CURL_GLOBAL_INIT_ERROR'],
        'BD_CURL_INIT_ERROR' => Xphp::$_lang['WEB_ERROR_BD_CURL_INIT_ERROR'],
        'BD_CURL_PERFORM_HTTP_POST_ERROR' => Xphp::$_lang['WEB_ERROR_BD_CURL_PERFORM_HTTP_POST_ERROR'],
        'BD_CURL_PERFORM_HTTP_GET_ERROR' => Xphp::$_lang['WEB_ERROR_BD_CURL_PERFORM_HTTP_GET_ERROR'],

        'BD_LICENSE_EMPTY_ERROR' => Xphp::$_lang['WEB_ERROR_BD_LICENSE_EMPTY_ERROR'],
        'BD_VM_HOST_NOT_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_BD_VM_HOST_NOT_EXIST_ERROR'],
        'BD_SYSTEM_NOT_AUTH_ERROR' => Xphp::$_lang['WEB_ERROR_BD_SYSTEM_NOT_AUTH_ERROR'],
        'BD_LICENSE_EXHAUST_ERROR' => Xphp::$_lang['WEB_ERROR_BD_LICENSE_EXHAUST_ERROR'],
        'BD_MODULE_SERVER_NOT_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_BD_MODULE_SERVER_NOT_EXIST_ERROR'],
        'BD_TIMEPOINT_IS_USED_BY_RECOVERY_OR_INSTANT_RECOVERY_TASK_ERROR' => Xphp::$_lang['WEB_ERROR_BD_TIMEPOINT_IS_USED_BY_RECOVERY_OR_INSTANT_RECOVERY_TASK_ERROR'],
        'BD_SYSTEM_REBOOT_ABNORMAL' => Xphp::$_lang['WEB_ERROR_BD_SYSTEM_REBOOT_ABNORMAL'],

        'BD_DISK_GET_PED_DEVICE_ERROR' => Xphp::$_lang['WEB_ERROR_BD_DISK_GET_PED_DEVICE_ERROR'],
        'BD_DISK_GET_PED_DISK_ERROR' => Xphp::$_lang['WEB_ERROR_BD_DISK_GET_PED_DISK_ERROR'],

        'BD_LVM_GENERIC_ERROR' => Xphp::$_lang['WEB_ERROR_BD_LVM_GENERIC_ERROR'],
        'BD_LVM_INIT_ERROR' => Xphp::$_lang['WEB_ERROR_BD_LVM_INIT_ERROR'],
        'BD_LVM_RELOAD_ERROR' => Xphp::$_lang['WEB_ERROR_BD_LVM_RELOAD_ERROR'],
        'BD_LVM_SCAN_ERROR' => Xphp::$_lang['WEB_ERROR_BD_LVM_SCAN_ERROR'],
        'BD_LVM_VG_OPEN_ERROR' => Xphp::$_lang['WEB_ERROR_BD_LVM_VG_OPEN_ERROR'],
        'BD_LVM_PV_IS_NOT_USED_ERROR' => Xphp::$_lang['WEB_ERROR_BD_LVM_PV_IS_NOT_USED_ERROR'],

        'BD_GPT_LBA0_MBR_INVALID_ERROR' => Xphp::$_lang['WEB_ERROR_BD_GPT_LBA0_MBR_INVALID_ERROR'],
        'BD_GPT_LBA1_HEADER_INVALID_ERROR' => Xphp::$_lang['WEB_ERROR_BD_GPT_LBA1_HEADER_INVALID_ERROR'],
        'BD_GPT_NOT_EXIST_VSS_PARTITION_ERROR' => Xphp::$_lang['WEB_ERROR_BD_GPT_NOT_EXIST_VSS_PARTITION_ERROR'],

        'BD_BACKUP_NODE_IS_ABNORMAL_ERROR' => Xphp::$_lang['WEB_ERROR_BD_BACKUP_NODE_IS_ABNORMAL_ERROR'],


        //TODO 提取语言包
        'BD_LIBVIRT_ERROR' => Xphp::$_lang['WEB_ERROR_BD_LIBVIRT_ERROR'],
        'BD_XML_PARSE_ERROR' => Xphp::$_lang['WEB_ERROR_BD_XML_PARSE_ERROR'],
        'BD_NOT_SUPPORT_SUB_BACKUP_TYPE_ERROR' => Xphp::$_lang['WEB_ERROR_BD_NOT_SUPPORT_SUB_BACKUP_TYPE_ERROR'],
        'BD_NOT_SUPPORT_SUB_RECOVERY_TYPE_ERROR' => Xphp::$_lang['WEB_ERROR_BD_NOT_SUPPORT_SUB_RECOVERY_TYPE_ERROR'],
        'BD_RPC_CONNECTION_NOT_FOUND_ERROR' => Xphp::$_lang['WEB_ERROR_BD_RPC_CONNECTION_NOT_FOUND_ERROR'],
        'BD_RPC_CONNECTION_ALREADY_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_BD_RPC_CONNECTION_ALREADY_EXIST_ERROR'],
        'BD_BACKUP_TASK_LEVEL_DOWN_ERROR' => Xphp::$_lang['WEB_ERROR_BD_BACKUP_TASK_LEVEL_DOWN_ERROR'],
        'BD_TIMEPOINT_REACH_MAX_ERROR' => Xphp::$_lang['WEB_ERROR_BD_TIMEPOINT_REACH_MAX_ERROR'],
        'BD_PARAM_TOO_LONG_ERROR' => Xphp::$_lang['WEB_ERROR_BD_PARAM_TOO_LONG_ERROR'],
        'BD_CURL_ERROR' => Xphp::$_lang['WEB_ERROR_BD_CURL_ERROR'],
        'BD_CURL_UNSUPPORTED_PROTOCOL_ERROR' => Xphp::$_lang['WEB_ERROR_BD_CURL_UNSUPPORTED_PROTOCOL_ERROR'],
        'BD_CURL_FAILED_INIT_ERROR' => Xphp::$_lang['WEB_ERROR_BD_CURL_FAILED_INIT_ERROR'],
        'BD_CURL_URL_MALFORMAT_ERROR' => Xphp::$_lang['WEB_ERROR_BD_CURL_URL_MALFORMAT_ERROR'],
        'BD_CURL_NOT_BUILT_IN_ERROR' => Xphp::$_lang['WEB_ERROR_BD_CURL_NOT_BUILT_IN_ERROR'],
        'BD_CURL_COULDNT_RESOLVE_PROXY_ERROR' => Xphp::$_lang['WEB_ERROR_BD_CURL_COULDNT_RESOLVE_PROXY_ERROR'],
        'BD_CURL_COULDNT_RESOLVE_HOST_ERROR' => Xphp::$_lang['WEB_ERROR_BD_CURL_COULDNT_RESOLVE_HOST_ERROR'],
        'BD_CURL_COULDNT_CONNECT_ERROR' => Xphp::$_lang['WEB_ERROR_BD_CURL_COULDNT_CONNECT_ERROR'],
        'BD_CURL_FTP_WEIRD_SERVER_REPLY_ERROR' => Xphp::$_lang['WEB_ERROR_BD_CURL_FTP_WEIRD_SERVER_REPLY_ERROR'],
        'BD_CURL_REMOTE_ACCESS_DENIED_ERROR' => Xphp::$_lang['WEB_ERROR_BD_CURL_REMOTE_ACCESS_DENIED_ERROR'],
        'BD_CURL_FTP_ACCEPT_FAILED_ERROR' => Xphp::$_lang['WEB_ERROR_BD_CURL_FTP_ACCEPT_FAILED_ERROR'],
        'BD_CURL_FTP_WEIRD_PASS_REPLY_ERROR' => Xphp::$_lang['WEB_ERROR_BD_CURL_FTP_WEIRD_PASS_REPLY_ERROR'],
        'BD_CURL_FTP_ACCEPT_TIMEOUT_ERROR' => Xphp::$_lang['WEB_ERROR_BD_CURL_FTP_ACCEPT_TIMEOUT_ERROR'],
        'BD_CURL_FTP_WEIRD_PASV_REPLY_ERROR' => Xphp::$_lang['WEB_ERROR_BD_CURL_FTP_WEIRD_PASV_REPLY_ERROR'],
        'BD_CURL_FTP_WEIRD_227_FORMAT_ERROR' => Xphp::$_lang['WEB_ERROR_BD_CURL_FTP_WEIRD_227_FORMAT_ERROR'],
        'BD_CURL_FTP_CANT_GET_HOST_ERROR' => Xphp::$_lang['WEB_ERROR_BD_CURL_FTP_CANT_GET_HOST_ERROR'],
        'BD_CURL_HTTP2_ERROR' => Xphp::$_lang['WEB_ERROR_BD_CURL_HTTP2_ERROR'],
        'BD_CURL_FTP_COULDNT_SET_TYPE_ERROR' => Xphp::$_lang['WEB_ERROR_BD_CURL_FTP_COULDNT_SET_TYPE_ERROR'],
        'BD_CURL_PARTIAL_FILE_ERROR' => Xphp::$_lang['WEB_ERROR_BD_CURL_PARTIAL_FILE_ERROR'],
        'BD_CURL_FTP_COULDNT_RETR_FILE_ERROR' => Xphp::$_lang['WEB_ERROR_BD_CURL_FTP_COULDNT_RETR_FILE_ERROR'],
        'BD_CURL_QUOTE_ERROR' => Xphp::$_lang['WEB_ERROR_BD_CURL_QUOTE_ERROR'],
        'BD_CURL_HTTP_RETURNED_ERROR' => Xphp::$_lang['WEB_ERROR_BD_CURL_HTTP_RETURNED_ERROR'],
        'BD_CURL_WRITE_ERROR' => Xphp::$_lang['WEB_ERROR_BD_CURL_WRITE_ERROR'],
        'BD_CURL_UPLOAD_FAILED_ERROR' => Xphp::$_lang['WEB_ERROR_BD_CURL_UPLOAD_FAILED_ERROR'],
        'BD_CURL_READ_ERROR' => Xphp::$_lang['WEB_ERROR_BD_CURL_READ_ERROR'],
        'BD_CURL_OUT_OF_MEMORY_ERROR' => Xphp::$_lang['WEB_ERROR_BD_CURL_OUT_OF_MEMORY_ERROR'],
        'BD_CURL_OPERATION_TIMEDOUT_ERROR' => Xphp::$_lang['WEB_ERROR_BD_CURL_OPERATION_TIMEDOUT_ERROR'],
        'BD_CURL_FTP_PORT_FAILED_ERROR' => Xphp::$_lang['WEB_ERROR_BD_CURL_FTP_PORT_FAILED_ERROR'],
        'BD_CURL_FTP_COULDNT_USE_REST_ERROR' => Xphp::$_lang['WEB_ERROR_BD_CURL_FTP_COULDNT_USE_REST_ERROR'],
        'BD_CURL_RANGE_ERROR' => Xphp::$_lang['WEB_ERROR_BD_CURL_RANGE_ERROR'],
        'BD_CURL_HTTP_POST_ERROR' => Xphp::$_lang['WEB_ERROR_BD_CURL_HTTP_POST_ERROR'],
        'BD_CURL_SSL_CONNECT_ERROR' => Xphp::$_lang['WEB_ERROR_BD_CURL_SSL_CONNECT_ERROR'],
        'BD_CURL_BAD_DOWNLOAD_RESUME_ERROR' => Xphp::$_lang['WEB_ERROR_BD_CURL_BAD_DOWNLOAD_RESUME_ERROR'],
        'BD_CURL_FILE_COULDNT_READ_FILE_ERROR' => Xphp::$_lang['WEB_ERROR_BD_CURL_FILE_COULDNT_READ_FILE_ERROR'],
        'BD_CURL_LDAP_CANNOT_BIND_ERROR' => Xphp::$_lang['WEB_ERROR_BD_CURL_LDAP_CANNOT_BIND_ERROR'],
        'BD_CURL_LDAP_SEARCH_FAILED_ERROR' => Xphp::$_lang['WEB_ERROR_BD_CURL_LDAP_SEARCH_FAILED_ERROR'],
        'BD_CURL_FUNCTION_NOT_FOUND_ERROR' => Xphp::$_lang['WEB_ERROR_BD_CURL_FUNCTION_NOT_FOUND_ERROR'],
        'BD_CURL_ABORTED_BY_CALLBACK_ERROR' => Xphp::$_lang['WEB_ERROR_BD_CURL_ABORTED_BY_CALLBACK_ERROR'],
        'BD_CURL_BAD_FUNCTION_ARGUMENT_ERROR' => Xphp::$_lang['WEB_ERROR_BD_CURL_BAD_FUNCTION_ARGUMENT_ERROR'],
        'BD_CURL_INTERFACE_FAILED_ERROR' => Xphp::$_lang['WEB_ERROR_BD_CURL_INTERFACE_FAILED_ERROR'],
        'BD_CURL_TOO_MANY_REDIRECTS_ERROR' => Xphp::$_lang['WEB_ERROR_BD_CURL_TOO_MANY_REDIRECTS_ERROR'],
        'BD_CURL_UNKNOWN_OPTION_ERROR' => Xphp::$_lang['WEB_ERROR_BD_CURL_UNKNOWN_OPTION_ERROR'],
        'BD_CURL_TELNET_OPTION_SYNTAX_ERROR' => Xphp::$_lang['WEB_ERROR_BD_CURL_TELNET_OPTION_SYNTAX_ERROR'],
        'BD_CURL_PEER_FAILED_VERIFICATION_ERROR' => Xphp::$_lang['WEB_ERROR_BD_CURL_PEER_FAILED_VERIFICATION_ERROR'],
        'BD_CURL_GOT_NOTHING_ERROR' => Xphp::$_lang['WEB_ERROR_BD_CURL_GOT_NOTHING_ERROR'],
        'BD_CURL_SSL_ENGINE_NOTFOUND_ERROR' => Xphp::$_lang['WEB_ERROR_BD_CURL_SSL_ENGINE_NOTFOUND_ERROR'],
        'BD_CURL_SSL_ENGINE_SETFAILED_ERROR' => Xphp::$_lang['WEB_ERROR_BD_CURL_SSL_ENGINE_SETFAILED_ERROR'],
        'BD_CURL_SEND_ERROR' => Xphp::$_lang['WEB_ERROR_BD_CURL_SEND_ERROR'],
        'BD_CURL_RECV_ERROR' => Xphp::$_lang['WEB_ERROR_BD_CURL_RECV_ERROR'],
        'BD_CURL_SSL_CERTPROBLEM_ERROR' => Xphp::$_lang['WEB_ERROR_BD_CURL_SSL_CERTPROBLEM_ERROR'],
        'BD_CURL_SSL_CIPHER_ERROR' => Xphp::$_lang['WEB_ERROR_BD_CURL_SSL_CIPHER_ERROR'],
        'BD_CURL_SSL_CACERT_ERROR' => Xphp::$_lang['WEB_ERROR_BD_CURL_SSL_CACERT_ERROR'],
        'BD_CURL_BAD_CONTENT_ENCODING_ERROR' => Xphp::$_lang['WEB_ERROR_BD_CURL_BAD_CONTENT_ENCODING_ERROR'],
        'BD_CURL_LDAP_INVALID_URL_ERROR' => Xphp::$_lang['WEB_ERROR_BD_CURL_LDAP_INVALID_URL_ERROR'],
        'BD_CURL_FILESIZE_EXCEEDED_ERROR' => Xphp::$_lang['WEB_ERROR_BD_CURL_FILESIZE_EXCEEDED_ERROR'],
        'BD_CURL_USE_SSL_FAILED_ERROR' => Xphp::$_lang['WEB_ERROR_BD_CURL_USE_SSL_FAILED_ERROR'],
        'BD_CURL_SEND_FAIL_REWIND_ERROR' => Xphp::$_lang['WEB_ERROR_BD_CURL_SEND_FAIL_REWIND_ERROR'],
        'BD_CURL_SSL_ENGINE_INITFAILED_ERROR' => Xphp::$_lang['WEB_ERROR_BD_CURL_SSL_ENGINE_INITFAILED_ERROR'],
        'BD_CURL_LOGIN_DENIED_ERROR' => Xphp::$_lang['WEB_ERROR_BD_CURL_LOGIN_DENIED_ERROR'],
        'BD_CURL_TFTP_NOTFOUND_ERROR' => Xphp::$_lang['WEB_ERROR_BD_CURL_TFTP_NOTFOUND_ERROR'],
        'BD_CURL_TFTP_PERM_ERROR' => Xphp::$_lang['WEB_ERROR_BD_CURL_TFTP_PERM_ERROR'],
        'BD_CURL_REMOTE_DISK_FULL_ERROR' => Xphp::$_lang['WEB_ERROR_BD_CURL_REMOTE_DISK_FULL_ERROR'],
        'BD_CURL_TFTP_ILLEGAL_ERROR' => Xphp::$_lang['WEB_ERROR_BD_CURL_TFTP_ILLEGAL_ERROR'],
        'BD_CURL_TFTP_UNKNOWNID_ERROR' => Xphp::$_lang['WEB_ERROR_BD_CURL_TFTP_UNKNOWNID_ERROR'],
        'BD_CURL_REMOTE_FILE_EXISTS_ERROR' => Xphp::$_lang['WEB_ERROR_BD_CURL_REMOTE_FILE_EXISTS_ERROR'],
        'BD_CURL_TFTP_NOSUCHUSER_ERROR' => Xphp::$_lang['WEB_ERROR_BD_CURL_TFTP_NOSUCHUSER_ERROR'],
        'BD_CURL_CONV_FAILED_ERROR' => Xphp::$_lang['WEB_ERROR_BD_CURL_CONV_FAILED_ERROR'],
        'BD_CURL_CONV_REQD_ERROR' => Xphp::$_lang['WEB_ERROR_BD_CURL_CONV_REQD_ERROR'],
        'BD_CURL_SSL_CACERT_BADFILE_ERROR' => Xphp::$_lang['WEB_ERROR_BD_CURL_SSL_CACERT_BADFILE_ERROR'],
        'BD_CURL_REMOTE_FILE_NOT_FOUND_ERROR' => Xphp::$_lang['WEB_ERROR_BD_CURL_REMOTE_FILE_NOT_FOUND_ERROR'],
        'BD_CURL_SSH_ERROR' => Xphp::$_lang['WEB_ERROR_BD_CURL_SSH_ERROR'],
        'BD_CURL_SSL_SHUTDOWN_FAILED_ERROR' => Xphp::$_lang['WEB_ERROR_BD_CURL_SSL_SHUTDOWN_FAILED_ERROR'],
        'BD_CURL_AGAIN_ERROR' => Xphp::$_lang['WEB_ERROR_BD_CURL_AGAIN_ERROR'],
        'BD_CURL_SSL_CRL_BADFILE_ERROR' => Xphp::$_lang['WEB_ERROR_BD_CURL_SSL_CRL_BADFILE_ERROR'],
        'BD_CURL_SSL_ISSUER_ERROR' => Xphp::$_lang['WEB_ERROR_BD_CURL_SSL_ISSUER_ERROR'],
        'BD_CURL_FTP_PRET_FAILED_ERROR' => Xphp::$_lang['WEB_ERROR_BD_CURL_FTP_PRET_FAILED_ERROR'],
        'BD_CURL_RTSP_CSEQ_ERROR' => Xphp::$_lang['WEB_ERROR_BD_CURL_RTSP_CSEQ_ERROR'],
        'BD_CURL_RTSP_SESSION_ERROR' => Xphp::$_lang['WEB_ERROR_BD_CURL_RTSP_SESSION_ERROR'],
        'BD_CURL_FTP_BAD_FILE_LIST_ERROR' => Xphp::$_lang['WEB_ERROR_BD_CURL_FTP_BAD_FILE_LIST_ERROR'],
        'BD_CURL_CHUNK_FAILED_ERROR' => Xphp::$_lang['WEB_ERROR_BD_CURL_CHUNK_FAILED_ERROR'],
        'BD_CURL_NO_CONNECTION_AVAILABLE_ERROR' => Xphp::$_lang['WEB_ERROR_BD_CURL_NO_CONNECTION_AVAILABLE_ERROR'],
        'BD_CURL_SSL_PINNEDPUBKEYNOTMATCH_ERROR' => Xphp::$_lang['WEB_ERROR_BD_CURL_SSL_PINNEDPUBKEYNOTMATCH_ERROR'],
        'BD_CURL_SSL_INVALIDCERTSTATUS_ERROR' => Xphp::$_lang['WEB_ERROR_BD_CURL_SSL_INVALIDCERTSTATUS_ERROR'],
        'BD_CURL_HTTP2_STREAM_ERROR' => Xphp::$_lang['WEB_ERROR_BD_CURL_HTTP2_STREAM_ERROR'],


        'BD_XML_ATTRIBUTE_NOT_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_BD_XML_ATTRIBUTE_NOT_EXIST_ERROR'],
        'BD_XML_ELE_NOT_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_BD_XML_ELE_NOT_EXIST_ERROR'],

        'BD_HTTP_REQUEST_ERROR' => Xphp::$_lang['WEB_ERROR_BD_HTTP_REQUEST_ERROR'],
        'BD_HTTP_HEADER_EMPTY_ERROR' => Xphp::$_lang['WEB_ERROR_BD_HTTP_HEADER_EMPTY_ERROR'],

        // http 4xx error
        'BD_HTTP_HEADER_400_ERROR' => Xphp::$_lang['WEB_ERROR_BD_HTTP_HEADER_400_ERROR'],
        'BD_HTTP_HEADER_401_ERROR' => Xphp::$_lang['WEB_ERROR_BD_HTTP_HEADER_401_ERROR'],
        'BD_HTTP_HEADER_402_ERROR' => Xphp::$_lang['WEB_ERROR_BD_HTTP_HEADER_402_ERROR'],
        'BD_HTTP_HEADER_403_ERROR' => Xphp::$_lang['WEB_ERROR_BD_HTTP_HEADER_403_ERROR'],
        'BD_HTTP_HEADER_404_ERROR' => Xphp::$_lang['WEB_ERROR_BD_HTTP_HEADER_404_ERROR'],
        'BD_HTTP_HEADER_405_ERROR' => Xphp::$_lang['WEB_ERROR_BD_HTTP_HEADER_405_ERROR'],
        'BD_HTTP_HEADER_406_ERROR' => Xphp::$_lang['WEB_ERROR_BD_HTTP_HEADER_406_ERROR'],
        'BD_HTTP_HEADER_407_ERROR' => Xphp::$_lang['WEB_ERROR_BD_HTTP_HEADER_407_ERROR'],
        'BD_HTTP_HEADER_408_ERROR' => Xphp::$_lang['WEB_ERROR_BD_HTTP_HEADER_408_ERROR'],
        'BD_HTTP_HEADER_409_ERROR' => Xphp::$_lang['WEB_ERROR_BD_HTTP_HEADER_409_ERROR'],
        'BD_HTTP_HEADER_410_ERROR' => Xphp::$_lang['WEB_ERROR_BD_HTTP_HEADER_410_ERROR'],
        'BD_HTTP_HEADER_411_ERROR' => Xphp::$_lang['WEB_ERROR_BD_HTTP_HEADER_411_ERROR'],
        'BD_HTTP_HEADER_412_ERROR' => Xphp::$_lang['WEB_ERROR_BD_HTTP_HEADER_412_ERROR'],
        'BD_HTTP_HEADER_413_ERROR' => Xphp::$_lang['WEB_ERROR_BD_HTTP_HEADER_413_ERROR'],
        'BD_HTTP_HEADER_414_ERROR' => Xphp::$_lang['WEB_ERROR_BD_HTTP_HEADER_414_ERROR'],
        'BD_HTTP_HEADER_415_ERROR' => Xphp::$_lang['WEB_ERROR_BD_HTTP_HEADER_415_ERROR'],
        'BD_HTTP_HEADER_416_ERROR' => Xphp::$_lang['WEB_ERROR_BD_HTTP_HEADER_416_ERROR'],
        'BD_HTTP_HEADER_417_ERROR' => Xphp::$_lang['WEB_ERROR_BD_HTTP_HEADER_417_ERROR'],

        // http 5xx error
        'BD_HTTP_HEADER_500_ERROR' => Xphp::$_lang['WEB_ERROR_BD_HTTP_HEADER_500_ERROR'],
        'BD_HTTP_HEADER_501_ERROR' => Xphp::$_lang['WEB_ERROR_BD_HTTP_HEADER_501_ERROR'],
        'BD_HTTP_HEADER_502_ERROR' => Xphp::$_lang['WEB_ERROR_BD_HTTP_HEADER_502_ERROR'],
        'BD_HTTP_HEADER_503_ERROR' => Xphp::$_lang['WEB_ERROR_BD_HTTP_HEADER_503_ERROR'],
        'BD_HTTP_HEADER_504_ERROR' => Xphp::$_lang['WEB_ERROR_BD_HTTP_HEADER_504_ERROR'],
        'BD_HTTP_HEADER_505_ERROR' => Xphp::$_lang['WEB_ERROR_BD_HTTP_HEADER_505_ERROR'],
        'BD_USER_QUOTA_REACH_ERROR' => Xphp::$_lang['WEB_ERROR_BD_USER_QUOTA_REACH_ERROR'],

        //ceph error
        'BD_CEPH_INIT_ERROR' => Xphp::$_lang['WEB_ERROR_BD_CEPH_INIT_ERROR'],
        'BD_CEPH_CREATE_IO_CTX_ERROR' => Xphp::$_lang['WEB_ERROR_BD_CEPH_CREATE_IO_CTX_ERROR'],
        'BD_CEPH_CREATE_OBJ_SNAPSHOT_ERROR' => Xphp::$_lang['WEB_ERROR_BD_CEPH_CREATE_OBJ_SNAPSHOT_ERROR'],
        'BD_CEPH_DELETE_IO_CTX_ERROR' => Xphp::$_lang['WEB_ERROR_BD_CEPH_DELETE_IO_CTX_ERROR'],
        'BD_CEPH_OPEN_RBD_OBJ_ERROR' => Xphp::$_lang['WEB_ERROR_BD_CEPH_OPEN_RBD_OBJ_ERROR'],
        'BD_CEPH_SHUTDOWN_CONNECT_ERROR' => Xphp::$_lang['WEB_ERROR_BD_CEPH_SHUTDOWN_CONNECT_ERROR'],
        'BD_CEPH_CLOSE_RBD_OBJ_ERROR' => Xphp::$_lang['WEB_ERROR_BD_CEPH_CLOSE_RBD_OBJ_ERROR'],
        'BD_CEPH_GET_RBD_OBJ_SIZE_ERROR' => Xphp::$_lang['WEB_ERROR_BD_CEPH_GET_RBD_OBJ_SIZE_ERROR'],
        'BD_CEPH_READ_RBD_DATA_ERROR' => Xphp::$_lang['WEB_ERROR_BD_CEPH_READ_RBD_DATA_ERROR'],
        'BD_CEPH_RBD_DIFF_ITERATOR_ERROR' => Xphp::$_lang['WEB_ERROR_BD_CEPH_RBD_DIFF_ITERATOR_ERROR'],
        'BD_CEPH_WRITE_RBD_DATA_ERROR' => Xphp::$_lang['WEB_ERROR_BD_CEPH_WRITE_RBD_DATA_ERROR'],
        'BD_CEPH_DELETE_OBJ_SNAPSHOT_ERROR' => Xphp::$_lang['WEB_ERROR_BD_CEPH_DELETE_OBJ_SNAPSHOT_ERROR'],
        'BD_CEPH_CONTROLLER_ALREADY_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_BD_CEPH_CONTROLLER_ALREADY_EXIST_ERROR'],
        'BD_CEPH_CONTROLLER_NOT_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_BD_CEPH_CONTROLLER_NOT_EXIST_ERROR'],
        'BD_CEPH_CONTROLLER_NOT_FOUND_ERROR' => Xphp::$_lang['WEB_ERROR_BD_CEPH_CONTROLLER_NOT_FOUND_ERROR'],

        //----------------------------for disk valid data------------------------------------------------
        'BD_INVALID_DISK_SIZE_ERROR' => Xphp::$_lang['WEB_ERROR_BD_INVALID_DISK_SIZE_ERROR'],
        'BD_INVALID_MBR_ERROR' => Xphp::$_lang['WEB_ERROR_BD_INVALID_MBR_ERROR'],
        'BD_MULTI_FILE_RECORD_ERROR' => Xphp::$_lang['WEB_ERROR_BD_MULTI_FILE_RECORD_ERROR'],
        'BD_NO_NTFS_FILE_SYSTEM_PARTITION' => Xphp::$_lang['WEB_ERROR_BD_NO_NTFS_FILE_SYSTEM_PARTITION'],
        'BD_NOT_INDEX_BY_FILE_NAME_ERROR' => Xphp::$_lang['WEB_ERROR_BD_NOT_INDEX_BY_FILE_NAME_ERROR'],
        'BD_NONRESIDENT_90H_ATTRIBUTE_ERROR' => Xphp::$_lang['WEB_ERROR_BD_NONRESIDENT_90H_ATTRIBUTE_ERROR'],

        'BD_NBD_RECV_INVALID_IN_INIT_TLS_ERROR' => Xphp::$_lang['WEB_ERROR_BD_NBD_RECV_INVALID_IN_INIT_TLS_ERROR'],
        'BD_NBD_RECV_INVALID_IN_HANDSHAKE_ERROR' => Xphp::$_lang['WEB_ERROR_BD_NBD_RECV_INVALID_IN_HANDSHAKE_ERROR'],
        'BD_NBD_RECV_INVALID_REPLY_HEADER_ERROR' => Xphp::$_lang['WEB_ERROR_BD_NBD_RECV_INVALID_REPLY_HEADER_ERROR'],
        'BD_NBD_RECV_INVALID_HANDLE_ERROR' => Xphp::$_lang['WEB_ERROR_BD_NBD_RECV_INVALID_HANDLE_ERROR'],
        'BD_NBD_OPERATION_ERROR' => Xphp::$_lang['WEB_ERROR_BD_NBD_OPERATION_ERROR'],

        'BD_TRANSPORT_MODE_ERROR' => Xphp::$_lang['WEB_ERROR_BD_TRANSPORT_MODE_ERROR'],
        'BD_STORAGE_CONNECTOR_NOT_SUPPORT_OP_ERROR' => Xphp::$_lang['WEB_ERROR_BD_STORAGE_CONNECTOR_NOT_SUPPORT_OP_ERROR'],
        'BD_SNAPSHOT_NOT_SUPPORT_OPEN_FOR_WRITE_ERROR' => Xphp::$_lang['WEB_ERROR_BD_SNAPSHOT_NOT_SUPPORT_OPEN_FOR_WRITE_ERROR'],
        'BD_CEPH_GET_RBD_OBJ_SNAPSHOT_LIST_ERROR' => Xphp::$_lang['WEB_ERROR_BD_CEPH_GET_RBD_OBJ_SNAPSHOT_LIST_ERROR'],

        //2018.8.10 new error_log
        'BD_HTTP_RESULT_EMPTY' => Xphp::$_lang['WEB_ERROR_BD_HTTP_RESULT_EMPTY'],
        'BD_HTTP_RESPONSE_HEADER_INVALID_ERROR' => Xphp::$_lang['WEB_ERROR_BD_HTTP_RESPONSE_HEADER_INVALID_ERROR'],
        'BD_JSON_STRING_INVALID' => Xphp::$_lang['WEB_ERROR_BD_JSON_STRING_INVALID'],
        'BD_NOT_LOGIN_ERROR' => Xphp::$_lang['WEB_ERROR_BD_NOT_LOGIN_ERROR'],

        'BD_ERRNO_EPERM' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_EPERM'],
        'BD_ERRNO_ENOENT' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_ENOENT'],
        'BD_ERRNO_ESRCH' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_ESRCH'],
        'BD_ERRNO_EINTR' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_EINTR'],
        'BD_ERRNO_EIO' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_EIO'],
        'BD_ERRNO_ENXIO' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_ENXIO'],
        'BD_ERRNO_E2BIG' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_E2BIG'],
        'BD_ERRNO_ENOEXEC' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_ENOEXEC'],
        'BD_ERRNO_EBADF' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_EBADF'],
        'BD_ERRNO_ECHILD' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_ECHILD'],
        'BD_ERRNO_EAGAIN' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_EAGAIN'],
        'BD_ERRNO_ENOMEM' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_ENOMEM'],
        'BD_ERRNO_EACCES' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_EACCES'],
        'BD_ERRNO_EFAULT' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_EFAULT'],
        'BD_ERRNO_ENOTBLK' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_ENOTBLK'],
        'BD_ERRNO_EBUSY' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_EBUSY'],
        'BD_ERRNO_EEXIST' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_EEXIST'],
        'BD_ERRNO_EXDEV' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_EXDEV'],
        'BD_ERRNO_ENODEV' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_ENODEV'],
        'BD_ERRNO_ENOTDIR' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_ENOTDIR'],
        'BD_ERRNO_EISDIR' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_EISDIR'],
        'BD_ERRNO_EINVAL' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_EINVAL'],
        'BD_ERRNO_ENFILE' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_ENFILE'],
        'BD_ERRNO_EMFILE' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_EMFILE'],
        'BD_ERRNO_ENOTTY' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_ENOTTY'],
        'BD_ERRNO_ETXTBSY' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_ETXTBSY'],
        'BD_ERRNO_EFBIG' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_EFBIG'],
        'BD_ERRNO_ENOSPC' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_ENOSPC'],
        'BD_ERRNO_ESPIPE' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_ESPIPE'],
        'BD_ERRNO_EROFS' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_EROFS'],
        'BD_ERRNO_EMLINK' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_EMLINK'],
        'BD_ERRNO_EPIPE' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_EPIPE'],
        'BD_ERRNO_EDOM' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_EDOM'],
        'BD_ERRNO_ERANGE' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_ERANGE'],
        'BD_ERRNO_EDEADLK' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_EDEADLK'],
        'BD_ERRNO_ENAMETOOLONG' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_ENAMETOOLONG'],
        'BD_ERRNO_ENOLCK' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_ENOLCK'],
        'BD_ERRNO_ENOSYS' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_ENOSYS'],
        'BD_ERRNO_ENOTEMPTY' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_ENOTEMPTY'],
        'BD_ERRNO_ELOOP' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_ELOOP'],
        'BD_ERRNO_EWOULDBLOCK' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_EWOULDBLOCK'],
        'BD_ERRNO_ENOMSG' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_ENOMSG'],
        'BD_ERRNO_EIDRM' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_EIDRM'],
        'BD_ERRNO_ECHRNG' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_ECHRNG'],
        'BD_ERRNO_EL2NSYNC' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_EL2NSYNC'],
        'BD_ERRNO_EL3HLT' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_EL3HLT'],
        'BD_ERRNO_EL3RST' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_EL3RST'],
        'BD_ERRNO_ELNRNG' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_ELNRNG'],
        'BD_ERRNO_EUNATCH' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_EUNATCH'],
        'BD_ERRNO_ENOCSI' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_ENOCSI'],
        'BD_ERRNO_EL2HLT' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_EL2HLT'],
        'BD_ERRNO_EBADE' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_EBADE'],
        'BD_ERRNO_EBADR' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_EBADR'],
        'BD_ERRNO_EXFULL' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_EXFULL'],
        'BD_ERRNO_ENOANO' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_ENOANO'],
        'BD_ERRNO_EBADRQC' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_EBADRQC'],
        'BD_ERRNO_EBADSLT' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_EBADSLT'],
        'BD_ERRNO_EDEADLOCK' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_EDEADLOCK'],
        'BD_ERRNO_EBFONT' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_EBFONT'],
        'BD_ERRNO_ENOSTR' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_ENOSTR'],
        'BD_ERRNO_ENODATA' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_ENODATA'],
        'BD_ERRNO_ETIME' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_ETIME'],
        'BD_ERRNO_ENOSR' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_ENOSR'],
        'BD_ERRNO_ENONET' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_ENONET'],
        'BD_ERRNO_ENOPKG' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_ENOPKG'],
        'BD_ERRNO_EREMOTE' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_EREMOTE'],
        'BD_ERRNO_ENOLINK' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_ENOLINK'],
        'BD_ERRNO_EADV' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_EADV'],
        'BD_ERRNO_ESRMNT' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_ESRMNT'],
        'BD_ERRNO_ECOMM' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_ECOMM'],
        'BD_ERRNO_EPROTO' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_EPROTO'],
        'BD_ERRNO_EMULTIHOP' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_EMULTIHOP'],
        'BD_ERRNO_EDOTDOT' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_EDOTDOT'],
        'BD_ERRNO_EBADMSG' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_EBADMSG'],
        'BD_ERRNO_EOVERFLOW' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_EMULTIHOP'],
        'BD_ERRNO_ENOTUNIQ' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_EOVERFLOW'],
        'BD_ERRNO_EBADFD' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_EBADFD'],
        'BD_ERRNO_EREMCHG' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_EREMCHG'],
        'BD_ERRNO_ELIBACC' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_ELIBACC'],
        'BD_ERRNO_ELIBBAD' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_ELIBBAD'],
        'BD_ERRNO_ELIBSCN' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_ELIBSCN'],
        'BD_ERRNO_ELIBMAX' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_ELIBMAX'],
        'BD_ERRNO_ELIBEXEC' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_ELIBEXEC'],
        'BD_ERRNO_EILSEQ' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_EILSEQ'],
        'BD_ERRNO_ERESTART' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_ERESTART'],
        'BD_ERRNO_ESTRPIPE' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_ESTRPIPE'],
        'BD_ERRNO_EUSERS' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_EUSERS'],
        'BD_ERRNO_ENOTSOCK' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_ENOTSOCK'],
        'BD_ERRNO_EDESTADDRREQ' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_EDESTADDRREQ'],
        'BD_ERRNO_EMSGSIZE' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_EMSGSIZE'],
        'BD_ERRNO_EPROTOTYPE' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_EPROTOTYPE'],
        'BD_ERRNO_ENOPROTOOPT' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_ENOPROTOOPT'],
        'BD_ERRNO_EPROTONOSUPPORT' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_EPROTONOSUPPORT'],
        'BD_ERRNO_ESOCKTNOSUPPORT' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_ESOCKTNOSUPPORT'],
        'BD_ERRNO_EOPNOTSUPP' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_EOPNOTSUPP'],
        'BD_ERRNO_EPFNOSUPPORT' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_EPFNOSUPPORT'],
        'BD_ERRNO_EAFNOSUPPORT' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_EAFNOSUPPORT'],
        'BD_ERRNO_EADDRINUSE' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_EADDRINUSE'],
        'BD_ERRNO_EADDRNOTAVAIL' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_EADDRNOTAVAIL'],
        'BD_ERRNO_ENETDOWN' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_ENETDOWN'],
        'BD_ERRNO_ENETUNREACH' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_ENETUNREACH'],
        'BD_ERRNO_ENETRESET' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_ENETRESET'],
        'BD_ERRNO_ECONNABORTED' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_ECONNABORTED'],
        'BD_ERRNO_ECONNRESET' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_ECONNRESET'],
        'BD_ERRNO_ENOBUFS' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_ENOBUFS'],
        'BD_ERRNO_EISCONN' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_EISCONN'],
        'BD_ERRNO_ENOTCONN' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_ENOTCONN'],
        'BD_ERRNO_ESHUTDOWN' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_ESHUTDOWN'],
        'BD_ERRNO_ETOOMANYREFS' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_ETOOMANYREFS'],
        'BD_ERRNO_ETIMEDOUT' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_ETIMEDOUT'],
        'BD_ERRNO_ECONNREFUSED' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_ECONNREFUSED'],
        'BD_ERRNO_EHOSTDOWN' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_EHOSTDOWN'],
        'BD_ERRNO_EHOSTUNREACH' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_EHOSTUNREACH'],
        'BD_ERRNO_EALREADY' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_EALREADY'],
        'BD_ERRNO_EINPROGRESS' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_EINPROGRESS'],
        'BD_ERRNO_ESTALE' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_ESTALE'],
        'BD_ERRNO_EUCLEAN' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_EUCLEAN'],
        'BD_ERRNO_ENOTNAM' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_ENOTNAM'],
        'BD_ERRNO_ENAVAIL' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_ENAVAIL'],
        'BD_ERRNO_EISNAM' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_EISNAM'],
        'BD_ERRNO_EREMOTEIO' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_EREMOTEIO'],
        'BD_ERRNO_EDQUOT' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_EDQUOT'],
        'BD_ERRNO_ENOMEDIUM' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_ENOMEDIUM'],
        'BD_ERRNO_EMEDIUMTYPE' => Xphp::$_lang['WEB_ERROR_BD_ERRNO_EMEDIUMTYPE'],
        'BD_USERNAME_NOT_INCLUDE_DOMAIN_NAME_ERROR' => Xphp::$_lang['WEB_ERROR_BD_USERNAME_NOT_INCLUDE_DOMAIN_NAME_ERROR'],
        'BD_FILE_MD5_NOT_MATCH' => Xphp::$_lang['WEB_ERROR_BD_FILE_MD5_NOT_MATCH'],
        'BD_PATCH_FILE_IS_NOT_EXIST' => Xphp::$_lang['WEB_ERROR_BD_PATCH_FILE_IS_NOT_EXIST'],

        'BD_SQLITE_UNKNOWN_ERROR' => Xphp::$_lang['WEB_ERROR_BD_SQLITE_UNKNOWN_ERROR'],
        'BD_SQLITE_ERROR' => Xphp::$_lang['WEB_ERROR_BD_SQLITE_ERROR'],
        'BD_SQLITE_INTERNAL' => Xphp::$_lang['WEB_ERROR_BD_SQLITE_INTERNAL'],
        'BD_SQLITE_PERM' => Xphp::$_lang['WEB_ERROR_BD_SQLITE_PERM'],
        'BD_SQLITE_ABORT' => Xphp::$_lang['WEB_ERROR_BD_SQLITE_ABORT'],
        'BD_SQLITE_BUSY' => Xphp::$_lang['WEB_ERROR_BD_SQLITE_BUSY'],
        'BD_SQLITE_LOCKED' => Xphp::$_lang['WEB_ERROR_BD_SQLITE_LOCKED'],
        'BD_SQLITE_NOMEM' => Xphp::$_lang['WEB_ERROR_BD_SQLITE_NOMEM'],
        'BD_SQLITE_READONLY' => Xphp::$_lang['WEB_ERROR_BD_SQLITE_READONLY'],
        'BD_SQLITE_INTERRUPT' => Xphp::$_lang['WEB_ERROR_BD_SQLITE_INTERRUPT'],
        'BD_SQLITE_IOERR' => Xphp::$_lang['WEB_ERROR_BD_SQLITE_IOERR'],
        'BD_SQLITE_CORRUPT' => Xphp::$_lang['WEB_ERROR_BD_SQLITE_CORRUPT'],
        'BD_SQLITE_NOTFOUND' => Xphp::$_lang['WEB_ERROR_BD_SQLITE_NOTFOUND'],
        'BD_SQLITE_FULL' => Xphp::$_lang['WEB_ERROR_BD_SQLITE_FULL'],
        'BD_SQLITE_CANTOPEN' => Xphp::$_lang['WEB_ERROR_BD_SQLITE_CANTOPEN'],
        'BD_SQLITE_PROTOCOL' => Xphp::$_lang['WEB_ERROR_BD_SQLITE_PROTOCOL'],
        'BD_SQLITE_EMPTY' => Xphp::$_lang['WEB_ERROR_BD_SQLITE_EMPTY'],
        'BD_SQLITE_SCHEMA' => Xphp::$_lang['WEB_ERROR_BD_SQLITE_SCHEMA'],
        'BD_SQLITE_TOOBIG' => Xphp::$_lang['WEB_ERROR_BD_SQLITE_TOOBIG'],
        'BD_SQLITE_CONSTRAINT' => Xphp::$_lang['WEB_ERROR_BD_SQLITE_CONSTRAINT'],
        'BD_SQLITE_MISMATCH' => Xphp::$_lang['WEB_ERROR_BD_SQLITE_MISMATCH'],
        'BD_SQLITE_MISUSE' => Xphp::$_lang['WEB_ERROR_BD_SQLITE_MISUSE'],
        'BD_SQLITE_NOLFS' => Xphp::$_lang['WEB_ERROR_BD_SQLITE_NOLFS'],
        'BD_SQLITE_AUTH' => Xphp::$_lang['WEB_ERROR_BD_SQLITE_AUTH'],
        'BD_SQLITE_FORMAT' => Xphp::$_lang['WEB_ERROR_BD_SQLITE_FORMAT'],
        'BD_SQLITE_RANGE' => Xphp::$_lang['WEB_ERROR_BD_SQLITE_RANGE'],
        'BD_SQLITE_NOTADB' => Xphp::$_lang['WEB_ERROR_BD_SQLITE_NOTADB'],
        'BD_SQLITE_NOTICE' => Xphp::$_lang['WEB_ERROR_BD_SQLITE_NOTICE'],
        'BD_SQLITE_WARNING' => Xphp::$_lang['WEB_ERROR_BD_SQLITE_WARNING'],
        'BD_SQLITE_OBJECT_NOT_EXIST' => Xphp::$_lang['WEB_ERROR_BD_SQLITE_OBJECT_NOT_EXIST'],

        'BD_SOURCE_BITMAP_SIZE_NOT_COMPITBLE_TARGET_SIZE' => Xphp::$_lang['WEB_ERROR_BD_SOURCE_BITMAP_SIZE_NOT_COMPITBLE_TARGET_SIZE'],
        'BD_PATCH_ALREADY_INSTALLED_ERROR' => Xphp::$_lang['WEB_ERROR_BD_PATCH_ALREADY_INSTALLED_ERROR'],
        'BD_DOWNLOAD_ERROR' => Xphp::$_lang['WEB_ERROR_BD_DOWNLOAD_ERROR'],

        'BD_GUEST_UNSUPPORT_ERROR' => Xphp::$_lang['WEB_ERROR_BD_GUEST_UNSUPPORT_ERROR'],
        'BD_GUEST_FILESYSTEM_ROOT_NOT_FOUND_ERROR' => Xphp::$_lang['WEB_ERROR_BD_GUEST_FILESYSTEM_ROOT_NOT_FOUND_ERROR'],
        'BD_GUEST_VM_CANT_GET_OPERATING_SYSTEM_ERROR' => Xphp::$_lang['WEB_ERROR_BD_GUEST_VM_CANT_GET_OPERATING_SYSTEM_ERROR'],
        'BD_GUEST_VM_OPERATING_SYSTEM_UNKNOWN_ERROR' => Xphp::$_lang['WEB_ERROR_BD_GUEST_VM_OPERATING_SYSTEM_UNKNOWN_ERROR'],
        'BD_GUEST_WINDOWS_LETTER_MAP_NOT_FOUND_ERROR' => Xphp::$_lang['WEB_ERROR_BD_GUEST_WINDOWS_LETTER_MAP_NOT_FOUND_ERROR'],
        'BD_GUEST_WINDOWS_LETTER_MAP_NOT_PART_MISSING' => Xphp::$_lang['WEB_ERROR_BD_GUEST_WINDOWS_LETTER_MAP_NOT_PART_MISSING'],
        'BD_GUEST_FILESYSTEM_INFO_MISSING_ERROR' => Xphp::$_lang['WEB_ERROR_BD_GUEST_FILESYSTEM_INFO_MISSING_ERROR'],
        'BD_GUEST_WORK_THREAD_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_BD_GUEST_WORK_THREAD_EXIST_ERROR'],
        'BD_GUEST_WORK_THREAD_NOT_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_BD_GUEST_WORK_THREAD_NOT_EXIST_ERROR'],
        'BD_GUEST_HANDLER_NOT_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_BD_GUEST_HANDLER_NOT_EXIST_ERROR'],
        'BD_GUEST_HANDLER_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_BD_GUEST_HANDLER_EXIST_ERROR'],
        'BD_GUEST_HANDLER_BUSY_ERROR' => Xphp::$_lang['WEB_ERROR_GUEST_HANDLER_BUSY_ERROR'],
        'BD_GUEST_HANDLER_IS_DELETING_ERROR' => Xphp::$_lang['WEB_ERROR_BD_GUEST_HANDLER_BUSY_ERROR'],
        'BD_GUEST_FSTAB_MISSING_ERROR' => Xphp::$_lang['WEB_ERROR_BD_GUEST_FSTAB_MISSING_ERROR'],
        'BD_GUEST_MOUNTPOINT_INFO_MISSING_ERROR' => Xphp::$_lang['WEB_ERROR_BD_GUEST_MOUNTPOINT_INFO_MISSING_ERROR'],
        'BD_GUEST_INTERNAL_MOUNT_ERROR' => Xphp::$_lang['WEB_ERROR_BD_GUEST_INTERNAL_MOUNT_ERROR'],
        'BD_GUEST_WINDOWS_DRIVER_LETTER_NOT_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_BD_GUEST_WINDOWS_DRIVER_LETTER_NOT_EXIST_ERROR'],
        'BD_GUEST_DRIVER_IS_BUSY_ERROR' => Xphp::$_lang['WEB_ERROR_BD_GUEST_DRIVER_IS_BUSY_ERROR'],
        'BD_GUEST_LIST_DIR_ERROR' => Xphp::$_lang['WEB_ERROR_BD_GUEST_LIST_DIR_ERROR'],
        'BD_GUEST_LIST_RESULT_NOT_MATCH' => Xphp::$_lang['WEB_ERROR_BD_GUEST_LIST_RESULT_NOT_MATCH'],
        'BD_GUEST_GET_ITEM_STAT_ERROR' => Xphp::$_lang['WEB_ERROR_BD_GUEST_GET_ITEM_STAT_ERROR'],
        'BD_GUEST_ROOT_IS_NOT_MOUNT_ERROR' => Xphp::$_lang['WEB_ERROR_BD_GUEST_ROOT_IS_NOT_MOUNT_ERROR'],

        'BD_TASK_IS_STOPPING_ERROR' => Xphp::$_lang['WEB_ERROR_BD_TASK_IS_STOPPING_ERROR'],
        'BD_TASK_IS_STOPPED_ERROR' => Xphp::$_lang['WEB_ERROR_BD_TASK_IS_STOPPED_ERROR'],

        // new add guest error code
        'BD_GUEST_PATH_INVALID_ERROR' => Xphp::$_lang['WEB_ERROR_BD_GUEST_PATH_INVALID_ERROR'],
        'BD_TASK_BE_PAUSED_ERROR' => Xphp::$_lang['WEB_ERROR_BD_TASK_BE_PAUSED_ERROR'],
        'BD_GUEST_READ_BEYOND_THE_FILE_ERROR' => Xphp::$_lang['WEB_ERROR_BD_GUEST_READ_BEYOND_THE_FILE_ERROR'],

        // file handle pool
        'BD_FILE_NOT_OPEN_ERROR' => Xphp::$_lang['WEB_ERROR_BD_FILE_NOT_OPEN_ERROR'],
        'BD_FILE_HANDLE_POOL_REACH_MAX_NUM_ERROR' => Xphp::$_lang['WEB_ERROR_BD_FILE_HANDLE_POOL_REACH_MAX_NUM_ERROR'],

        // task work thread
        'BD_TASK_WORK_THREAD_OTHER_THREAD_ERROR' => Xphp::$_lang['WEB_ERROR_BD_TASK_WORK_THREAD_OTHER_THREAD_ERROR'],

        //for netapp
        'BD_NETAPP_OPEN_CONNECTION_ERROR' => Xphp::$_lang['WEB_ERROR_BD_NETAPP_OPEN_CONNECTION_ERROR'],
        'BD_NETAPP_SET_SERVER_TYPE_ERROR' => Xphp::$_lang['WEB_ERROR_BD_NETAPP_SET_SERVER_TYPE_ERROR'],
        'BD_NETAPP_SET_TRANSPORT_TYPE_ERROR' => Xphp::$_lang['WEB_ERROR_BD_NETAPP_SET_TRANSPORT_TYPE_ERROR'],
        'BD_NETAPP_SET_PORT_ERROR' => Xphp::$_lang['WEB_ERROR_BD_NETAPP_SET_PORT_ERROR'],
        'BD_NETAPP_SET_USERNAME_AND_PASSWORD_ERROR' => Xphp::$_lang['WEB_ERROR_BD_NETAPP_SET_USERNAME_AND_PASSWORD_ERROR'],
        'BD_NETAPP_GET_API_VERSION_ERROR' => Xphp::$_lang['WEB_ERROR_BD_NETAPP_GET_API_VERSION_ERROR'],
        'BD_NETAPP_GET_LUN_ITER_ERROR' => Xphp::$_lang['WEB_ERROR_BD_NETAPP_GET_LUN_ITER_ERROR'],
        'BD_NETAPP_GET_VOLUME_LUN_PATH_ERROR' => Xphp::$_lang['WEB_ERROR_BD_NETAPP_GET_VOLUME_LUN_PATH_ERROR'],
        'BD_NETAPP_LUN_MAP_ERROR' => Xphp::$_lang['WEB_ERROR_BD_NETAPP_LUN_MAP_ERROR'],
        'BD_NETAPP_LUN_UNMAP_ERROR' => Xphp::$_lang['WEB_ERROR_BD_NETAPP_LUN_UNMAP_ERROR'],
        'BD_NETAPP_GET_IGROUP_ITER_ERROR' => Xphp::$_lang['WEB_ERROR_BD_NETAPP_GET_IGROUP_ITER_ERROR'],
        'BD_NETAPP_GET_LUN_MAP_INFO_ERROR' => Xphp::$_lang['WEB_ERROR_BD_NETAPP_GET_LUN_MAP_INFO_ERROR'],
        'BD_NETAPP_SET_VSERVER_ERROR' => Xphp::$_lang['WEB_ERROR_BD_NETAPP_SET_VSERVER_ERROR'],

        // multiple thread
        'BD_TASK_INFO_CHANGE_ERROR' => Xphp::$_lang['WEB_ERROR_BD_TASK_INFO_CHANGE_ERROR'],

        //for progress
        'BD_PROGRESS_IS_NOT_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_BD_PROGRESS_IS_NOT_EXIST_ERROR'],
        'BD_PROGRESS_IS_ALREADY_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_BD_PROGRESS_IS_ALREADY_EXIST_ERROR'],
        'BD_PROGRESS_START_PROGRESS_ERROR' => Xphp::$_lang['WEB_ERROR_BD_PROGRESS_START_PROGRESS_ERROR'],
        'BD_PROGRESS_HAS_NO_UNUSED_PORT_ERROR' => Xphp::$_lang['WEB_ERROR_BD_PROGRESS_HAS_NO_UNUSED_PORT_ERROR'],
        'BD_PROGRESS_GET_ALL_USED_PORTS_ERROR' => Xphp::$_lang['WEB_ERROR_BD_PROGRESS_HAS_NO_UNUSED_PORT_ERROR'],
        'BD_DO_SYSTEM_CMD_ERROR' => Xphp::$_lang['WEB_ERROR_BD_DO_SYSTEM_CMD_ERROR'],

        'BD_APPLIANCE_IS_NOT_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_BD_APPLIANCE_IS_NOT_EXIST_ERROR'],

        /* new version grain recovery */
        'BD_GUEST_DEVICE_NOT_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_BD_GUEST_DEVICE_NOT_EXIST_ERROR'],

        //for libssh
        'BD_LIBSSH2_GENERIC_ERROR' => Xphp::$_lang['WEB_ERROR_BD_LIBSSH2_GENERIC_ERROR'],
        'BD_LIBSSH2_SESSION_INIT_ERROR' => Xphp::$_lang['WEB_ERROR_BD_LIBSSH2_SESSION_INIT_ERROR'],


        'BD_LIBSSH2_SOCKET_NONE_ERROR' => Xphp::$_lang['WEB_ERROR_BD_LIBSSH2_SOCKET_NONE_ERROR'],
        'BD_LIBSSH2_BANNER_RECV_ERROR' => Xphp::$_lang['WEB_ERROR_BD_LIBSSH2_BANNER_RECV_ERROR'],
        'BD_LIBSSH2_BANNER_SEND_ERROR' => Xphp::$_lang['WEB_ERROR_BD_LIBSSH2_BANNER_SEND_ERROR'],
        'BD_LIBSSH2_INVALID_MAC_ERROR' => Xphp::$_lang['WEB_ERROR_BD_LIBSSH2_INVALID_MAC_ERROR'],
        'BD_LIBSSH2_KEX_FAILURE_ERROR' => Xphp::$_lang['WEB_ERROR_BD_LIBSSH2_KEX_FAILURE_ERROR'],
        'BD_LIBSSH2_ALLOC_ERROR' => Xphp::$_lang['WEB_ERROR_BD_LIBSSH2_ALLOC_ERROR'],
        'BD_LIBSSH2_SOCKET_SEND_ERROR' => Xphp::$_lang['WEB_ERROR_BD_LIBSSH2_SOCKET_SEND_ERROR'],
        'BD_LIBSSH2_KEY_EXCHANGE_FAILURE_ERROR' => Xphp::$_lang['WEB_ERROR_BD_LIBSSH2_KEY_EXCHANGE_FAILURE_ERROR'],
        'BD_LIBSSH2_TIMEOUT_ERROR' => Xphp::$_lang['WEB_ERROR_BD_LIBSSH2_TIMEOUT_ERROR'],
        'BD_LIBSSH2_HOSTKEY_INIT_ERROR' => Xphp::$_lang['WEB_ERROR_BD_LIBSSH2_HOSTKEY_INIT_ERROR'],
        'BD_LIBSSH2_HOSTKEY_SIGN_ERROR' => Xphp::$_lang['WEB_ERROR_BD_LIBSSH2_HOSTKEY_SIGN_ERROR'],
        'BD_LIBSSH2_DECRYPT_ERROR' => Xphp::$_lang['WEB_ERROR_BD_LIBSSH2_DECRYPT_ERROR'],
        'BD_LIBSSH2_SOCKET_DISCONNECT_ERROR' => Xphp::$_lang['WEB_ERROR_BD_LIBSSH2_SOCKET_DISCONNECT_ERROR'],
        'BD_LIBSSH2_PROTO_ERROR' => Xphp::$_lang['WEB_ERROR_BD_LIBSSH2_PROTO_ERROR'],
        'BD_LIBSSH2_PASSWORD_EXPIRED_ERROR' => Xphp::$_lang['WEB_ERROR_BD_LIBSSH2_PASSWORD_EXPIRED_ERROR'],
        'BD_LIBSSH2_FILE_ERROR' => Xphp::$_lang['WEB_ERROR_BD_LIBSSH2_FILE_ERROR'],
        'BD_LIBSSH2_METHOD_NONE_ERROR' => Xphp::$_lang['WEB_ERROR_BD_LIBSSH2_METHOD_NONE_ERROR'],
        'BD_LIBSSH2_AUTHENTICATION_FAILED_ERROR' => Xphp::$_lang['WEB_ERROR_BD_LIBSSH2_AUTHENTICATION_FAILED_ERROR'],
        'BD_LIBSSH2_PUBLICKEY_UNRECOGNIZED_ERROR' => Xphp::$_lang['WEB_ERROR_BD_LIBSSH2_PUBLICKEY_UNRECOGNIZED_ERROR'],
        'BD_LIBSSH2_PUBLICKEY_UNVERIFIED_ERROR' => Xphp::$_lang['WEB_ERROR_BD_LIBSSH2_PUBLICKEY_UNVERIFIED_ERROR'],
        'BD_LIBSSH2_CHANNEL_OUTOFORDER_ERROR' => Xphp::$_lang['WEB_ERROR_BD_LIBSSH2_CHANNEL_OUTOFORDER_ERROR'],
        'BD_LIBSSH2_CHANNEL_FAILURE_ERROR' => Xphp::$_lang['WEB_ERROR_BD_LIBSSH2_CHANNEL_FAILURE_ERROR'],
        'BD_LIBSSH2_CHANNEL_REQUEST_DENIED_ERROR' => Xphp::$_lang['WEB_ERROR_BD_LIBSSH2_CHANNEL_REQUEST_DENIED_ERROR'],
        'BD_LIBSSH2_CHANNEL_UNKNOWN_ERROR' => Xphp::$_lang['WEB_ERROR_BD_LIBSSH2_CHANNEL_UNKNOWN_ERROR'],
        'BD_LIBSSH2_CHANNEL_WINDOW_EXCEEDED_ERROR' => Xphp::$_lang['WEB_ERROR_BD_LIBSSH2_CHANNEL_WINDOW_EXCEEDED_ERROR'],
        'BD_LIBSSH2_CHANNEL_PACKET_EXCEEDED_ERROR' => Xphp::$_lang['WEB_ERROR_BD_LIBSSH2_CHANNEL_PACKET_EXCEEDED_ERROR'],
        'BD_LIBSSH2_CHANNEL_CLOSED_ERROR' => Xphp::$_lang['WEB_ERROR_BD_LIBSSH2_CHANNEL_CLOSED_ERROR'],
        'BD_LIBSSH2_CHANNEL_EOF_SENT_ERROR' => Xphp::$_lang['WEB_ERROR_BD_LIBSSH2_CHANNEL_EOF_SENT_ERROR'],
        'BD_LIBSSH2_SCP_PROTOCOL_ERROR' => Xphp::$_lang['WEB_ERROR_BD_LIBSSH2_SCP_PROTOCOL_ERROR'],
        'BD_LIBSSH2_ZLIB_ERROR' => Xphp::$_lang['WEB_ERROR_BD_LIBSSH2_ZLIB_ERROR'],
        'BD_LIBSSH2_SOCKET_TIMEOUT_ERROR' => Xphp::$_lang['WEB_ERROR_BD_LIBSSH2_SOCKET_TIMEOUT_ERROR'],
        'BD_LIBSSH2_SFTP_PROTOCOL_ERROR' => Xphp::$_lang['WEB_ERROR_BD_LIBSSH2_SFTP_PROTOCOL_ERROR'],
        'BD_LIBSSH2_REQUEST_DENIED_ERROR' => Xphp::$_lang['WEB_ERROR_BD_LIBSSH2_REQUEST_DENIED_ERROR'],
        'BD_LIBSSH2_METHOD_NOT_SUPPORTED_ERROR' => Xphp::$_lang['WEB_ERROR_BD_LIBSSH2_METHOD_NOT_SUPPORTED_ERROR'],
        'BD_LIBSSH2_INVAL_ERROR' => Xphp::$_lang['WEB_ERROR_BD_LIBSSH2_INVAL_ERROR'],
        'BD_LIBSSH2_INVALID_POLL_TYPE_ERROR' => Xphp::$_lang['WEB_ERROR_BD_LIBSSH2_INVALID_POLL_TYPE_ERROR'],
        'BD_LIBSSH2_PUBLICKEY_PROTOCOL_ERROR' => Xphp::$_lang['WEB_ERROR_BD_LIBSSH2_PUBLICKEY_PROTOCOL_ERROR'],
        'BD_LIBSSH2_EAGAIN_ERROR' => Xphp::$_lang['WEB_ERROR_BD_LIBSSH2_EAGAIN_ERROR'],
        'BD_LIBSSH2_BUFFER_TOO_SMALL_ERROR' => Xphp::$_lang['WEB_ERROR_BD_LIBSSH2_BUFFER_TOO_SMALL_ERROR'],
        'BD_LIBSSH2_BAD_USE_ERROR' => Xphp::$_lang['WEB_ERROR_BD_LIBSSH2_BAD_USE_ERROR'],
        'BD_LIBSSH2_COMPRESS_ERROR' => Xphp::$_lang['WEB_ERROR_BD_LIBSSH2_COMPRESS_ERROR'],
        'BD_LIBSSH2_OUT_OF_BOUNDARY_ERROR' => Xphp::$_lang['WEB_ERROR_BD_LIBSSH2_OUT_OF_BOUNDARY_ERROR'],
        'BD_LIBSSH2_AGENT_PROTOCOL_ERROR' => Xphp::$_lang['WEB_ERROR_BD_LIBSSH2_AGENT_PROTOCOL_ERROR'],
        'BD_LIBSSH2_SOCKET_RECV_ERROR' => Xphp::$_lang['WEB_ERROR_BD_LIBSSH2_SOCKET_RECV_ERROR'],
        'BD_LIBSSH2_ENCRYPT_ERROR' => Xphp::$_lang['WEB_ERROR_BD_LIBSSH2_ENCRYPT_ERROR'],
        'BD_LIBSSH2_BAD_SOCKET_ERROR' => Xphp::$_lang['WEB_ERROR_BD_LIBSSH2_BAD_SOCKET_ERROR'],
        'BD_LIBSSH2_KNOWN_HOSTS_ERROR' => Xphp::$_lang['WEB_ERROR_BD_LIBSSH2_KNOWN_HOSTS_ERROR'],

        'BD_JSON_MEMBER_MISSING_ERROR' => Xphp::$_lang['WEB_ERROR_BD_JSON_MEMBER_MISSING_ERROR'],
        'BD_PROMISE_LOGIN_ERROR' => Xphp::$_lang['WEB_ERROR_BD_PROMISE_LOGIN_ERROR'],
        'BD_CHECK_MOUNTPOINT_TIMEOUT_ERROR' => Xphp::$_lang['WEB_ERROR_BD_CHECK_MOUNTPOINT_TIMEOUT_ERROR'],


        ///// 6.0 new error code /////
        'BD_SINGLE_DISK_NOT_SUPPORT_OP_ERROR' => Xphp::$_lang['WEB_ERROR_BD_SINGLE_DISK_NOT_SUPPORT_OP_ERROR'],
        'BD_REMOTE_FILE_ALREADY_OPEN_ERROR' => Xphp::$_lang['WEB_ERROR_BD_REMOTE_FILE_ALREADY_OPEN_ERROR'],
        'BD_REMOTE_FILE_NOT_OPEN_ERROR' => Xphp::$_lang['WEB_ERROR_BD_REMOTE_FILE_NOT_OPEN_ERROR'],
        'BD_REMOTE_FILE_HANDLE_INVALID_ERROR' => Xphp::$_lang['WEB_ERROR_BD_REMOTE_FILE_HANDLE_INVALID_ERROR'],
        'BD_LICENSE_CURRENT_VM_IS_MORE_THAN_LIC_FILE' => Xphp::$_lang['WEB_ERROR_BD_LICENSE_CURRENT_VM_IS_MORE_THAN_LIC_FILE'],
        'BD_STORAGE_IS_USING_BY_TASK_ERROR' => Xphp::$_lang['WEB_ERROR_BD_STORAGE_IS_USING_BY_TASK_ERROR'],
        'BD_BACKUP_NODE_IS_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_BD_BACKUP_NODE_IS_EXIST_ERROR'],

        ////// curl download/upload error code //////////
        'BD_CURL_TRANSFER_BUFFER_IS_NOT_ENOUGH_ERROR' => Xphp::$_lang['WEB_ERROR_BD_CURL_TRANSFER_BUFFER_IS_NOT_ENOUGH_ERROR'],
        'BD_IP_SEGMENT_FORMAT_IS_INVALID_ERROR' => Xphp::$_lang['WEB_ERROR_BD_IP_SEGMENT_FORMAT_IS_INVALID_ERROR'],
        'BD_INC_AND_DIFF_TIMEPOINT_MIX_IN_ONE_CHAINS' => Xphp::$_lang['WEB_ERROR_BD_INC_AND_DIFF_TIMEPOINT_MIX_IN_ONE_CHAINS'],

        'BD_GFS_ALREADY_EXIST_MARKED_FULL_BACKUP_TIMEPOINT_AT_THE_SAME_WEEK' => Xphp::$_lang['WEB_ERROR_BD_GFS_ALREADY_EXIST_MARKED_FULL_BACKUP_TIMEPOINT_AT_THE_SAME_WEEK'],
        'BD_GFS_ALREADY_EXIST_MARKED_FULL_BACKUP_TIMEPOINT_AT_THE_SAME_MONTH' => Xphp::$_lang['WEB_ERROR_BD_GFS_ALREADY_EXIST_MARKED_FULL_BACKUP_TIMEPOINT_AT_THE_SAME_MONTH'],
        'BD_GFS_ALREADY_EXIST_MARKED_FULL_BACKUP_TIMEPOINT_AT_THE_SAME_YEAR' => Xphp::$_lang['WEB_ERROR_BD_GFS_ALREADY_EXIST_MARKED_FULL_BACKUP_TIMEPOINT_AT_THE_SAME_YEAR'],

        'BD_VERSION_ERROR' => Xphp::$_lang['WEB_ERROR_BD_VERSION_ERROR'],

        'BD_GET_IPSAN_FILE_LOCK_TIMEOUT' => Xphp::$_lang['WEB_ERROR_BD_GET_IPSAN_FILE_LOCK_TIMEOUT'],
        'BD_UNLOCK_IPSAN_FILE_LOCK_ERROR' => Xphp::$_lang['WEB_ERROR_BD_UNLOCK_IPSAN_FILE_LOCK_ERROR'],

        'BD_ISCSI_DISCOVERY_TARGET_ERROR' => Xphp::$_lang['WEB_ERROR_BD_ISCSI_DISCOVERY_TARGET_ERROR'],                    //iscsi扫描存储目标失败
        'BD_ISCSI_LOGIN_TARGET_ERROR' => Xphp::$_lang['WEB_ERROR_BD_ISCSI_LOGIN_TARGET_ERROR'],                         //iscsi登录存储目标失败
        'BD_ISCSI_LOGOUT_TARGET_ERROR' => Xphp::$_lang['WEB_ERROR_BD_ISCSI_LOGOUT_TARGET_ERROR'],                          //iscsi注销存储目标失败
        'BD_ISCSI_MOUNT_LUN_ERROR' => Xphp::$_lang['WEB_ERROR_BD_ISCSI_MOUNT_LUN_ERROR'],                               //iscsi挂载存储单元失败
        'BD_ISCSI_UNMOUNT_LUN_ERROR' => Xphp::$_lang['WEB_ERROR_BD_ISCSI_UNMOUNT_LUN_ERROR'],                         //iscsi解挂存储单元失败
        'BD_ISCSI_FIND_LUN_ERROR' => Xphp::$_lang['WEB_ERROR_BD_ISCSI_FIND_LUN_ERROR'],                                    //iscsi查找存储单元失败
        'BD_ISCSI_CREATE_TARGET_ERROR' => Xphp::$_lang['WEB_ERROR_BD_ISCSI_CREATE_TARGET_ERROR'],                           //iscsi创建存储目标失败
        'BD_ISCSI_DELETE_TARGET_ERROR' => Xphp::$_lang['WEB_ERROR_BD_ISCSI_DELETE_TARGET_ERROR'],                            //iscsi删除存储目标失败
        'BD_ISCSI_TARGET_ADD_LUN_ERROR' => Xphp::$_lang['WEB_ERROR_BD_ISCSI_TARGET_ADD_LUN_ERROR'],                        //iscsi向存储目标添加存储单元失败
        'BD_ISCSI_TARGET_BIND_INITIATOR_IP_ERROR' => Xphp::$_lang['WEB_ERROR_BD_ISCSI_TARGET_BIND_INITIATOR_IP_ERROR'],         //iscsi存储目标绑定客户端IP失败
        'BD_ISCSI_TARGET_UNBIND_INITIATOR_IP_ERROR' => Xphp::$_lang['WEB_ERROR_BD_ISCSI_TARGET_UNBIND_INITIATOR_IP_ERROR'],    //iscsi存储目标解绑客户端IP失败

        'BD_AGENT_APP_NOT_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_BD_AGENT_APP_NOT_EXIST_ERROR'],   	//agent app not exist
        'BD_AGENT_DISK_NOT_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_BD_AGENT_DISK_NOT_EXIST_ERROR'],   //agent disk not exist
        'BD_AGENT_VOL_NOT_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_BD_AGENT_VOL_NOT_EXIST_ERROR'],   //agent vol not exist
        'BD_STORAGE_ENCRYPT_CHNAGED_ERROR' => Xphp::$_lang['WEB_ERROR_BD_STORAGE_ENCRYPT_CHNAGED_ERROR'],  //storage encrypt changed error
        'BD_NODE_NETWORK_NOT_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_BD_NODE_NETWORK_NOT_EXIST_ERROR'],  //node network not exist
        'BD_NOT_MASTER_NODE_ERROR' => Xphp::$_lang['WEB_ERROR_BD_NOT_MASTER_NODE_ERROR'],				//is not master node, used for backup copy add remote system
        'BD_TIMEPOINT_NOT_COPY_OR_ARCHIVE_ERROR' => Xphp::$_lang['WEB_ERROR_BD_TIMEPOINT_NOT_COPY_OR_ARCHIVE_ERROR'],	//timepoint is not copied or archived by backup copy task or archive task
        'BD_DANGEROUS_COMMAND_ERROR' => Xphp::$_lang['WEB_ERROR_BD_DANGEROUS_COMMAND_ERROR'],    //dangerous command
        'BD_AGENT_ALREADY_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_BD_AGENT_ALREADY_EXIST_ERROR'],    //agent already exist error
        'BD_AGENT_CUR_LSN_SMALLER_THAN_BACKUPSET_END_LSN_ERROR' => Xphp::$_lang['WEB_ERROR_BD_AGENT_CUR_LSN_SMALLER_THAN_BACKUPSET_END_LSN_ERROR'], //the current lsn is smaller than the backupset lsn
        'BD_STORAGE_USE_MODE_NOT_MATCH_ERROR' => Xphp::$_lang['WEB_ERROR_BD_STORAGE_USE_MODE_NOT_MATCH_ERROR'], 		//storage use mode not match error
        'BD_AGENT_APP_IN_USE_BY_TASK_ERROR' => Xphp::$_lang['WEB_ERROR_BD_AGENT_APP_IN_USE_BY_TASK_ERROR'],   //agent app in use by task
        'BD_DYNAMIC_PARTITION_EXISTS' => Xphp::$_lang['WEB_ERROR_BD_DYNAMIC_PARTITION_EXISTS'],   //dynamic partition exists

        //XFS valid data error code
        'BD_INVALID_FS_TYPE' => Xphp::$_lang['WEB_ERROR_BD_INVALID_FS_TYPE'],     				//invalid fs type
        'BD_NTFS_INVALID_RECORD_HEAD_ERROR' => Xphp::$_lang['WEB_ERROR_BD_NTFS_INVALID_RECORD_HEAD_ERROR'],  	//invalid ntfs record head
        'BD_XFS_SB_MAGIC_ERROR' => Xphp::$_lang['WEB_ERROR_BD_XFS_SB_MAGIC_ERROR'],    			//get sb magic error
        'BD_XFS_AGF_MAGIC_ERROR' => Xphp::$_lang['WEB_ERROR_BD_XFS_AGF_MAGIC_ERROR'],     			//get agf magic error
        'BD_XFS_AGFL_MAGIC_ERROR' => Xphp::$_lang['WEB_ERROR_BD_XFS_AGFL_MAGIC_ERROR'],    			//get agfl magic error
        'BD_XFS_MAKE_FS_IS_IN_PROGRESSING' => Xphp::$_lang['WEB_ERROR_BD_XFS_MAKE_FS_IS_IN_PROGRESSING'],  	//make fs in proressing error
        'BD_XFS_ALLOC_SPACE_TOO_SMALL' => Xphp::$_lang['WEB_ERROR_BD_XFS_ALLOC_SPACE_TOO_SMALL'],   		//alloc space too small error
        'BD_XFS_UNSUPPORTED_VERSION' => Xphp::$_lang['WEB_ERROR_BD_XFS_UNSUPPORTED_VERSION'],


        'BD_VOL_CBT_CONTROL_DEVICE_OPEN_ERROR' => Xphp::$_lang['WEB_ERROR_BD_VOL_CBT_CONTROL_DEVICE_OPEN_ERROR'],
        'BD_VOL_CBT_DEVICE_NOT_MONITORING' => Xphp::$_lang['WEB_ERROR_BD_VOL_CBT_DEVICE_NOT_MONITORING'],
        'BD_VOL_CBT_CHECK_PARAM_INVALID' => Xphp::$_lang['WEB_ERROR_BD_VOL_CBT_CHECK_PARAM_INVALID'],
        'BD_VOL_CBT_OPEN_BITMAP_FILE_ERROR' => Xphp::$_lang['WEB_ERROR_BD_VOL_CBT_OPEN_BITMAP_FILE_ERROR'],
        'BD_VOL_CBT_WRITE_BITMAP_FILE_ERROR' => Xphp::$_lang['WEB_ERROR_BD_VOL_CBT_WRITE_BITMAP_FILE_ERROR'],
        'BD_REG_KEY_NOT_FOUND' => Xphp::$_lang['WEB_ERROR_BD_REG_KEY_NOT_FOUND'],
        'BD_REG_SPACE_NOT_ENOUGH' => Xphp::$_lang['WEB_ERROR_BD_REG_SPACE_NOT_ENOUGH'],
        'BD_USER_NAME_AND_PASSWD_NOT_MATCH' => Xphp::$_lang['WEB_ERROR_BD_USER_NAME_AND_PASSWD_NOT_MATCH'],
        'BD_S3_CLIENT_MISSING_ACCESS_KEY_ID' => Xphp::$_lang['WEB_ERROR_BD_S3_CLIENT_MISSING_ACCESS_KEY_ID'],
        'BD_S3_CLIENT_MISSING_ACCESS_SECRET_KEY' => Xphp::$_lang['WEB_ERROR_BD_S3_CLIENT_MISSING_ACCESS_SECRET_KEY'],
        'BD_S3_CLIENT_UNSURPORTED_PLATFORM' => Xphp::$_lang['WEB_ERROR_BD_S3_CLIENT_UNSURPORTED_PLATFORM'],
        'BD_S3_CLIENT_NULL_AGENT' => Xphp::$_lang['WEB_ERROR_BD_S3_CLIENT_NULL_AGENT'],
        'BD_S3_PARAM_MISSING_OBJECT_KEY' => Xphp::$_lang['WEB_ERROR_BD_S3_PARAM_MISSING_OBJECT_KEY'],
        'BD_S3_PARAM_MISSING_BUCKET_NAME' => Xphp::$_lang['WEB_ERROR_BD_S3_PARAM_MISSING_BUCKET_NAME'],
        'BD_S3_PARAM_MISSING_UPLOAD_ID' => Xphp::$_lang['WEB_ERROR_BD_S3_PARAM_MISSING_UPLOAD_ID'],
        'BD_S3_PARAM_INVALID_PART_NUMBER' => Xphp::$_lang['WEB_ERROR_BD_S3_PARAM_INVALID_PART_NUMBER'],
        'BD_S3_PARAM_INVALID_BUFFER_SIZE' => Xphp::$_lang['WEB_ERROR_BD_S3_PARAM_INVALID_BUFFER_SIZE'],
        'BD_S3_PARAM_NULL_POINTER' => Xphp::$_lang['WEB_ERROR_BD_S3_PARAM_NULL_POINTER'],
        'BD_S3_UTILS_GET_GMT_TIME_ERROR' => Xphp::$_lang['WEB_ERROR_BD_S3_UTILS_GET_GMT_TIME_ERROR'],
        'BD_S3_UTILS_AUTH_INVALID_CANONICAL_STRING' => Xphp::$_lang['WEB_ERROR_BD_S3_UTILS_AUTH_INVALID_CANONICAL_STRING'],
        'BD_S3_UNKNOWN_ACTION' => Xphp::$_lang['WEB_ERROR_BD_S3_UNKNOWN_ACTION'],
        'BD_S3_CURL_OPTSET_ERROR' => Xphp::$_lang['WEB_ERROR_BD_S3_CURL_OPTSET_ERROR'],
        'BD_S3_CURL_GLOBAL_INIT_ERROR' => Xphp::$_lang['WEB_ERROR_BD_S3_CURL_GLOBAL_INIT_ERROR'],
        'BD_S3_CURL_GLOBAL_INIT_ERROR' => Xphp::$_lang['WEB_ERROR_BD_S3_CURL_GLOBAL_INIT_ERROR'],
        'BD_S3_CURL_GLOBAL_DESTROY_ERROR' => Xphp::$_lang['WEB_ERROR_BD_S3_CURL_GLOBAL_DESTROY_ERROR'],
        'BD_S3_CURL_INIT_ERROR' => Xphp::$_lang['WEB_ERROR_BD_S3_CURL_INIT_ERROR'],
        'BD_S3_CURL_DESTROY_ERROR' => Xphp::$_lang['WEB_ERROR_BD_S3_CURL_DESTROY_ERROR'],
        'BD_S3_CURL_PERFORM_OUT_OF_MEMORY' => Xphp::$_lang['WEB_ERROR_BD_S3_CURL_PERFORM_OUT_OF_MEMORY'],
        'BD_S3_CURL_PERFORM_COULDNT_RESOLVE_PROXY' => Xphp::$_lang['WEB_ERROR_BD_S3_CURL_PERFORM_COULDNT_RESOLVE_PROXY'],
        'BD_S3_CURL_PERFORM_COULDNT_RESOLVE_HOST' => Xphp::$_lang['WEB_ERROR_BD_S3_CURL_PERFORM_COULDNT_RESOLVE_HOST'],
        'BD_S3_CURL_PERFORM_COULDNT_CONNECT' => Xphp::$_lang['WEB_ERROR_BD_S3_CURL_PERFORM_COULDNT_CONNECT'],
        'BD_S3_CURL_PERFORM_WRITE_ERROR' => Xphp::$_lang['WEB_ERROR_BD_S3_CURL_PERFORM_WRITE_ERROR'],
        'BD_S3_CURL_PERFORM_SSL_CACERT' => Xphp::$_lang['WEB_ERROR_BD_S3_CURL_PERFORM_SSL_CACERT'],
        'BD_S3_CURL_PERFORM_OPERATION_TIMEDOUT' => Xphp::$_lang['WEB_ERROR_BD_S3_CURL_PERFORM_OPERATION_TIMEDOUT'],
        'BD_S3_CURL_PERFORM_INTERNAL_ERROR' => Xphp::$_lang['WEB_ERROR_BD_S3_CURL_PERFORM_INTERNAL_ERROR'],
        'BD_S3_CURL_PERFORM_UNKNOWN_ERROR' => Xphp::$_lang['WEB_ERROR_BD_S3_CURL_PERFORM_UNKNOWN_ERROR'],
        'BD_S3_RESPONSE_INCOMPLETE_SIGNATURE' => Xphp::$_lang['WEB_ERROR_BD_S3_RESPONSE_INCOMPLETE_SIGNATURE'],
        'BD_S3_RESPONSE_INTERNAL_FAILURE' => Xphp::$_lang['WEB_ERROR_BD_S3_RESPONSE_INTERNAL_FAILURE'],
        'BD_S3_RESPONSE_INVALID_ACTION' => Xphp::$_lang['WEB_ERROR_BD_S3_RESPONSE_INVALID_ACTION'],
        'BD_S3_RESPONSE_INVALID_CLIENT_TOKEN_ID' => Xphp::$_lang['WEB_ERROR_BD_S3_RESPONSE_INVALID_CLIENT_TOKEN_ID'],
        'BD_S3_RESPONSE_INVALID_PARAMETER_COMBINATION' => Xphp::$_lang['WEB_ERROR_BD_S3_RESPONSE_INVALID_PARAMETER_COMBINATION'],
        'BD_S3_RESPONSE_INVALID_QUERY_PARAMETER' => Xphp::$_lang['WEB_ERROR_BD_S3_RESPONSE_INVALID_QUERY_PARAMETER'],
        'BD_S3_RESPONSE_INVALID_PARAMETER_VALUE' => Xphp::$_lang['WEB_ERROR_BD_S3_RESPONSE_INVALID_PARAMETER_VALUE'],
        'BD_S3_RESPONSE_MISSING_ACTION' => Xphp::$_lang['WEB_ERROR_BD_S3_RESPONSE_MISSING_ACTION'],
        'BD_S3_RESPONSE_MISSING_AUTHENTICATION_TOKEN' => Xphp::$_lang['WEB_ERROR_BD_S3_RESPONSE_MISSING_AUTHENTICATION_TOKEN'],
        'BD_S3_RESPONSE_MISSING_PARAMETER' => Xphp::$_lang['WEB_ERROR_BD_S3_RESPONSE_MISSING_PARAMETER'],
        'BD_S3_RESPONSE_OPT_IN_REQUIRED' => Xphp::$_lang['WEB_ERROR_BD_S3_RESPONSE_OPT_IN_REQUIRED'],
        'BD_S3_RESPONSE_REQUEST_EXPIRED' => Xphp::$_lang['WEB_ERROR_BD_S3_RESPONSE_REQUEST_EXPIRED'],
        'BD_S3_RESPONSE_SERVICE_UNAVAILABLE' => Xphp::$_lang['WEB_ERROR_BD_S3_RESPONSE_SERVICE_UNAVAILABLE'],
        'BD_S3_RESPONSE_THROTTLING' => Xphp::$_lang['WEB_ERROR_BD_S3_RESPONSE_THROTTLING'],
        'BD_S3_RESPONSE_VALIDATION' => Xphp::$_lang['WEB_ERROR_BD_S3_RESPONSE_VALIDATION'],
        'BD_S3_RESPONSE_ACCESS_DENIED' => Xphp::$_lang['WEB_ERROR_BD_S3_RESPONSE_ACCESS_DENIED'],
        'BD_S3_RESPONSE_RESOURCE_NOT_FOUND' => Xphp::$_lang['WEB_ERROR_BD_S3_RESPONSE_RESOURCE_NOT_FOUND'],
        'BD_S3_RESPONSE_UNRECOGNIZED_CLIENT' => Xphp::$_lang['WEB_ERROR_BD_S3_RESPONSE_UNRECOGNIZED_CLIENT'],
        'BD_S3_RESPONSE_MALFORMED_QUERY_STRING' => Xphp::$_lang['WEB_ERROR_BD_S3_RESPONSE_MALFORMED_QUERY_STRING'],
        'BD_S3_RESPONSE_SLOW_DOWN' => Xphp::$_lang['WEB_ERROR_BD_S3_RESPONSE_SLOW_DOWN'],
        'BD_S3_RESPONSE_REQUEST_TIME_TOO_SKEWED' => Xphp::$_lang['WEB_ERROR_BD_S3_RESPONSE_REQUEST_TIME_TOO_SKEWED'],
        'BD_S3_RESPONSE_INVALID_SIGNATURE' => Xphp::$_lang['WEB_ERROR_BD_S3_RESPONSE_INVALID_SIGNATURE'],
        'BD_S3_RESPONSE_SIGNATURE_DOES_NOT_MATCH' => Xphp::$_lang['WEB_ERROR_BD_S3_RESPONSE_SIGNATURE_DOES_NOT_MATCH'],
        'BD_S3_RESPONSE_INVALID_ACCESS_KEY_ID' => Xphp::$_lang['WEB_ERROR_BD_S3_RESPONSE_INVALID_ACCESS_KEY_ID'],
        'BD_S3_RESPONSE_REQUEST_TIMEOUT' => Xphp::$_lang['WEB_ERROR_BD_S3_RESPONSE_REQUEST_TIMEOUT'],
        'BD_S3_RESPONSE_NETWORK_CONNECTION' => Xphp::$_lang['WEB_ERROR_BD_S3_RESPONSE_NETWORK_CONNECTION'],
        'BD_S3_RESPONSE_UNKNOWN' => Xphp::$_lang['WEB_ERROR_BD_S3_RESPONSE_UNKNOWN'],
        'BD_S3_RESPONSE_CLIENT_SIGNING_FAILURE' => Xphp::$_lang['WEB_ERROR_BD_S3_RESPONSE_CLIENT_SIGNING_FAILURE'],
        'BD_S3_RESPONSE_USER_CANCELLED' => Xphp::$_lang['WEB_ERROR_BD_S3_RESPONSE_USER_CANCELLED'],
        'BD_S3_RESPONSE_ENDPOINT_RESOLUTION_FAILURE' => Xphp::$_lang['WEB_ERROR_BD_S3_RESPONSE_ENDPOINT_RESOLUTION_FAILURE'],
        'BD_S3_RESPONSE_SERVICE_EXTENSION_START_RANGE' => Xphp::$_lang['WEB_ERROR_BD_S3_RESPONSE_SERVICE_EXTENSION_START_RANGE'],
        'BD_S3_RESPONSE_OK' => Xphp::$_lang['WEB_ERROR_BD_S3_RESPONSE_OK'],
        'BD_S3_UNKNOWN_ERROR' => Xphp::$_lang['WEB_ERROR_BD_S3_UNKNOWN_ERROR'],
        'BD_TASK_BLOCK_SIZE_ZERO_ERROR' => Xphp::$_lang['WEB_ERROR_BD_TASK_BLOCK_SIZE_ZERO_ERROR'],
        'BD_UNSUPPORTED_FILESYSTEM_TYPE' => Xphp::$_lang['WEB_ERROR_BD_UNSUPPORTED_FILESYSTEM_TYPE'],
        //huaweiCBR
        'BD_HUAWEI_CBR_GET_REGION_ERROR' => Xphp::$_lang['WEB_ERROR_BD_HUAWEI_CBR_GET_REGION_ERROR'],
        'BD_HUAWEI_CBR_GET_PROJECT_ERROR' => Xphp::$_lang['WEB_ERROR_BD_HUAWEI_CBR_GET_PROJECT_ERROR'],
        'BD_HUAWEI_CBR_GET_VAULT_ERROR' => Xphp::$_lang['WEB_ERROR_BD_HUAWEI_CBR_GET_VAULT_ERROR'],
        'BD_HUAWEI_CBR_GET_BACKUP_ERROR' => Xphp::$_lang['WEB_ERROR_BD_HUAWEI_CBR_GET_BACKUP_ERROR'],
        'BD_HUAWEI_CBR_SYNC_TIMEPOINT_TO_CLOUD_ERROR' => Xphp::$_lang['WEB_ERROR_BD_HUAWEI_CBR_SYNC_TIMEPOINT_TO_CLOUD_ERROR'],
        'BD_HUAWEI_CBR_GET_BACKUP_METADATA_ERROR' => Xphp::$_lang['WEB_ERROR_BD_HUAWEI_CBR_GET_BACKUP_METADATA_ERROR'],
        'BD_HUAWEI_CBR_SYNC_GRAIN_INVAILD_ERROR' => Xphp::$_lang['WEB_ERROR_BD_HUAWEI_CBR_SYNC_GRAIN_INVAILD_ERROR'],
        'BD_HUAWEI_CBR_ADD_BUCKET_AUTH_ERROR' => Xphp::$_lang['WEB_ERROR_BD_HUAWEI_CBR_ADD_BUCKET_AUTH_ERROR'],
        'BD_HUAWEI_CBR_INIT_TRAFFIC_REPORT_ERROR' => Xphp::$_lang['WEB_ERROR_BD_HUAWEI_CBR_INIT_TRAFFIC_REPORT_ERROR'],
        'BD_HUAWEI_CBR_DO_TRAFFIC_REPORT_ERROR' => Xphp::$_lang['WEB_ERROR_BD_HUAWEI_CBR_DO_TRAFFIC_REPORT_ERROR'],
        'BD_HUAWEI_CBR_GET_AK_SK_ERROR' => Xphp::$_lang['WEB_ERROR_BD_HUAWEI_CBR_GET_AK_SK_ERROR'],
        'BD_HUAWEI_CBR_TASK_TIMEOUT_ERROR' => Xphp::$_lang['WEB_ERROR_BD_HUAWEI_CBR_TASK_TIMEOUT_ERROR'],
        'BD_HUAWEI_CBR_GET_TASK_ERROR' => Xphp::$_lang['WEB_ERROR_BD_HUAWEI_CBR_GET_TASK_ERROR'],
        'BD_HUAWEI_CBR_GET_RESOURCE_ERROR' => Xphp::$_lang['WEB_ERROR_BD_HUAWEI_CBR_GET_RESOURCE_ERROR'],
        'BD_HUAWEI_CBR_BACKUP_POINT_SYNCHRONIZED_ALREADY_ERROR' => Xphp::$_lang['WEB_ERROR_BD_HUAWEI_CBR_BACKUP_POINT_SYNCHRONIZED_ALREADY_ERROR'],


        'BD_SHM_READ_ERROR' => Xphp::$_lang['WEB_ERROR_BD_SHM_READ_ERROR'],
        'BD_SHM_UNMAP_ERROR' => Xphp::$_lang['WEB_ERROR_BD_SHM_UNMAP_ERROR'],
        'BD_SHM_DES_ERROR' => Xphp::$_lang['WEB_ERROR_BD_SHM_DES_ERROR'],
        'BD_SHM_INIT_ERROR' => Xphp::$_lang['WEB_ERROR_BD_SHM_INIT_ERROR'],
        'BD_SHM_MAP_ERROR' => Xphp::$_lang['WEB_ERROR_BD_SHM_MAP_ERROR'],

        'BD_NAMEDSEM_POST_ERROR' => Xphp::$_lang['WEB_ERROR_BD_NAMEDSEM_POST_ERROR'],
        'BD_NAMEDSEM_CLOSE_ERROR' => Xphp::$_lang['WEB_ERROR_BD_NAMEDSEM_CLOSE_ERROR'],
        'BD_NAMEDSEM_UNLINK_ERROR' => Xphp::$_lang['WEB_ERROR_BD_NAMEDSEM_UNLINK_ERROR'],
        'BD_NAMEDSEM_TIMEWAIT_ERROR' => Xphp::$_lang['WEB_ERROR_BD_NAMEDSEM_TIMEWAIT_ERROR'],
        'BD_NAMEDSEM_OPEN_ERROR' => Xphp::$_lang['WEB_ERROR_BD_NAMEDSEM_OPEN_ERROR'],
        'BD_COMPRESS_BUFFER_ERROR' => Xphp::$_lang['WEB_ERROR_BD_COMPRESS_BUFFER_ERROR'],
        'BD_COMPRESS_ALLOCATE_MEMORY_ERROR' => Xphp::$_lang['WEB_ERROR_BD_COMPRESS_ALLOCATE_MEMORY_ERROR'],

        'BD_OBS_FS_PASSWORD_FILE_BUILD_ERROR' => Xphp::$_lang['WEB_ERROR_BD_OBS_FS_PASSWORD_FILE_BUILD_ERROR'],
        'BD_OBS_FS_PASSWORD_FILE_ADD_RIGHTS_ERROR' => Xphp::$_lang['WEB_ERROR_BD_OBS_FS_PASSWORD_FILE_ADD_RIGHTS_ERROR'],
        'BD_OBS_FS_MOUNT_ERROR' => Xphp::$_lang['WEB_ERROR_BD_OBS_FS_MOUNT_ERROR'],
        'BD_STORAGE_COMPRESS_CHNAGED_ERROR' => Xphp::$_lang['WEB_ERROR_BD_STORAGE_COMPRESS_CHNAGED_ERROR'],
        'BD_STORAGE_NOT_USE_SPECIAL_PATH' => Xphp::$_lang['WEB_ERROR_BD_STORAGE_NOT_USE_SPECIAL_PATH'],
        'BD_SYSTEM_CACHE_NOT_CONFIG' => Xphp::$_lang['WEB_ERROR_BD_SYSTEM_CACHE_NOT_CONFIG'],
        'BD_SYSTEM_CACHE_CONFIG_SPACE_IS_FULL' => Xphp::$_lang['WEB_ERROR_BD_SYSTEM_CACHE_CONFIG_SPACE_IS_FULL'],
        'BD_SYSTEM_CACHE_NO_AVAILABLE_CONFIG_SPACE' => Xphp::$_lang['WEB_ERROR_BD_SYSTEM_CACHE_NO_AVAILABLE_CONFIG_SPACE'],
        'BD_SYSTEM_CACHE_NO_AVAILABLE_SWITCHING_SPACE' => Xphp::$_lang['WEB_ERROR_BD_SYSTEM_CACHE_NO_AVAILABLE_SWITCHING_SPACE'],
        'BD_TIMEPOINT_UNSUPPORT_DO_MERGE_ERROR' => Xphp::$_lang['WEB_ERROR_BD_TIMEPOINT_UNSUPPORT_DO_MERGE_ERROR'],
        'BD_USER_LEVEL_WITHOUT_PERMISSION' => Xphp::$_lang['WEB_ERROR_BD_USER_LEVEL_WITHOUT_PERMISSION'],
        'BD_STORAGE_TAPE_NOT_FOUND_AVAILABLE_DRIVE' => Xphp::$_lang['WEB_ERROR_BD_STORAGE_TAPE_NOT_FOUND_AVAILABLE_DRIVE'],

        'BD_STORAGE_LOGIN_ERROR' => Xphp::$_lang['WEB_ERROR_BD_STORAGE_LOGIN_ERROR'],
        'BD_STORAGE_GET_LUN_INFO_ERROR' => Xphp::$_lang['WEB_ERROR_BD_STORAGE_GET_LUN_INFO_ERROR'],
        'BD_STORAGE_GET_SNAPSHOT_INFO_ERROR' => Xphp::$_lang['WEB_ERROR_BD_STORAGE_GET_SNAPSHOT_INFO_ERROR'],
        'BD_STORAGE_CREATE_SNAPSHOT_ERROR' => Xphp::$_lang['WEB_ERROR_BD_STORAGE_CREATE_SNAPSHOT_ERROR'],
        'BD_STORAGE_DELETE_SNAPSHOT_ERROR' => Xphp::$_lang['WEB_ERROR_BD_STORAGE_DELETE_SNAPSHOT_ERROR'],
        'BD_STORAGE_ACTIVE_SNAPSHOT_ERROR' => Xphp::$_lang['WEB_ERROR_BD_STORAGE_ACTIVE_SNAPSHOT_ERROR'],
        'BD_STORAGE_INACTIVE_SNAPSHOT_ERROR' => Xphp::$_lang['WEB_ERROR_BD_STORAGE_INACTIVE_SNAPSHOT_ERROR'],
        'BD_STORAGE_GET_HOST_ERROR' => Xphp::$_lang['WEB_ERROR_BD_STORAGE_GET_HOST_ERROR'],
        'BD_STORAGE_GET_HOST_GROUP_ERROR' => Xphp::$_lang['WEB_ERROR_BD_STORAGE_GET_HOST_GROUP_ERROR'],
        'BD_STORAGE_GET_LUN_GROUP_ERROR' => Xphp::$_lang['WEB_ERROR_BD_STORAGE_GET_LUN_GROUP_ERROR'],
        'BD_STORAGE_GET_MAP_INFO_ERROR' => Xphp::$_lang['WEB_ERROR_BD_STORAGE_GET_MAP_INFO_ERROR'],
        'BD_STORAGE_CREATE_INITIATOR_ERROR' => Xphp::$_lang['WEB_ERROR_BD_STORAGE_CREATE_INITIATOR_ERROR'],
        'BD_STORAGE_ADD_INITIATOR_TO_HOST_ERROR' => Xphp::$_lang['WEB_ERROR_BD_STORAGE_ADD_INITIATOR_TO_HOST_ERROR'],
        'BD_STORAGE_REMOVE_INITIATOR_FROM_HOST_ERROR' => Xphp::$_lang['WEB_ERROR_BD_STORAGE_REMOVE_INITIATOR_FROM_HOST_ERROR'],
        'BD_STORAGE_GET_INITIATOR_INFO_ERROR' => Xphp::$_lang['WEB_ERROR_BD_STORAGE_GET_INITIATOR_INFO_ERROR'],
        'BD_STORAGE_CREATE_HOST_ERROR' => Xphp::$_lang['WEB_ERROR_BD_STORAGE_CREATE_HOST_ERROR'],
        'BD_STORAGE_CREATE_LUN_GROUP_ERROR' => Xphp::$_lang['WEB_ERROR_BD_STORAGE_CREATE_LUN_GROUP_ERROR'],
        'BD_STORAGE_CREATE_HOST_GROUP_ERROR' => Xphp::$_lang['WEB_ERROR_BD_STORAGE_CREATE_HOST_GROUP_ERROR'],
        'BD_STORAGE_CREATE_MAP_ERROR' => Xphp::$_lang['WEB_ERROR_BD_STORAGE_CREATE_MAP_ERROR'],
        'BD_S3_ACCESS_CONTROL_LIST_NOT_SUPPORTED' => Xphp::$_lang['WEB_ERROR_BD_S3_ACCESS_CONTROL_LIST_NOT_SUPPORTED'],
        'BD_S3_ACCESS_DENIED' => Xphp::$_lang['WEB_ERROR_BD_S3_ACCESS_DENIED'],
        'BD_S3_ACCESS_POINT_ALREADY_OWNED_BY_YOU' => Xphp::$_lang['WEB_ERROR_BD_S3_ACCESS_POINT_ALREADY_OWNED_BY_YOU'],
        'BD_S3_ACCOUNT_PROBLEM' => Xphp::$_lang['WEB_ERROR_BD_S3_ACCOUNT_PROBLEM'],
        'BD_S3_ALL_ACCESS_DISABLED' => Xphp::$_lang['WEB_ERROR_BD_S3_ALL_ACCESS_DISABLED'],
        'BD_S3_AMBIGUOUS_GRANT_BY_EMAIL_ADDRESS' => Xphp::$_lang['WEB_ERROR_BD_S3_AMBIGUOUS_GRANT_BY_EMAIL_ADDRESS'],
        'BD_S3_AUTHORIZATION_HEADER_MALFORMED' => Xphp::$_lang['WEB_ERROR_BD_S3_AUTHORIZATION_HEADER_MALFORMED'],
        'BD_S3_BAD_DIGEST' => Xphp::$_lang['WEB_ERROR_BD_S3_BAD_DIGEST'],
        'BD_S3_BUCKET_ALREADY_EXISTS' => Xphp::$_lang['WEB_ERROR_BD_S3_BUCKET_ALREADY_EXISTS'],
        'BD_S3_BUCKET_ALREADY_OWNED_BY_YOU' => Xphp::$_lang['WEB_ERROR_BD_S3_BUCKET_ALREADY_OWNED_BY_YOU'],
        'BD_S3_BUCKET_NOT_EMPTY' => Xphp::$_lang['WEB_ERROR_BD_S3_BUCKET_NOT_EMPTY'],
        'BD_S3_CLIENT_TOKEN_CONFLICT' => Xphp::$_lang['WEB_ERROR_BD_S3_CLIENT_TOKEN_CONFLICT'],
        'BD_S3_CREDENTIALS_NOT_SUPPORTED' => Xphp::$_lang['WEB_ERROR_BD_S3_CREDENTIALS_NOT_SUPPORTED'],
        'BD_S3_CROSS_LOCATION_LOGGING_PROHIBITED' => Xphp::$_lang['WEB_ERROR_BD_S3_CROSS_LOCATION_LOGGING_PROHIBITED'],
        'BD_S3_ENTITY_TOO_SMALL' => Xphp::$_lang['WEB_ERROR_BD_S3_ENTITY_TOO_SMALL'],
        'BD_S3_ENTITY_TOO_LARGE' => Xphp::$_lang['WEB_ERROR_BD_S3_ENTITY_TOO_LARGE'],
        'BD_S3_EXPIRED_TOKEN' => Xphp::$_lang['WEB_ERROR_BD_S3_EXPIRED_TOKEN'],
        'BD_S3_ILLEGAL_LOCATION_CONSTRAINT_EXCEPTION' => Xphp::$_lang['WEB_ERROR_BD_S3_ILLEGAL_LOCATION_CONSTRAINT_EXCEPTION'],
        'BD_S3_ILLEGAL_VERSIONING_CONFIGURATION_EXCEPTION' => Xphp::$_lang['WEB_ERROR_BD_S3_ILLEGAL_VERSIONING_CONFIGURATION_EXCEPTION'],
        'BD_S3_INCOMPLETE_BODY' => Xphp::$_lang['WEB_ERROR_BD_S3_INCOMPLETE_BODY'],
        'BD_S3_INCORRECT_NUMBER_OF_FILES_IN_POST_REQUEST' => Xphp::$_lang['WEB_ERROR_BD_S3_INCORRECT_NUMBER_OF_FILES_IN_POST_REQUEST'],
        'BD_S3_INLINE_DATA_TOO_LARGE' => Xphp::$_lang['WEB_ERROR_BD_S3_INLINE_DATA_TOO_LARGE'],
        'BD_S3_INTERNAL_ERROR' => Xphp::$_lang['WEB_ERROR_BD_S3_INTERNAL_ERROR'],
        'BD_S3_INVALID_ACCESS_KEY_ID' => Xphp::$_lang['WEB_ERROR_BD_S3_INVALID_ACCESS_KEY_ID'],
        'BD_S3_INVALID_ACCESS_POINT' => Xphp::$_lang['WEB_ERROR_BD_S3_INVALID_ACCESS_POINT'],
        'BD_S3_INVALID_ACCESS_POINT_ALIAS_ERROR' => Xphp::$_lang['WEB_ERROR_BD_S3_INVALID_ACCESS_POINT_ALIAS_ERROR'],
        'BD_S3_INVALID_ADDRESSING_HEADER' => Xphp::$_lang['WEB_ERROR_BD_S3_INVALID_ADDRESSING_HEADER'],
        'BD_S3_INVALID_ARGUMENT' => Xphp::$_lang['WEB_ERROR_BD_S3_INVALID_ARGUMENT'],
        'BD_S3_INVALID_BUCKET_ACL_WITH_OBJECT_OWNERSHIP' => Xphp::$_lang['WEB_ERROR_BD_S3_INVALID_BUCKET_ACL_WITH_OBJECT_OWNERSHIP'],
        'BD_S3_INVALID_BUCKET_NAME' => Xphp::$_lang['WEB_ERROR_BD_S3_INVALID_BUCKET_NAME'],
        'BD_S3_INVALID_BUCKET_STATE' => Xphp::$_lang['WEB_ERROR_BD_S3_INVALID_BUCKET_STATE'],
        'BD_S3_INVALID_DIGEST' => Xphp::$_lang['WEB_ERROR_BD_S3_INVALID_DIGEST'],
        'BD_S3_INVALID_ENCRYPTION_ALGORITHM_ERROR' => Xphp::$_lang['WEB_ERROR_BD_S3_INVALID_ENCRYPTION_ALGORITHM_ERROR'],
        'BD_S3_INVALID_LOCATION_CONSTRAINT' => Xphp::$_lang['WEB_ERROR_BD_S3_INVALID_LOCATION_CONSTRAINT'],
        'BD_S3_INVALID_OBJECT_STATE' => Xphp::$_lang['WEB_ERROR_BD_S3_INVALID_OBJECT_STATE'],
        'BD_S3_INVALID_PART' => Xphp::$_lang['WEB_ERROR_BD_S3_INVALID_PART'],
        'BD_S3_INVALID_PART_ORDER' => Xphp::$_lang['WEB_ERROR_BD_S3_INVALID_PART_ORDER'],
        'BD_S3_INVALID_PAYER' => Xphp::$_lang['WEB_ERROR_BD_S3_INVALID_PAYER'],
        'BD_S3_INVALID_POLICY_DOCUMENT' => Xphp::$_lang['WEB_ERROR_BD_S3_INVALID_POLICY_DOCUMENT'],
        'BD_S3_INVALID_RANGE' => Xphp::$_lang['WEB_ERROR_BD_S3_INVALID_RANGE'],
        'BD_S3_INVALID_REQUEST' => Xphp::$_lang['WEB_ERROR_BD_S3_INVALID_REQUEST'],
        'BD_S3_INVALID_SECURITY' => Xphp::$_lang['WEB_ERROR_BD_S3_INVALID_SECURITY'],
        'BD_S3_INVALID_SOAPREQUEST' => Xphp::$_lang['WEB_ERROR_BD_S3_INVALID_SOAPREQUEST'],
        'BD_S3_INVALID_STORAGE_CLASS' => Xphp::$_lang['WEB_ERROR_BD_S3_INVALID_STORAGE_CLASS'],
        'BD_S3_INVALID_TARGET_BUCKET_FOR_LOGGING' => Xphp::$_lang['WEB_ERROR_BD_S3_INVALID_TARGET_BUCKET_FOR_LOGGING'],
        'BD_S3_INVALID_TOKEN' => Xphp::$_lang['WEB_ERROR_BD_S3_INVALID_TOKEN'],
        'BD_S3_INVALID_URI' => Xphp::$_lang['WEB_ERROR_BD_S3_INVALID_URI'],
        'BD_S3_KEY_TOO_LONG_ERROR' => Xphp::$_lang['WEB_ERROR_BD_S3_KEY_TOO_LONG_ERROR'],
        'BD_S3_MALFORMED_ACL_ERROR' => Xphp::$_lang['WEB_ERROR_BD_S3_MALFORMED_ACL_ERROR'],
        'BD_S3_MALFORMED_POSTREQUEST' => Xphp::$_lang['WEB_ERROR_BD_S3_MALFORMED_POSTREQUEST'],
        'BD_S3_MALFORMED_XML' => Xphp::$_lang['WEB_ERROR_BD_S3_MALFORMED_XML'],
        'BD_S3_MAX_MESSAGE_LENGTH_EXCEEDED' => Xphp::$_lang['WEB_ERROR_BD_S3_MAX_MESSAGE_LENGTH_EXCEEDED'],
        'BD_S3_MAX_POST_PRE_DATA_LENGTH_EXCEEDED_ERROR' => Xphp::$_lang['WEB_ERROR_BD_S3_MAX_POST_PRE_DATA_LENGTH_EXCEEDED_ERROR'],
        'BD_S3_METADATA_TOO_LARGE' => Xphp::$_lang['WEB_ERROR_BD_S3_METADATA_TOO_LARGE'],
        'BD_S3_METHOD_NOT_ALLOWED' => Xphp::$_lang['WEB_ERROR_BD_S3_METHOD_NOT_ALLOWED'],
        'BD_S3_MISSING_ATTACHMENT' => Xphp::$_lang['WEB_ERROR_BD_S3_MISSING_ATTACHMENT'],
        'BD_S3_MISSING_CONTENT_LENGTH' => Xphp::$_lang['WEB_ERROR_BD_S3_MISSING_CONTENT_LENGTH'],
        'BD_S3_MISSING_REQUEST_BODY_ERROR' => Xphp::$_lang['WEB_ERROR_BD_S3_MISSING_REQUEST_BODY_ERROR'],
        'BD_S3_MISSING_SECURITY_ELEMENT' => Xphp::$_lang['WEB_ERROR_BD_S3_MISSING_SECURITY_ELEMENT'],
        'BD_S3_MISSING_SECURITY_HEADER' => Xphp::$_lang['WEB_ERROR_BD_S3_MISSING_SECURITY_HEADER'],
        'BD_S3_NO_LOGGING_STATUS_FOR_KEY' => Xphp::$_lang['WEB_ERROR_BD_S3_NO_LOGGING_STATUS_FOR_KEY'],
        'BD_S3_NO_SUCH_BUCKET' => Xphp::$_lang['WEB_ERROR_BD_S3_NO_SUCH_BUCKET'],
        'BD_S3_NO_SUCH_BUCKET_POLICY' => Xphp::$_lang['WEB_ERROR_BD_S3_NO_SUCH_BUCKET_POLICY'],
        'BD_S3_NO_SUCH_CORSCONFIGURATION' => Xphp::$_lang['WEB_ERROR_BD_S3_NO_SUCH_CORSCONFIGURATION'],
        'BD_S3_NO_SUCH_KEY' => Xphp::$_lang['WEB_ERROR_BD_S3_NO_SUCH_KEY'],
        'BD_S3_NO_SUCH_LIFECYCLE_CONFIGURATION' => Xphp::$_lang['WEB_ERROR_'],
        'BD_S3_NO_SUCH_MULTI_REGION_ACCESS_POINT' => Xphp::$_lang['WEB_ERROR_'],
        'BD_S3_NO_SUCH_WEBSITE_CONFIGURATION' => Xphp::$_lang['WEB_ERROR_'],
        'BD_S3_NO_SUCH_TAG_SET' => Xphp::$_lang['WEB_ERROR_BD_S3_NO_SUCH_TAG_SET'],
        'BD_S3_NO_SUCH_UPLOAD' => Xphp::$_lang['WEB_ERROR_BD_S3_NO_SUCH_UPLOAD'],
        'BD_S3_OBJECT_LOCK_CONFIGURATION_NOT_FOUND_ERROR' => Xphp::$_lang['WEB_ERROR_BD_S3_OBJECT_LOCK_CONFIGURATION_NOT_FOUND_ERROR'],
        'BD_S3_OPERATION_ABORTED' => Xphp::$_lang['WEB_ERROR_BD_S3_OPERATION_ABORTED'],
        'BD_S3_PERMANENT_REDIRECT' => Xphp::$_lang['WEB_ERROR_BD_S3_PERMANENT_REDIRECT'],
        'BD_S3_PRECONDITION_FAILED' => Xphp::$_lang['WEB_ERROR_BD_S3_PRECONDITION_FAILED'],
        'BD_S3_REDIRECT' => Xphp::$_lang['WEB_ERROR_BD_S3_REDIRECT'],
        'BD_S3_REQUEST_HEADER_SECTION_TOO_LARGE' => Xphp::$_lang['WEB_ERROR_BD_S3_REQUEST_HEADER_SECTION_TOO_LARGE'],
        'BD_S3_REQUEST_IS_NOT_MULTI_PART_CONTENT' => Xphp::$_lang['WEB_ERROR_BD_S3_REQUEST_IS_NOT_MULTI_PART_CONTENT'],
        'BD_S3_REQUEST_TIMEOUT' => Xphp::$_lang['WEB_ERROR_BD_S3_REQUEST_TIMEOUT'],
        'BD_S3_REQUEST_TIME_TOO_SKEWED' => Xphp::$_lang['WEB_ERROR_BD_S3_REQUEST_TIME_TOO_SKEWED'],
        'BD_S3_REQUEST_TORRENT_OF_BUCKET_ERROR' => Xphp::$_lang['WEB_ERROR_BD_S3_REQUEST_TORRENT_OF_BUCKET_ERROR'],
        'BD_S3_RESTORE_ALREADY_IN_PROGRESS' => Xphp::$_lang['WEB_ERROR_BD_S3_RESTORE_ALREADY_IN_PROGRESS'],
        'BD_S3_SERVER_SIDE_ENCRYPTION_CONFIGURATION_NOT_FOUND_ERROR' => Xphp::$_lang['WEB_ERROR_BD_S3_SERVER_SIDE_ENCRYPTION_CONFIGURATION_NOT_FOUND_ERROR'],
        'BD_S3_SERVICE_UNAVAILABLE' => Xphp::$_lang['WEB_ERROR_BD_S3_SERVICE_UNAVAILABLE'],
        'BD_S3_SIGNATURE_DOES_NOT_MATCH' => Xphp::$_lang['WEB_ERROR_BD_S3_SIGNATURE_DOES_NOT_MATCH'],
        'BD_S3_SLOW_DOWN' => Xphp::$_lang['WEB_ERROR_BD_S3_SLOW_DOWN'],
        'BD_S3_SLOW_DOWN_503' => Xphp::$_lang['WEB_ERROR_BD_S3_SLOW_DOWN_503'],
        'BD_S3_TEMPORARY_REDIRECT' => Xphp::$_lang['WEB_ERROR_BD_S3_TEMPORARY_REDIRECT'],
        'BD_S3_TOKEN_REFRESH_REQUIRED' => Xphp::$_lang['WEB_ERROR_BD_S3_TOKEN_REFRESH_REQUIRED'],
        'BD_S3_TOO_MANY_ACCESS_POINTS' => Xphp::$_lang['WEB_ERROR_BD_S3_TOO_MANY_ACCESS_POINTS'],
        'BD_S3_TOO_MANY_BUCKETS' => Xphp::$_lang['WEB_ERROR_BD_S3_TOO_MANY_BUCKETS'],
        'BD_S3_TOO_MANY_MULTI_REGION_ACCESS_POINTREGIONS_ERROR' => Xphp::$_lang['WEB_ERROR_BD_S3_TOO_MANY_MULTI_REGION_ACCESS_POINTREGIONS_ERROR'],
        'BD_S3_TOO_MANY_MULTI_REGION_ACCESS_POINTS' => Xphp::$_lang['WEB_ERROR_BD_S3_TOO_MANY_MULTI_REGION_ACCESS_POINTS'],
        'BD_S3_UNEXPECTED_CONTENT' => Xphp::$_lang['WEB_ERROR_BD_S3_UNEXPECTED_CONTENT'],
        'BD_S3_UNRESOLVABLE_GRANT_BY_EMAIL_ADDRESS' => Xphp::$_lang['WEB_ERROR_BD_S3_UNRESOLVABLE_GRANT_BY_EMAIL_ADDRESS'],
        'BD_S3_USER_KEY_MUST_BE_SPECIFIED' => Xphp::$_lang['WEB_ERROR_BD_S3_USER_KEY_MUST_BE_SPECIFIED'],
        'BD_S3_NO_SUCH_ACCESS_POINT' => Xphp::$_lang['WEB_ERROR_BD_S3_NO_SUCH_ACCESS_POINT'],
        'BD_S3_INVALID_TAG' => Xphp::$_lang['WEB_ERROR_BD_S3_INVALID_TAG'],
        'BD_S3_MALFORMED_POLICY' => Xphp::$_lang['WEB_ERROR_BD_S3_MALFORMED_POLICY'],
        'BD_CEPH_RADOS_GET_OMAP_KEY_VAL_ERROR' => Xphp::$_lang['WEB_ERROR_BD_CEPH_RADOS_GET_OMAP_KEY_VAL_ERROR'],
        'BD_CEPH_IS_NOT_HEALTHY_ERROR' => Xphp::$_lang['WEB_ERROR_BD_CEPH_IS_NOT_HEALTHY_ERROR'],

        'BD_OFFLINE_REMOVE_DEVICE_INSTALLATION_RESTRICTIONS_ERROR' => Xphp::$_lang['WEB_ERROR_BD_OFFLINE_REMOVE_DEVICE_INSTALLATION_RESTRICTIONS_ERROR'],
        'BD_OFFLINE_REMOVE_DEVICE_INSTALLATION_RESTRICTIONS_WARNING' => Xphp::$_lang['WEB_ERROR_BD_OFFLINE_REMOVE_DEVICE_INSTALLATION_RESTRICTIONS_WARNING'],

        'BD_LICENSE_INVALID_ROOT_TYPE' => Xphp::$_lang['WEB_ERROR_BD_LICENSE_INVALID_ROOT_TYPE'],
        'BD_LICENSE_AUTH_SPACE_NOT_ENOUGH_ERROR' => Xphp::$_lang['WEB_ERROR_BD_LICENSE_AUTH_SPACE_NOT_ENOUGH_ERROR'],
        'BD_LICENSE_AUTH_SPACE_NOT_ENOUGH_ERROR_TO_CONTINUE_VOLCDP_BACKUP' => Xphp::$_lang['WEB_ERROR_BD_LICENSE_AUTH_SPACE_NOT_ENOUGH_ERROR_TO_CONTINUE_VOLCDP_BACKUP'],
        'BD_LICENSE_CLIENT_CAPACITY_MAX_CONSUMED_100' => Xphp::$_lang['WEB_ERROR_BD_LICENSE_CLIENT_CAPACITY_MAX_CONSUMED_100'],
        'BD_LICENSE_CLIENT_CAPACITY_MAX_CONSUMED_150' => Xphp::$_lang['WEB_ERROR_BD_LICENSE_CLIENT_CAPACITY_MAX_CONSUMED_150'],
        'BD_LICENSE_OF_EMD_TAKEOVER_EXHAUST_ERROR' => Xphp::$_lang['WEB_ERROR_BD_LICENSE_OF_EMD_TAKEOVER_EXHAUST_ERROR'],
        'BD_LICENSE_AUTH_EXPIRED' => Xphp::$_lang['WEB_ERROR_BD_LICENSE_AUTH_EXPIRED'],
        'BD_LICENSE_AUTH_SPACE_NOT_ENOUGH_ERROR_TO_EXEC_CDP' => Xphp::$_lang['WEB_ERROR_BD_LICENSE_AUTH_SPACE_NOT_ENOUGH_ERROR_TO_EXEC_CDP'],


        'BD_COMMAND_ERROR' => Xphp::$_lang['WEB_ERROR_BD_COMMAND_ERROR'],
        'BD_COMMAND_PERMISSION_DENIED_ERROR' => Xphp::$_lang['WEB_ERROR_BD_COMMAND_PERMISSION_DENIED_ERROR'],
        'BD_COMMAND_NOT_FOUND_ERROR' => Xphp::$_lang['WEB_ERROR_BD_COMMAND_NOT_FOUND_ERROR'],
        'BD_COMMAND_INTERRUPTED_ERROR' => Xphp::$_lang['WEB_ERROR_BD_COMMAND_INTERRUPTED_ERROR'],
        'BD_COMMAND_SYSTEM_ERROR' => Xphp::$_lang['WEB_ERROR_BD_COMMAND_SYSTEM_ERROR'],
        'BD_ISCSI_ERR' => Xphp::$_lang['WEB_ERROR_BD_ISCSI_ERR'],
        'BD_ISCSI_ERR_SESS_NOT_FOUND' => Xphp::$_lang['WEB_ERROR_BD_ISCSI_ERR_SESS_NOT_FOUND'],
        'BD_ISCSI_ERR_NOMEM' => Xphp::$_lang['WEB_ERROR_BD_ISCSI_ERR_NOMEM'],
        'BD_ISCSI_ERR_TRANS' => Xphp::$_lang['WEB_ERROR_BD_ISCSI_ERR_TRANS'],
        'BD_ISCSI_ERR_LOGIN' => Xphp::$_lang['WEB_ERROR_BD_ISCSI_ERR_LOGIN'],
        'BD_ISCSI_ERR_IDBM' => Xphp::$_lang['WEB_ERROR_BD_ISCSI_ERR_IDBM'],
        'BD_ISCSI_ERR_INVAL' => Xphp::$_lang['WEB_ERROR_BD_ISCSI_ERR_INVAL'],
        'BD_ISCSI_ERR_TRANS_TIMEOUT' => Xphp::$_lang['WEB_ERROR_BD_ISCSI_ERR_TRANS_TIMEOUT'],
        'BD_ISCSI_ERR_INTERNAL' => Xphp::$_lang['WEB_ERROR_BD_ISCSI_ERR_INTERNAL'],
        'BD_ISCSI_ERR_LOGOUT' => Xphp::$_lang['WEB_ERROR_BD_ISCSI_ERR_LOGOUT'],
        'BD_ISCSI_ERR_PDU_TIMEOUT' => Xphp::$_lang['WEB_ERROR_BD_ISCSI_ERR_PDU_TIMEOUT'],
        'BD_ISCSI_ERR_TRANS_NOT_FOUND' => Xphp::$_lang['WEB_ERROR_BD_ISCSI_ERR_TRANS_NOT_FOUND'],
        'BD_ISCSI_ERR_ACCESS' => Xphp::$_lang['WEB_ERROR_BD_ISCSI_ERR_ACCESS'],
        'BD_ISCSI_ERR_TRANS_CAPS' => Xphp::$_lang['WEB_ERROR_BD_ISCSI_ERR_TRANS_CAPS'],
        'BD_ISCSI_ERR_SESS_EXISTS' => Xphp::$_lang['WEB_ERROR_BD_ISCSI_ERR_SESS_EXISTS'],
        'BD_ISCSI_ERR_INVALID_MGMT_REQ' => Xphp::$_lang['WEB_ERROR_BD_ISCSI_ERR_INVALID_MGMT_REQ'],
        'BD_ISCSI_ERR_ISNS_UNAVAILABLE' => Xphp::$_lang['WEB_ERROR_BD_ISCSI_ERR_ISNS_UNAVAILABLE'],
        'BD_ISCSI_ERR_ISCSID_COMM_ERR' => Xphp::$_lang['WEB_ERROR_BD_ISCSI_ERR_ISCSID_COMM_ERR'],
        'BD_ISCSI_ERR_FATAL_LOGIN' => Xphp::$_lang['WEB_ERROR_BD_ISCSI_ERR_FATAL_LOGIN'],
        'BD_ISCSI_ERR_ISCSID_NOTCONN' => Xphp::$_lang['WEB_ERROR_BD_ISCSI_ERR_ISCSID_NOTCONN'],
        'BD_ISCSI_ERR_NO_OBJS_FOUND' => Xphp::$_lang['WEB_ERROR_BD_ISCSI_ERR_NO_OBJS_FOUND'],
        'BD_ISCSI_ERR_SYSFS_LOOKUP' => Xphp::$_lang['BD_ISCSI_ERR_SYSFS_LOOKUP'],
        'BD_ISCSI_ERR_HOST_NOT_FOUND' => Xphp::$_lang['WEB_ERROR_BD_ISCSI_ERR_HOST_NOT_FOUND'],
        'BD_ISCSI_ERR_LOGIN_AUTH_FAILED' => Xphp::$_lang['WEB_ERROR_BD_ISCSI_ERR_LOGIN_AUTH_FAILED'],
        'BD_ISCSI_ERR_ISNS_QUERY' => Xphp::$_lang['WEB_ERROR_BD_ISCSI_ERR_ISNS_QUERY'],
        'BD_ISCSI_ERR_ISNS_REG_FAILED' => Xphp::$_lang['WEB_ERROR_BD_ISCSI_ERR_ISNS_REG_FAILED'],
        'BD_DATA_STORAGE_FILE_LOCK_FAILED' => Xphp::$_lang['WEB_ERROR_BD_DATA_STORAGE_FILE_LOCK_FAILED'],
        'BD_DATA_STORAGE_FILE_UNLOCK_FAILED' => Xphp::$_lang['WEB_ERROR_BD_DATA_STORAGE_FILE_UNLOCK_FAILED'],
        'BD_STORAGE_TAPE_GROUP_ONLY_USE_ONE_BACKUP_SET_AND_BACKUP_SET_FREEZE' => Xphp::$_lang['WEB_ERROR_BD_STORAGE_TAPE_GROUP_ONLY_USE_ONE_BACKUP_SET_AND_BACKUP_SET_FREEZE'],
        'BD_TIMEPOINT_CHECK_BACKUP_TASK_OCCUPY_CHAIN_ERROR' => Xphp::$_lang['WEB_ERROR_BD_TIMEPOINT_CHECK_BACKUP_TASK_OCCUPY_CHAIN_ERROR'],
        'BD_TIMEPOINT_CHECK_BACKUP_COPY_TASK_OCCUPY_CHAIN_ERROR' => Xphp::$_lang['WEB_ERROR_BD_TIMEPOINT_CHECK_BACKUP_COPY_TASK_OCCUPY_CHAIN_ERROR'],
        'BD_TIMEPOINT_CHECK_RECOVERY_TASK_OCCUPY_CHAIN_ERROR' => Xphp::$_lang['WEB_ERROR_BD_TIMEPOINT_CHECK_RECOVERY_TASK_OCCUPY_CHAIN_ERROR'],
        'BD_STORAGE_IMPORT_BACKUP_TIMEPOINT_ERROR' => Xphp::$_lang['WEB_ERROR_BD_STORAGE_IMPORT_BACKUP_TIMEPOINT_ERROR'],
        'BD_CURRENT_OBJECT_HAS_NO_BACKUP_TIMEPOINT_ERROR' => Xphp::$_lang['WEB_ERROR_BD_CURRENT_OBJECT_HAS_NO_BACKUP_TIMEPOINT_ERROR'],
        'BD_STORAGE_ADD_HOST_TO_HOST_GROUP_ERROR' => Xphp::$_lang['WEB_ERROR_BD_STORAGE_ADD_HOST_TO_HOST_GROUP_ERROR'],
        'BD_TAPE_STORAGE_TYPE_NOT_SUPPORT_DESTINATION_INC_OR_DIFF' => Xphp::$_lang['WEB_ERROR_BD_TAPE_STORAGE_TYPE_NOT_SUPPORT_DESTINATION_INC_OR_DIFF'],
        'BD_NETWORK_UNSURPORTED_ENCRYPT_METHOD' => Xphp::$_lang['WEB_ERROR_BD_NETWORK_UNSURPORTED_ENCRYPT_METHOD'],
        'BD_TASK_ALREADY_PENDING_ERROR' => Xphp::$_lang['WEB_ERROR_BD_TASK_ALREADY_PENDING_ERROR'],
        'BD_STORAGE_TAPE_CARRIAGE_NO_ENOUGH_SPACE' => Xphp::$_lang['WEB_ERROR_BD_STORAGE_TAPE_CARRIAGE_NO_ENOUGH_SPACE'],
        'BD_STORAGE_TAPE_NOT_FOUND_USEABLE_CARRIAGE' => Xphp::$_lang['WEB_ERROR_BD_STORAGE_TAPE_NOT_FOUND_USEABLE_CARRIAGE'],
        'BD_STORAGE_TAPE_CARRIAGE_USING_FOR_ANOTHER_TASK' => Xphp::$_lang['WEB_ERROR_BD_STORAGE_TAPE_CARRIAGE_USING_FOR_ANOTHER_TASK'],
        'BD_STORAGE_TAPE_GROUP_NEED_ADD_IDLE_TAPE_CARRIAGE' => Xphp::$_lang['WEB_ERROR_BD_STORAGE_TAPE_GROUP_NEED_ADD_IDLE_TAPE_CARRIAGE'],
        'BD_STORAGE_TAPE_GROUP_NEED_GENERATE_NEW_BACKUP_SET' => Xphp::$_lang['WEB_ERROR_BD_STORAGE_TAPE_GROUP_NEED_GENERATE_NEW_BACKUP_SET'],
        'BD_DEPEND_TIMEPOINT_BITMAP_VERSION_OLD_ERROR' => Xphp::$_lang['WEB_ERROR_BD_DEPEND_TIMEPOINT_BITMAP_VERSION_OLD_ERROR'],
        'BD_INTEGRITY_CHECK_ERROR' => Xphp::$_lang['WEB_ERROR_BD_INTEGRITY_CHECK_ERROR'],
        'BD_INTEGRITY_CHECK_ERROR_STRATEGY' => Xphp::$_lang['WEB_ERROR_BD_INTEGRITY_CHECK_ERROR_STRATEGY'],
        'BD_INTEGRITY_CHECK_ERROR_NOT_SUPPORT_TASK_TYPE' => Xphp::$_lang['WEB_ERROR_BD_INTEGRITY_CHECK_ERROR_NOT_SUPPORT_TASK_TYPE'],
        'BD_TIMEPOINT_PENETRATION_SEARCH_NOT_COMPLETE' => Xphp::$_lang['WEB_ERROR_BD_TIMEPOINT_PENETRATION_SEARCH_NOT_COMPLETE'],
        'BD_TIMEPOINT_PENETRATION_SEARCH_RESULT_ERROR' => Xphp::$_lang['WEB_ERROR_BD_TIMEPOINT_PENETRATION_SEARCH_RESULT_ERROR'],
        'BD_IP_NOT_MATCH_ERROR' => Xphp::$_lang['WEB_ERROR_BD_IP_NOT_MATCH_ERROR'],
        'BD_WORM_SET_ERROR' => Xphp::$_lang['WEB_ERROR_BD_WORM_SET_ERROR'],
        'BD_API_TOKEN_TIMEOUT' => Xphp::$_lang['WEB_ERROR_BD_API_TOKEN_TIMEOUT'],
        'BD_STORAGE_WORM_CLOSE_ERROR' => Xphp::$_lang['WEB_ERROR_BD_STORAGE_WORM_CLOSE_ERROR'],
        'BD_STORAGE_WORM_NOT_ENOUGH_ERROR' => Xphp::$_lang['WEB_ERROR_BD_STORAGE_WORM_NOT_ENOUGH_ERROR'],
        'BD_WORM_GET_LEFT_TIME_ERROR' => Xphp::$_lang['WEB_ERROR_BD_WORM_GET_LEFT_TIME_ERROR'],
        'BD_WORM_STORAGE_NOT_SUPPORT_ERROR' => Xphp::$_lang['WEB_ERROR_BD_WORM_STORAGE_NOT_SUPPORT_ERROR'],
        'BD_WORM_STORAGE_NOT_ENABLE_ERROR' => Xphp::$_lang['WEB_ERROR_BD_WORM_STORAGE_NOT_ENABLE_ERROR'],

        'BD_TIMEPOINT_PENETRATION_SEARCH_RESULT_NO_DATA' => Xphp::$_lang['WEB_ERROR_BD_TIMEPOINT_PENETRATION_SEARCH_RESULT_NO_DATA'],
        'BD_VIRUS_INIT_ERROR' => Xphp::$_lang['WEB_ERROR_BD_VIRUS_INIT_ERROR'],
        'BD_VIRUS_SET_ENV_ERROR' => Xphp::$_lang['WEB_ERROR_BD_VIRUS_SET_ENV_ERROR'],
        'BD_VIRUS_CREATE_ENGINE_ERROR' => Xphp::$_lang['WEB_ERROR_BD_VIRUS_CREATE_ENGINE_ERROR'],
        'BD_VIRUS_LOAD_LIB_ERROR' => Xphp::$_lang['WEB_ERROR_BD_VIRUS_LOAD_LIB_ERROR'],
        'BD_VIRUS_LIB_INVALID_ERROR' => Xphp::$_lang['WEB_ERROR_BD_VIRUS_LIB_INVALID_ERROR'],
        'BD_VIRUS_GET_MOUNT_POINT_MAP_RELATIONSHIP_ERROR' => Xphp::$_lang['WEB_ERROR_BD_VIRUS_GET_MOUNT_POINT_MAP_RELATIONSHIP_ERROR'],
        'BD_VIRUS_STOP_GET_VIRUS_SCAN_PROGRESS' => Xphp::$_lang['WEB_ERROR_BD_VIRUS_STOP_GET_VIRUS_SCAN_PROGRESS'],
        'BD_VIRUS_FILE_IN_DANGER' => Xphp::$_lang['WEB_ERROR_BD_VIRUS_FILE_IN_DANGER'],
        'BD_OS_TYPE_CHOSEN_MISMATCH_WITH_ACTUAL_ERROR' => Xphp::$_lang['WEB_ERROR_BD_OS_TYPE_CHOSEN_MISMATCH_WITH_ACTUAL_ERROR'],
        'BD_WINDOWS_SESSION_NOT_ACTIVE' => Xphp::$_lang['WEB_ERROR_BD_WINDOWS_SESSION_NOT_ACTIVE'],
        'BD_OFFLINE_RESET_IP_ERROR' => Xphp::$_lang['WEB_ERROR_BD_OFFLINE_RESET_IP_ERROR'],
        'BD_OFFLINE_PLACE_SCRIPT_ERROR' => Xphp::$_lang['WEB_ERROR_BD_OFFLINE_PLACE_SCRIPT_ERROR'],
        'BD_OFFLINE_DELETE_AGENT_UUID_ERROR' => Xphp::$_lang['WEB_ERROR_BD_OFFLINE_DELETE_AGENT_UUID_ERROR'],
        'BD_OFFLINE_RESET_HOST_NAME_ERROR' => Xphp::$_lang['WEB_ERROR_BD_OFFLINE_RESET_HOST_NAME_ERROR'],
        'BD_OFFLINE_RESET_AGENT_UUID_ERROR' => Xphp::$_lang['WEB_ERROR_BD_OFFLINE_RESET_AGENT_UUID_ERROR'],
        'BD_OFFLINE_RESET_VOLCDP_PREFIX_INFO_ERROR' => Xphp::$_lang['WEB_ERROR_BD_OFFLINE_RESET_VOLCDP_PREFIX_INFO_ERROR'],
        'BD_OFFLINE_ADD_DRIVER_ERROR' => Xphp::$_lang['WEB_ERROR_BD_OFFLINE_ADD_DRIVER_ERROR'],
        'BD_OFFLINE_REPAIR_FSTAB_ERROR' => Xphp::$_lang['WEB_ERROR_BD_OFFLINE_REPAIR_FSTAB_ERROR'],
        'BD_OFFLINE_SET_ONLIE_DISK_SCRIPT_ERROR' => Xphp::$_lang['WEB_ERROR_BD_OFFLINE_SET_ONLIE_DISK_SCRIPT_ERROR'],
        'BD_BACKUP_CHAIN_IS_USE_ERROR' => Xphp::$_lang['WEB_ERROR_BD_BACKUP_CHAIN_IS_USE_ERROR'],
        'BD_OFFLINE_NOT_SUPPORT_SCRIPT_TYPE_ERROR' => Xphp::$_lang['WEB_ERROR_BD_OFFLINE_NOT_SUPPORT_SCRIPT_TYPE_ERROR'],
        'BD_RANSOM_PROTECT_STATUS_MODIFY_ERROR' => Xphp::$_lang['WEB_ERROR_BD_RANSOM_PROTECT_STATUS_MODIFY_ERROR'],
        'BD_OFFLINE_MODIFY_NET_MODEL_ERROR' => Xphp::$_lang['WEB_ERROR_BD_OFFLINE_MODIFY_NET_MODEL_ERROR'],
        'BD_DATA_CONTAINER_SIZE_BECOME_SMALLER_ERROR' => Xphp::$_lang['WEB_ERROR_BD_DATA_CONTAINER_SIZE_BECOME_SMALLER_ERROR'],
        'BD_VIRUS_MERGE_RESULT_DB_FILE_FAILED' => Xphp::$_lang['WEB_ERROR_BD_VIRUS_MERGE_RESULT_DB_FILE_FAILED'],
        'BD_OFFLINE_NOT_SUPPORT_MOUNT_BTRFS_FILE_SYSTEM_ERROR' => Xphp::$_lang['WEB_ERROR_BD_OFFLINE_NOT_SUPPORT_MOUNT_BTRFS_FILE_SYSTEM_ERROR'],
        'BD_BACKUP_CHAIN_MERGE_FAIL_ERROR' => Xphp::$_lang['WEB_ERROR_BD_BACKUP_CHAIN_MERGE_FAIL_ERROR'],
        'BD_CAN_NOT_FOUND_GRUB_MENU_CONFIG_FILE' => Xphp::$_lang['WEB_ERROR_BD_CAN_NOT_FOUND_GRUB_MENU_CONFIG_FILE'],
        'BD_VOLUME_MAX_REMAINING_SIZE_NOT_ENOUGH' => Xphp::$_lang['WEB_ERROR_BD_VOLUME_MAX_REMAINING_SIZE_NOT_ENOUGH'],
        'BD_WAITE_THE_RAMOS_ONLINE_TIMEOUT' => Xphp::$_lang['WEB_ERROR_BD_WAITE_THE_RAMOS_ONLINE_TIMEOUT'],
        'BD_OBJECT_NEWEST_BACKUP_TIMEPOINT_DIFFERENT_WITH_DEPEND_TIMEPOINT' => Xphp::$_lang['WEB_ERROR_BD_OBJECT_NEWEST_BACKUP_TIMEPOINT_DIFFERENT_WITH_DEPEND_TIMEPOINT'],
        //unsupported xfs version
        'PT_SERVER_TIME_CONVERT_ERROR' => Xphp::$_lang['WEB_ERROR_PT_SERVER_TIME_CONVERT_ERROR'],
        'PT_SERVER_QUERY_ALL_STRATEGY_ERROR' => Xphp::$_lang['WEB_ERROR_PT_SERVER_QUERY_ALL_STRATEGY_ERROR'],
        'PT_SERVER_STRATEGY_NULL_ERROR' => Xphp::$_lang['WEB_ERROR_PT_SERVER_STRATEGY_NULL_ERROR'],
        'PT_SERVER_STRATEGY_ALREADY_START_ERROR' => Xphp::$_lang['WEB_ERROR_PT_SERVER_STRATEGY_ALREADY_START_ERROR'],
        'PT_SERVER_STRATEGY_TIME_CONFIG_ERROR' => Xphp::$_lang['WEB_ERROR_PT_SERVER_STRATEGY_TIME_CONFIG_ERROR'],

        'PT_SERVER_LICENSE_INVALID_ERROR' => Xphp::$_lang['WEB_ERROR_PT_SERVER_LICENSE_INVALID_ERROR'],
        'PT_SERVER_LICENSE_NOT_BELONG_TO_HOST_ERROR' => Xphp::$_lang['WEB_ERROR_PT_SERVER_LICENSE_NOT_BELONG_TO_HOST_ERROR'],
        'PT_SERVER_LICENSE_DETECTED_OP_ERROR' => Xphp::$_lang['WEB_ERROR_PT_SERVER_LICENSE_DETECTED_OP_ERROR'],
        'PT_SERVER_LICENSE_ALREADY_USED' => Xphp::$_lang['WEB_ERROR_PT_SERVER_LICENSE_ALREADY_USED'],
        'PT_SERVER_LICENSE_SMALLER_THAN_OLD' => Xphp::$_lang['WEB_ERROR_PT_SERVER_LICENSE_SMALLER_THAN_OLD'],

        'PT_SERVER_LICENSE_EXHAUST_ERROR' => Xphp::$_lang['WEB_ERROR_PT_SERVER_LICENSE_EXHAUST_ERROR'],
        'PT_SERVER_LICENSE_TYPE_NOT_MATCH' => Xphp::$_lang['WEB_ERROR_PT_SERVER_LICENSE_TYPE_NOT_MATCH'],
        'PT_SERVER_LICENSE_NOT_ENOUGH' => Xphp::$_lang['WEB_ERROR_PT_SERVER_LICENSE_NOT_ENOUGH'],			// the remaining license quantity is insufficient
        'PT_SERVER_CDP_BACKUP_FEATURE_IS_NOT_SUPPORT_ERROR' => Xphp::$_lang['WEB_ERROR_PT_SERVER_CDP_BACKUP_FEATURE_IS_NOT_SUPPORT_ERROR'], 	//not support real-time backup in license file, please contact technical support
        'PT_SERVER_CDP_TAKEOVER_FEATURE_IS_NOT_SUPPORT_ERROR' => Xphp::$_lang['WEB_ERROR_PT_SERVER_CDP_TAKEOVER_FEATURE_IS_NOT_SUPPORT_ERROR'], //not support real-time takeover in license file, please contact technical support
        'PT_SERVER_CDP_BACKUP_LICENSE_NOT_ENOUGH_ERROR' => Xphp::$_lang['WEB_ERROR_PT_SERVER_CDP_BACKUP_LICENSE_NOT_ENOUGH_ERROR'],	//real-time backup license is not enough, please delete some real-time backup task and re-upload the license file
        'PT_SERVER_CDP_TAKEOVER_LICENSE_NOT_ENOUGH_ERROR' => Xphp::$_lang['WEB_ERROR_PT_SERVER_CDP_TAKEOVER_LICENSE_NOT_ENOUGH_ERROR'], //real-time disaster license is not enough, please delete some real-time backup task which configured auto-takeover and re-upload the license file



        'RT_SERVER_DATABASE_ERROR' => Xphp::$_lang['WEB_ERROR_RT_SERVER_DATABASE_ERROR'],

        //虚拟机
        'VM_DB_MACHINE_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_VM_DB_MACHINE_EXIST_ERROR'],
        'VM_DB_VCENTER_NOT_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_VM_DB_VCENTER_NOT_EXIST_ERROR'],
        'VM_DB_DECODE_PASSWORD_ERROR' => Xphp::$_lang['WEB_ERROR_VM_DB_DECODE_PASSWORD_ERROR'],
        'VM_DB_VCENTER_ALREADY_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_VM_DB_VCENTER_ALREADY_EXIST_ERROR'],
        'VM_DB_HOST_ALREADY_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_VM_DB_HOST_ALREADY_EXIST_ERROR'],
        'VM_HOST_NOT_AUTH_ERROR' => Xphp::$_lang['WEB_ERROR_VM_HOST_NOT_AUTH_ERROR'],

        'VMWARE_CONN_NETWORK_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_CONN_NETWORK_ERROR'],
        'VMWARE_CONN_URL_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_CONN_URL_ERROR'],
        'VMWARE_GET_VCENTER_INFO_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_GET_VCENTER_INFO_ERROR'],
        'VMWARE_LOGIN_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_LOGIN_ERROR'],
        'VMWARE_LOGIN_USERNAME_PASSWORD_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_LOGIN_USERNAME_PASSWORD_ERROR'],
        'VMWARE_INVALID_ARGUMENT' => Xphp::$_lang['WEB_ERROR_VMWARE_INVALID_ARGUMENT'],
        'VMWARE_LOGOUT_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_LOGOUT_ERROR'],
        'VMWARE_AUTHENTICATED_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_AUTHENTICATED_ERROR'],
        'VMWARE_RETRIEVE_PROPERTY_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_RETRIEVE_PROPERTY_ERROR'],
        'VMWARE_RETRIEVE_PROPERTY_ZERO_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_RETRIEVE_PROPERTY_ZERO_ERROR'],
        'VMWARE_SCAN_HOST_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_SCAN_HOST_ERROR'],
        'VMWARE_SCAN_DATACENTER_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_SCAN_DATACENTER_ERROR'],
        'VMWARE_SCAN_VCENTER_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_SCAN_VCENTER_ERROR'],
        'VMWARE_UNSUPPORT_DISK_TYPE_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_UNSUPPORT_DISK_TYPE_ERROR'],
        'VMWARE_NOT_FIND_VM_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_NOT_FIND_VM_ERROR'],
        'VMWARE_GET_VCENTER_THUMBPRINT_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_GET_VCENTER_THUMBPRINT_ERROR'],
        'VMWARE_DISK_LIB_INIT_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_DISK_LIB_INIT_ERROR'],
        'VMWARE_DISK_LIB_LOAD_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_DISK_LIB_LOAD_ERROR'],
        'VMWARE_DISK_LIB_CONNECT_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_DISK_LIB_CONNECT_ERROR'],
        'VMWARE_SET_CBT_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_SET_CBT_ERROR'],
        'VMWARE_POWEROFF_VM_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_POWEROFF_VM_ERROR'],
        'VMWARE_POWERON_VM_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_POWERON_VM_ERROR'],
        'VMWARE_INDEPENDENT_DISK_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_INDEPENDENT_DISK_ERROR'],
        'VMWARE_RDM_DISK_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_RDM_DISK_ERROR'],
        'VMWARE_LATEST_TIMEPOINT_NOT_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_LATEST_TIMEPOINT_NOT_EXIST_ERROR'],
        'VMWARE_LATEST_SNAPSHOT_NOT_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_LATEST_SNAPSHOT_NOT_EXIST_ERROR'],
        'VMWARE_NOT_SUPPORT_CBT_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_NOT_SUPPORT_CBT_ERROR'],
        'VMWARE_CBT_NOT_ENABLE_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_CBT_NOT_ENABLE_ERROR'],
        'VMWARE_DISK_NUM_CHANGED_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_DISK_NUM_CHANGED_ERROR'],
        'VMWARE_DISK_CHANGED_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_DISK_CHANGED_ERROR'],
        'VMWARE_LATEST_CHANGE_ID_IS_EMPTRY_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_LATEST_CHANGE_ID_IS_EMPTRY_ERROR'],
        'VMWARE_LATEST_SNAPSHOT_NOT_IN_CHAIN_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_LATEST_SNAPSHOT_NOT_IN_CHAIN_ERROR'],
        'VMWARE_QUERY_CHANGED_DISK_AREAS_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_QUERY_CHANGED_DISK_AREAS_ERROR'],
        'VMWARE_DISK_NOT_IN_VMFS_VOLUME_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_DISK_NOT_IN_VMFS_VOLUME_ERROR'],
        'VMWARE_CREATE_SNAPSHOT_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_CREATE_SNAPSHOT_ERROR'],
        'VMWARE_DELETE_SNAPSHOT_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_DELETE_SNAPSHOT_ERROR'],
        'VMWARE_GET_SNAPSHOT_TREE_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_GET_SNAPSHOT_TREE_ERROR'],
        'VMWARE_GET_SNAPSHOT_DISKS_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_GET_SNAPSHOT_DISKS_ERROR'],
        'VMWARE_SNAPSHOT_NUM_NOT_ZERO_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_SNAPSHOT_NUM_NOT_ZERO_ERROR'],
        'VMWARE_GET_TOTAL_BACKUP_SIZE_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_GET_TOTAL_BACKUP_SIZE_ERROR'],
        'VMWARE_NOT_FIND_SNAPSHOT_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_NOT_FIND_SNAPSHOT_ERROR'],
        'VMWARE_CREATE_BACKUP_DIR_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_CREATE_BACKUP_DIR_ERROR'],
        'VMWARE_RECOVERY_TIMEPOINT_NOT_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_RECOVERY_TIMEPOINT_NOT_EXIST_ERROR'],
        'VMWARE_DEPEND_TIMEPOINT_NOT_EXIT_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_DEPEND_TIMEPOINT_NOT_EXIT_ERROR'],
        'VMWARE_VM_ALREADY_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_VM_ALREADY_EXIST_ERROR'],
        'VMWARE_HOST_NOT_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_HOST_NOT_EXIST_ERROR'],
        'VMWARE_VCENTER_NOT_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_VCENTER_NOT_EXIST_ERROR'],
        'VMWARE_MACHINE_NOT_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_MACHINE_NOT_EXIST_ERROR'],
        'VMWARE_DATASTORE_NOT_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_DATASTORE_NOT_EXIST_ERROR'],
        'VMWARE_DATASTORE_SPACE_NOT_ENOUGH_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_DATASTORE_SPACE_NOT_ENOUGH_ERROR'],
        'VMWARE_CREATE_VM_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_CREATE_VM_ERROR'],
        'VMWARE_VM_ALREADY_POWEROFF_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_VM_ALREADY_POWEROFF_ERROR'],
        'VMWARE_VM_ALREADY_POWERON_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_VM_ALREADY_POWERON_ERROR'],
        'VMWARE_VM_NOT_SUSPEND_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_VM_NOT_SUSPEND_ERROR'],
        'VMWARE_VM_NOT_POWERON_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_VM_NOT_POWERON_ERROR'],
        'VMWARE_PARSER_VM_CONFIG_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_PARSER_VM_CONFIG_ERROR'],
        'VMWARE_HOST_NOT_SUPPORTED_VM_VERSION_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_HOST_NOT_SUPPORTED_VM_VERSION_ERROR'],
        'VMWARE_CONNECT_DISK_LIB_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_CONNECT_DISK_LIB_ERROR'],
        'VMWARE_OPEN_REMOTE_DISK_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_OPEN_REMOTE_DISK_ERROR'],
        'VMWARE_READ_REMOTE_DISK_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_READ_REMOTE_DISK_ERROR'],
        'VMWARE_WRITE_REMOTE_DISK_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_WRITE_REMOTE_DISK_ERROR'],

        'VMWARE_GET_DISK_INFO_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_GET_DISK_INFO_ERROR'],
        'VMWARE_GET_DATASTORE_INFO_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_GET_DATASTORE_INFO_ERROR'],
        'VMWARE_GET_NETWORK_INFO_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_GET_NETWORK_INFO_ERROR'],
        'VMWARE_SCAN_DATASTORE_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_SCAN_DATASTORE_ERROR'],
        'VMWARE_GET_BACKUP_PREPARE_INFO_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_GET_BACKUP_PREPARE_INFO_ERROR'],
        'VMWARE_GET_RECOVERY_PREPARE_INFO_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_GET_RECOVERY_PREPARE_INFO_ERROR'],
        'VMWARE_GET_RECOVERY_TOTAL_SIZE_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_GET_RECOVERY_TOTAL_SIZE_ERROR'],
        'VMWARE_GET_VM_INFO_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_GET_VM_INFO_ERROR'],
        'VMWARE_OPEN_BACKUP_FILE_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_OPEN_BACKUP_FILE_ERROR'],
        'VMWARE_READ_BACKUP_FILE_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_READ_BACKUP_FILE_ERROR'],
        'VMWARE_WRITE_BACKUP_FILE_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_WRITE_BACKUP_FILE_ERROR'],
        'VMWARE_OPEN_BITMAP_FILE_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_OPEN_BITMAP_FILE_ERROR'],
        'VMWARE_READ_BITMAP_FILE_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_READ_BITMAP_FILE_ERROR'],
        'VMWARE_WRITE_BITMAP_FILE_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_WRITE_BITMAP_FILE_ERROR'],
        'VMWARE_OPEN_LATEST_BITMAP_FILE_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_OPEN_LATEST_BITMAP_FILE_ERROR'],
        'VMWARE_READ_LATEST_BITMAP_FILE_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_READ_LATEST_BITMAP_FILE_ERROR'],
        'VMWARE_OPEN_METADATA_FILE_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_OPEN_METADATA_FILE_ERROR'],
        'VMWARE_WRITE_METADATA_FILE_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_WRITE_METADATA_FILE_ERROR'],
        'VMWARE_READ_METADATA_FILE_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_READ_METADATA_FILE_ERROR'],
        'VMWARE_FIND_BACKUP_FILE_ID_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_FIND_BACKUP_FILE_ID_ERROR'],
        'VMWARE_SAVE_SELF_EXPLAN_FILE_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_SAVE_SELF_EXPLAN_FILE_ERROR'],
        'VMWARE_COMPRESS_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_COMPRESS_ERROR'],
        'VMWARE_DECOMPRESS_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_DECOMPRESS_ERROR'],
        'VMWARE_REVERT_TO_SNAPSHOT_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_REVERT_TO_SNAPSHOT_ERROR'],
        'VMWARE_DEDUPE_BLOCK_SIZE_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_DEDUPE_BLOCK_SIZE_ERROR'],
        'VMWARE_GET_RECOVERY_VALID_DATA_SIZE_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_GET_RECOVERY_VALID_DATA_SIZE_ERROR'],
        'VMWARE_CREATE_DATASTORE_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_CREATE_DATASTORE_ERROR'],
        'VMWARE_DELETE_DATASTORE_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_DELETE_DATASTORE_ERROR'],
        'VMWARE_MOUNT_NFS_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_MOUNT_NFS_ERROR'],
        'VMWARE_CONNECT_TO_VINFS_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_CONNECT_TO_VINFS_ERROR'],
        'VMWARE_START_NFS_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_START_NFS_ERROR'],
        'VMWARE_COPY_BITMAP_FILE_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_COPY_BITMAP_FILE_ERROR'],
        'VMWARE_MERGE_TIMEPOINT_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_MERGE_TIMEPOINT_ERROR'],
        'VMWARE_BUILD_BACKUP_VM_LIST_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_BUILD_BACKUP_VM_LIST_ERROR'],
        'VMWARE_CHECK_AND_UPDATE_VM_INFO_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_CHECK_AND_UPDATE_VM_INFO_ERROR'],
        'VMWARE_BACKUP_VM_IS_NOT_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_BACKUP_VM_IS_NOT_EXIST_ERROR'],
        'VMWARE_BACKUP_VM_IS_NOT_CONNECTED_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_BACKUP_VM_IS_NOT_CONNECTED_ERROR'],
        'VMWARE_BUILD_RECOVERY_VM_LIST_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_BUILD_RECOVERY_VM_LIST_ERROR'],
        'VMWARE_BUILD_INSTANT_RECOVERY_VM_INFO_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_BUILD_INSTANT_RECOVERY_VM_INFO_ERROR'],
        'VMWARE_BUILD_MOTION_VM_INFO_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_BUILD_MOTION_VM_INFO_ERROR'],
        'VMWARE_EXPORT_NFS_TABLE_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_EXPORT_NFS_TABLE_ERROR'],
        'VMWARE_OPEN_REMOTE_DISK_NETWORK_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_OPEN_REMOTE_DISK_NETWORK_ERROR'],
        'VMWARE_DELETE_VM_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_DELETE_VM_ERROR'],
        'VMWARE_BACKUP_CHAIN_IS_MERGING_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_BACKUP_CHAIN_IS_MERGING_ERROR'],
        'VMWARE_UPDATE_BACKUP_TOTAL_SIZE_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_UPDATE_BACKUP_TOTAL_SIZE_ERROR'],
        'VMWARE_UPDATE_BACKUP_REAL_SIZE_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_UPDATE_BACKUP_REAL_SIZE_ERROR'],
        'VMWARE_BACKUP_CHAIN_IS_USING_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_BACKUP_CHAIN_IS_USING_ERROR'],
        'VMWARE_VM_NOT_CREATE_COMPLETE_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_VM_NOT_CREATE_COMPLETE_ERROR'],
        'VMWARE_CREATE_DIR_ON_DATASTORE_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_CREATE_DIR_ON_DATASTORE_ERROR'],
        'VMWARE_DELETE_DIR_FROM_DATASTORE_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_DELETE_DIR_FROM_DATASTORE_ERROR'],
        'VMWARE_PROXY_VM_SOURCE_FILE_NOT_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_PROXY_VM_SOURCE_FILE_NOT_EXIST_ERROR'],

        'XENSERVER_SDK_GENERIC_ERROR' => Xphp::$_lang['WEB_ERROR_XENSERVER_SDK_GENERIC_ERROR'],
        'XENSERVER_HOST_IS_SLAVE_ERROR' => Xphp::$_lang['WEB_ERROR_XENSERVER_HOST_IS_SLAVE_ERROR'],
        'XENSERVER_HOST_EMPTY_ERROR' => Xphp::$_lang['WEB_ERROR_XENSERVER_HOST_EMPTY_ERROR'],
        'XENSERVER_HOST_NOT_FOUND_ERROR' => Xphp::$_lang['WEB_ERROR_XENSERVER_HOST_NOT_FOUND_ERROR'],
        'XENSERVER_TRANSPORT_FAULT_ERROR' => Xphp::$_lang['WEB_ERROR_XENSERVER_TRANSPORT_FAULT_ERROR'],
        'XENSERVER_FETCH_ALL_HOST_ERROR' => Xphp::$_lang['WEB_ERROR_XENSERVER_FETCH_ALL_HOST_ERROR'],
        'XENSERVER_FETCH_ALL_VM_ERROR' => Xphp::$_lang['WEB_ERROR_XENSERVER_FETCH_ALL_VM_ERROR'],
        'XENSERVER_FETCH_ALL_SR_ERROR' => Xphp::$_lang['WEB_ERROR_XENSERVER_FETCH_ALL_SR_ERROR'],
        'XENSERVER_HANDLE_INVALID_ERROR' => Xphp::$_lang['WEB_ERROR_XENSERVER_HANDLE_INVALID_ERROR'],
        'XENSERVER_VM_NOT_RUNNING_ERROR' => Xphp::$_lang['WEB_ERROR_XENSERVER_VM_NOT_RUNNING_ERROR'],
        'XENSERVER_VM_NOT_HALTED_ERROR' => Xphp::$_lang['WEB_ERROR_XENSERVER_VM_NOT_HALTED_ERROR'],
        'XENSERVER_INVALID_UUID_ERROR' => Xphp::$_lang['WEB_ERROR_XENSERVER_INVALID_UUID_ERROR'],
        'XENSERVER_AUTH_FAILED_ERROR' => Xphp::$_lang['WEB_ERROR_XENSERVER_AUTH_FAILED_ERROR'],

        'XENSERVER_VBD_NOT_DISK_ERROR' => Xphp::$_lang['WEB_ERROR_XENSERVER_VBD_NOT_DISK_ERROR'],
        'XENSERVER_VDI_IS_NOT_AVAILABLE_ERROR' => Xphp::$_lang['WEB_ERROR_XENSERVER_VDI_IS_NOT_AVAILABLE_ERROR'],
        'XENSERVER_VDI_INFO_CHANGE_ERROR' => Xphp::$_lang['WEB_ERROR_XENSERVER_VDI_INFO_CHANGE_ERROR'],
        'XENSERVER_VDI_IS_NOT_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_XENSERVER_VDI_IS_NOT_EXIST_ERROR'],
        'XENSERVER_BACKUP_CONTAINER_FULL_WARRN' => Xphp::$_lang['WEB_ERROR_XENSERVER_BACKUP_CONTAINER_FULL_WARRN'],
        'XENSERVER_SR_NOT_FOUND_ERROR' => Xphp::$_lang['WEB_ERROR_XENSERVER_SR_NOT_FOUND_ERROR'],
        'XENSERVER_VM_CONFIG_NOT_MATCH_WARN' => Xphp::$_lang['WEB_ERROR_XENSERVER_VM_CONFIG_NOT_MATCH_WARN'],
        'XENSERVER_CREATE_SNAPSHOT_ERROR' => Xphp::$_lang['WEB_ERROR_XENSERVER_CREATE_SNAPSHOT_ERROR'],

        'XENSERVER_COMPARE_VDI_IS_NOT_FOUND_ERROR' => Xphp::$_lang['WEB_ERROR_XENSERVER_COMPARE_VDI_IS_NOT_FOUND_ERROR'],
        'XENSERVER_HOST_FREE_MEMORY_NOT_ENOUGH_ERROR' => Xphp::$_lang['WEB_ERROR_XENSERVER_HOST_FREE_MEMORY_NOT_ENOUGH_ERROR'],
        'XENSERVER_HOST_RESOURCE_NOT_ENOUGH_ERROR' => Xphp::$_lang['WEB_ERROR_XENSERVER_HOST_RESOURCE_NOT_ENOUGH_ERROR'],
        'XENSERVER_CONNECT_TO_VXEFS_PROCESS_ERROR' => Xphp::$_lang['WEB_ERROR_XENSERVER_CONNECT_TO_VXEFS_PROCESS_ERROR'],
        'XENSERVER_SR_PLUG_ERROR' => Xphp::$_lang['WEB_ERROR_XENSERVER_SR_PLUG_ERROR'],
        'XENSERVER_DEPEND_TIMEPOINT_NOT_EXIST' => Xphp::$_lang['WEB_ERROR_XENSERVER_DEPEND_TIMEPOINT_NOT_EXIST'],
        'XENSERVER_BACKUP_LEVEL_CHANGE_WARN' => Xphp::$_lang['WEB_ERROR_XENSERVER_BACKUP_LEVEL_CHANGE_WARN'],


        'VM_NOT_SUPPORT_HYPERVISOR_ERROR' => Xphp::$_lang['WEB_ERROR_VM_NOT_SUPPORT_HYPERVISOR_ERROR'],
        'VM_HOST_ALREADY_EXIST_WARN' => Xphp::$_lang['WEB_ERROR_VM_HOST_ALREADY_EXIST_WARN'],
        'VM_HOST_ALREADY_EXIST_IN_VCENTER_ERROR' => Xphp::$_lang['WEB_ERROR_VM_HOST_ALREADY_EXIST_IN_VCENTER_ERROR'],
        'VM_MACHINE_LIST_NOT_FOUND_ERROR' => Xphp::$_lang['WEB_ERROR_VM_MACHINE_LIST_NOT_FOUND_ERROR'],
        'VM_MACHINE_NOT_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_VM_MACHINE_NOT_EXIST_ERROR'],
        'VMWARE_RESTRICTED_VERSION_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_RESTRICTED_VERSION_ERROR'],


        //TODO 提取语言包
        'VMWARE_ADD_FLOPPY_DEVICE_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_ADD_FLOPPY_DEVICE_ERROR'],
        'VMWARE_UPLOAD_DISK_FILE_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_UPLOAD_DISK_FILE_ERROR'],
        'VMWARE_ADD_VIRTUAL_SWITCH_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_ADD_VIRTUAL_SWITCH_ERROR'],
        'VMWARE_UPDATE_VIRTUAL_SWITCH_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_UPDATE_VIRTUAL_SWITCH_ERROR'],
        'VMWARE_ADD_PORT_GROUP_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_ADD_PORT_GROUP_ERROR'],
        'VMWARE_UPDATE_PORT_GROUP_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_UPDATE_PORT_GROUP_ERROR'],
        'VMWARE_REMOVE_VIRTUAL_SWITCH_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_REMOVE_VIRTUAL_SWITCH_ERROR'],
        'VMWARE_VIRTUAL_SWITCH_NOT_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_VIRTUAL_SWITCH_NOT_EXIST_ERROR'],
        'VMWARE_VIRTUAL_SWITCH_ALREADY_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_VIRTUAL_SWITCH_ALREADY_EXIST_ERROR'],
        'VMWARE_PORT_GROUP_NOT_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_PORT_GROUP_NOT_EXIST_ERROR'],
        'VMWARE_PORT_GROUP_ALREADY_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_PORT_GROUP_ALREADY_EXIST_ERROR'],
        'VMWARE_DEPLOY_ORCH_NETWORK_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_DEPLOY_ORCH_NETWORK_ERROR'],
        'VMWARE_DEPLOY_PROXY_VM_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_DEPLOY_PROXY_VM_ERROR'],
        'VMWARE_GET_DATACENTER_NAME_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_GET_DATACENTER_NAME_ERROR'],
        'VMWARE_GET_PROXY_INFO_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_GET_PROXY_INFO_ERROR'],
        'VMWARE_DELETE_ORCH_PROXY_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_DELETE_ORCH_PROXY_ERROR'],
        'VMWARE_CREATE_FLOPPY_FILE_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_CREATE_FLOPPY_FILE_ERROR'],

        'VMWARE_BUILD_ORCH_VM_LIST_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_BUILD_ORCH_VM_LIST_ERROR'],
        'VMWARE_ADD_VIRTUAL_IP_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_ADD_VIRTUAL_IP_ERROR'],
        'VMWARE_DEL_VIRTUAL_IP_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_DEL_VIRTUAL_IP_ERROR'],
        'VMWARE_ADD_ROUTE_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_ADD_ROUTE_ERROR'],
        'VMWARE_DEL_ROUTE_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_DEL_ROUTE_ERROR'],
        'VMWARE_ADD_DISK_FOR_VM_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_ADD_DISK_FOR_VM_ERROR'],
        'VMWARE_NOT_SUPPORT_BACKUP_TEMPLATE_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_NOT_SUPPORT_BACKUP_TEMPLATE_ERROR'],
        'VMWARE_GET_THUMBPRINT_OF_VC_OR_HOST_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_GET_THUMBPRINT_OF_VC_OR_HOST_ERROR'],
        'VMWARE_LONG_TIME_RUNNING_INSTANT_WARN' => Xphp::$_lang['WEB_ERROR_VMWARE_LONG_TIME_RUNNING_INSTANT_WARN'],

        'XHERE_CREATE_SNAPSHOT_FAILED_ERROR' => Xphp::$_lang['WEB_ERROR_XHERE_CREATE_SNAPSHOT_FAILED_ERROR'],
        'XHERE_CREATE_IMAGE_FAILED_ERROR' => Xphp::$_lang['WEB_ERROR_XHERE_CREATE_IMAGE_FAILED_ERROR'],
        'XHERE_CREATE_VM_FAILED_ERROR' => Xphp::$_lang['WEB_ERROR_XHERE_CREATE_IMAGE_FAILED_ERROR'],
        'XHERE_DELETE_CDROM_FAILED_ERROR' => Xphp::$_lang['WEB_ERROR_XHERE_DELETE_CDROM_FAILED_ERROR'],
        'XHERE_CREATE_VOLUME_FAILED_ERROR' => Xphp::$_lang['WEB_ERROR_XHERE_CREATE_VOLUME_FAILED_ERROR'],
        'XHERE_ATTACH_VOLUME_FAILED_ERROR' => Xphp::$_lang['WEB_ERROR_XHERE_ATTACH_VOLUME_FAILED_ERROR'],

        'PUBLIC_CLOUD_SCAN_VCENTER_ERROR' => Xphp::$_lang['WEB_ERROR_PUBLIC_CLOUD_SCAN_VCENTER_ERROR'],
        'PUBLIC_CLOUD_VM_NOT_FIND_VM_ERROR' => Xphp::$_lang['WEB_ERROR_PUBLIC_CLOUD_VM_NOT_FIND_VM_ERROR'],
        'PUBLIC_CLOUD_BUILD_RECOVERY_VM_LIST_ERROR' => Xphp::$_lang['WEB_ERROR_PUBLIC_CLOUD_BUILD_RECOVERY_VM_LIST_ERROR'],
        'PUBLIC_CLOUD_NOT_SUPPORT_BACKUP_LEVEL_ERROR' => Xphp::$_lang['WEB_ERROR_PUBLIC_CLOUD_NOT_SUPPORT_BACKUP_LEVEL_ERROR'],
        'PUBLIC_CLOUD_SAVE_SELF_EXPLAN_FILE_ERROR' => Xphp::$_lang['WEB_ERROR_PUBLIC_CLOUD_SAVE_SELF_EXPLAN_FILE_ERROR'],
        'PUBLIC_CLOUD_BUILD_BACKUP_VM_LIST_ERROR' => Xphp::$_lang['WEB_ERROR_PUBLIC_CLOUD_BUILD_BACKUP_VM_LIST_ERROR'],
        'PUBLIC_CLOUD_BACKUP_VM_IS_TERMINATED' => Xphp::$_lang['WEB_ERROR_PUBLIC_CLOUD_BACKUP_VM_IS_TERMINATED'],
        'PUBLIC_CLOUD_WRITE_BITMAP_FILE_ERROR' => Xphp::$_lang['WEB_ERROR_PUBLIC_CLOUD_WRITE_BITMAP_FILE_ERROR'],
        'PUBLIC_CLOUD_READ_REMOTE_DISK_ERROR' => Xphp::$_lang['WEB_ERROR_PUBLIC_CLOUD_READ_REMOTE_DISK_ERROR'],
        'PUBLIC_CLOUD_WRITE_REMOTE_DISK_ERROR' => Xphp::$_lang['WEB_ERROR_PUBLIC_CLOUD_WRITE_REMOTE_DISK_ERROR'],
        'PUBLIC_CLOUD_CLOSE_DISK_ERROR' => Xphp::$_lang['WEB_ERROR_PUBLIC_CLOUD_CLOSE_DISK_ERROR'],
        'PUBLIC_CLOUD_READ_LATEST_BITMAP_FILE_ERROR' => Xphp::$_lang['WEB_ERROR_PUBLIC_CLOUD_READ_LATEST_BITMAP_FILE_ERROR'],
        'PUBLIC_CLOUD_RECOVERY_TIMEPOINT_NOT_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_PUBLIC_CLOUD_RECOVERY_TIMEPOINT_NOT_EXIST_ERROR'],
        'PUBLIC_CLOUD_PARSER_VM_CONFIG_ERROR' => Xphp::$_lang['WEB_ERROR_PUBLIC_CLOUD_PARSER_VM_CONFIG_ERROR'],
        'PUBLIC_CLOUD_GET_RECOVERY_VALID_DATA_SIZE_ERROR' => Xphp::$_lang['WEB_ERROR_PUBLIC_CLOUD_GET_RECOVERY_VALID_DATA_SIZE_ERROR'],
        'PUBLIC_CLOUD_FIND_BACKUP_FILE_ID_ERROR' => Xphp::$_lang['WEB_ERROR_PUBLIC_CLOUD_FIND_BACKUP_FILE_ID_ERROR'],
        'PUBLIC_CLOUD_LATEST_TIMEPOINT_NOT_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_PUBLIC_CLOUD_LATEST_TIMEPOINT_NOT_EXIST_ERROR'],
        'PUBLIC_CLOUD_DISK_NUM_CHANGED_ERROR' => Xphp::$_lang['WEB_ERROR_PUBLIC_CLOUD_DISK_NUM_CHANGED_ERROR'],
        'PUBLIC_CLOUD_DISK_CHANGED_ERROR' => Xphp::$_lang['WEB_ERROR_PUBLIC_CLOUD_DISK_CHANGED_ERROR'],
        'PUBLIC_CLOUD_DEDUPE_BLOCK_SIZE_ERROR' => Xphp::$_lang['WEB_ERROR_PUBLIC_CLOUD_DEDUPE_BLOCK_SIZE_ERROR'],
        'PUBLIC_CLOUD_DIFF_AND_INC_CAN_NOT_COEXIST_ERROR' => Xphp::$_lang['WEB_ERROR_PUBLIC_CLOUD_DIFF_AND_INC_CAN_NOT_COEXIST_ERROR'],
        'PUBLIC_CLOUD_VM_ALREADY_POWEROFF_ERROR' => Xphp::$_lang['WEB_ERROR_PUBLIC_CLOUD_VM_ALREADY_POWEROFF_ERROR'],
        'PUBLIC_CLOUD_VM_ALREADY_POWERON_ERROR' => Xphp::$_lang['WEB_ERROR_PUBLIC_CLOUD_VM_ALREADY_POWERON_ERROR'],
        'PUBLIC_CLOUD_VM_NOT_SUSPEND_ERROR' => Xphp::$_lang['WEB_ERROR_PUBLIC_CLOUD_VM_NOT_SUSPEND_ERROR'],
        'PUBLIC_CLOUD_VM_NOT_POWERON_ERROR' => Xphp::$_lang['WEB_ERROR_PUBLIC_CLOUD_VM_NOT_POWERON_ERROR'],
        'PUBLIC_CLOUD_HAS_DIFF_TIMEPOINT_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_PUBLIC_CLOUD_HAS_DIFF_TIMEPOINT_EXIST_ERROR'],
        'PUBLIC_CLOUD_CREATE_BACKUP_DIR_ERROR' => Xphp::$_lang['WEB_ERROR_PUBLIC_CLOUD_CREATE_BACKUP_DIR_ERROR'],
        'PUBLIC_CLOUD_GET_AVALID_DATE_FILE_ERROR' => Xphp::$_lang['WEB_ERROR_PUBLIC_CLOUD_GET_AVALID_DATE_FILE_ERROR'],
        'PUBLIC_CLOUD_WRITE_METADATA_FILE_ERROR' => Xphp::$_lang['WEB_ERROR_PUBLIC_CLOUD_WRITE_METADATA_FILE_ERROR'],
        'PUBLIC_CLOUD_GET_TOTAL_BACKUP_SIZE_ERROR' => Xphp::$_lang['WEB_ERROR_PUBLIC_CLOUD_GET_TOTAL_BACKUP_SIZE_ERROR'],
        'PUBLIC_CLOUD_API_ERROR' => Xphp::$_lang['WEB_ERROR_PUBLIC_CLOUD_API_ERROR'],
        'PUBLIC_CLOUD_REGION_VM_EMPTY' => Xphp::$_lang['WEB_ERROR_PUBLIC_CLOUD_REGION_VM_EMPTY'],
        'PUBLIC_CLOUD_EBS_DISK_EMPTY' => Xphp::$_lang['WEB_ERROR_PUBLIC_CLOUD_EBS_DISK_EMPTY'],
        'PUBLIC_CLOUD_EBS_CHANGE_DISK_EMPTY' => Xphp::$_lang['WEB_ERROR_PUBLIC_CLOUD_EBS_CHANGE_DISK_EMPTY'],
        'PUBLIC_CLOUD_INIT_API_ERROR' => Xphp::$_lang['WEB_ERROR_PUBLIC_CLOUD_INIT_API_ERROR'],
        'PUBLIC_CLOUD_GET_XML_INFO_ERROR' => Xphp::$_lang['WEB_ERROR_PUBLIC_CLOUD_GET_XML_INFO_ERROR'],
        'PUBLIC_CLOUD_AMI_NOT_EXIST' => Xphp::$_lang['WEB_ERROR_PUBLIC_CLOUD_AMI_NOT_EXIST'],
        'PUBLIC_CLOUD_SECURITY_EXIST' => Xphp::$_lang['WEB_ERROR_PUBLIC_CLOUD_SECURITY_EXIST'],
        'PUBLIC_CLOUD_DESCRIBE_INSTANCE_ERROR' => Xphp::$_lang['WEB_ERROR_PUBLIC_CLOUD_DESCRIBE_INSTANCE_ERROR'],
        'PUBLIC_CLOUD_GET_AVALID_DATA_BY_SNAPSHOT_ERROR' => Xphp::$_lang['WEB_ERROR_PUBLIC_CLOUD_GET_AVALID_DATA_BY_SNAPSHOT_ERROR'],
        'PUBLIC_CLOUD_CREATE_SNAPSHOT_ERROR' => Xphp::$_lang['WEB_ERROR_PUBLIC_CLOUD_CREATE_SNAPSHOT_ERROR'],
        'PUBLIC_CLOUD_DELETE_SNAPSHOT_ERROR' => Xphp::$_lang['WEB_ERROR_PUBLIC_CLOUD_DELETE_SNAPSHOT_ERROR'],
        'PUBLIC_CLOUD_DESCRIBE_SNAPSHOT_INFO_ERROR' => Xphp::$_lang['WEB_ERROR_PUBLIC_CLOUD_DESCRIBE_SNAPSHOT_INFO_ERROR'],
        'PUBLIC_CLOUD_GET_SOURCE_STATUS_ERROR' => Xphp::$_lang['WEB_ERROR_PUBLIC_CLOUD_GET_SOURCE_STATUS_ERROR'],
        'PUBLIC_CLOUD_ATTACH_VOLUME_ERROR' => Xphp::$_lang['WEB_ERROR_PUBLIC_CLOUD_ATTACH_VOLUME_ERROR'],
        'PUBLIC_CLOUD_CREATE_VOLUME_ERROR' => Xphp::$_lang['WEB_ERROR_PUBLIC_CLOUD_CREATE_VOLUME_ERROR'],
        'PUBLIC_CLOUD_DELETE_VOLUME_ERROR' => Xphp::$_lang['WEB_ERROR_PUBLIC_CLOUD_DELETE_VOLUME_ERROR'],
        'PUBLIC_CLOUD_DESCRIBE_VOLUME_INFO_ERROR' => Xphp::$_lang['WEB_ERROR_PUBLIC_CLOUD_DESCRIBE_VOLUME_INFO_ERROR'],
        'PUBLIC_CLOUD_DEATCH_VOLUME_ERROR' => Xphp::$_lang['WEB_ERROR_PUBLIC_CLOUD_DEATCH_VOLUME_ERROR'],
        'PUBLIC_CLOUD_DESCRIBE_AMI_INFO_ERROR' => Xphp::$_lang['WEB_ERROR_PUBLIC_CLOUD_DESCRIBE_AMI_INFO_ERROR'],
        'PUBLIC_CLOUD_GET_AMI_ERROR' => Xphp::$_lang['WEB_ERROR_PUBLIC_CLOUD_GET_AMI_ERROR'],
        'PUBLIC_CLOUD_GET_AVAILABOLITY_ZONE_ERROR' => Xphp::$_lang['WEB_ERROR_PUBLIC_CLOUD_GET_AVAILABOLITY_ZONE_ERROR'],
        'PUBLIC_CLOUD_GET_NETWORK_INFO_ERROR' => Xphp::$_lang['WEB_ERROR_PUBLIC_CLOUD_GET_NETWORK_INFO_ERROR'],
        'PUBLIC_CLOUD_START_INSTANCE_ERROR' => Xphp::$_lang['WEB_ERROR_PUBLIC_CLOUD_START_INSTANCE_ERROR'],
        'PUBLIC_CLOUD_STOP_INSTANCE_ERROR' => Xphp::$_lang['WEB_ERROR_PUBLIC_CLOUD_STOP_INSTANCE_ERROR'],
        'PUBLIC_CLOUD_TERMINATE_INSTANCE_ERROR' => Xphp::$_lang['WEB_ERROR_PUBLIC_CLOUD_TERMINATE_INSTANCE_ERROR'],
        'PUBLIC_CLOUD_GET_INSTANCE_TYPE_ERROR' => Xphp::$_lang['WEB_ERROR_PUBLIC_CLOUD_GET_INSTANCE_TYPE_ERROR'],
        'PUBLIC_CLOUD_GET_KET_PAIR_INFO_ERROR' => Xphp::$_lang['WEB_ERROR_PUBLIC_CLOUD_GET_KET_PAIR_INFO_ERROR'],
        'PUBLIC_CLOUD_DESCRIBE_SECURITY_ERROR' => Xphp::$_lang['WEB_ERROR_PUBLIC_CLOUD_DESCRIBE_SECURITY_ERROR'],
        'PUBLIC_CLOUD_CREATE_SECURITY_ERROR' => Xphp::$_lang['WEB_ERROR_PUBLIC_CLOUD_CREATE_SECURITY_ERROR'],
        'PUBLIC_CLOUD_DELETE_SECURITY_ERROR' => Xphp::$_lang['WEB_ERROR_PUBLIC_CLOUD_DELETE_SECURITY_ERROR'],
        'PUBLIC_CLOUD_AUTHORIZE_SECURITY_GROUP_INGRESS_ERROR' => Xphp::$_lang['WEB_ERROR_PUBLIC_CLOUD_AUTHORIZE_SECURITY_GROUP_INGRESS_ERROR'],
        'PUBLIC_CLOUD_GET_ACCOUNT_ID_ERROR' => Xphp::$_lang['WEB_ERROR_PUBLIC_CLOUD_GET_ACCOUNT_ID_ERROR'],
        'PUBLIC_CLOUD_GET_DISK_ENCRYPT_INFO_ERROR' => Xphp::$_lang['WEB_ERROR_PUBLIC_CLOUD_GET_DISK_ENCRYPT_INFO_ERROR'],
        'PUBLIC_CLOUD_RECOVERY_NOT_FIND_ROOT_DISK_ERROR' => Xphp::$_lang['WEB_ERROR_PUBLIC_CLOUD_RECOVERY_NOT_FIND_ROOT_DISK_ERROR'],
        'PUBLIC_CLOUD_GET_RECOVERY_DISK_SIZE_ERROR' => Xphp::$_lang['WEB_ERROR_PUBLIC_CLOUD_GET_RECOVERY_DISK_SIZE_ERROR'],
        'PUBLIC_CLOUD_VM_STATUS_NOT_NEED_TO_BACKUP_ERROR' => Xphp::$_lang['WEB_ERROR_PUBLIC_CLOUD_VM_STATUS_NOT_NEED_TO_BACKUP_ERROR'],
        'PUBLIC_CLLOUD_BACKUP_VM_DISK_OUT_LIMIT_ERROR' => Xphp::$_lang['WEB_ERROR_PUBLIC_CLLOUD_BACKUP_VM_DISK_OUT_LIMIT_ERROR'],

        'VMWARE_VIRTUAL_ENVIRMENT_OFFLINE_ERROR' => Xphp::$_lang['WEB_LOG_TASK_DESC_KEY_VIRTUAL_ENVIRONMENT_OFFLINE_ERROR'],
        'VM_DISK_IS_CHANGED_ERROR' => Xphp::$_lang['WEB_LOG_TASK_DESC_KEY_VM_DISK_IS_CHANGED_ERROR'],

        'VM_ICS_KVM_SYSTEM_20055_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_SYSTEM_20055_ERROR'],
        'VM_ICS_KVM_NM_20102_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_NM_20102_ERROR'],
        'VM_ICS_KVM_TASK_201001_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_TASK_201001_ERROR'],
        'VM_ICS_KVM_TASK_201093_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_TASK_201093_ERROR'],
        'VM_ICS_KVM_TASK_210565_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_TASK_210565_ERROR'],
        'VM_ICS_KVM_TASK_322024_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_TASK_322024_ERROR'],

        'KVM_OPENSTACK_NOT_SUPPORT_CONSISTENCY_SNAPSHOT_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_OPENSTACK_NOT_SUPPORT_CONSISTENCY_SNAPSHOT_ERROR'],

        'VM_HUAWEI_CBR_OP_NOT_FOUND_ERROR' => Xphp::$_lang['WEB_ERROR_VM_HUAWEI_CBR_OP_NOT_FOUND_ERROR'],
        'VM_HUAWEI_CBR_SYNC_LIST_IS_EMPTY_ERROR' => Xphp::$_lang['WEB_ERROR_VM_HUAWEI_CBR_SYNC_LIST_IS_EMPTY_ERROR'],
        'VM_HUAWEI_CBR_SYNC_OBJECT_NOT_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_VM_HUAWEI_CBR_SYNC_OBJECT_NOT_EXIST_ERROR'],
        'VM_HUAWEI_CBR_SYNC_GRAIN_TYPE_NOT_SUPPORT_ERROR' => Xphp::$_lang['WEB_ERROR_VM_HUAWEI_CBR_SYNC_GRAIN_TYPE_NOT_SUPPORT_ERROR'],
        'VM_HUAWEI_CBR_SYNC_SOME_TIMEPOINT_ERROR' => Xphp::$_lang['WEB_ERROR_VM_HUAWEI_CBR_SYNC_SOME_TIMEPOINT_ERROR'],

        'VM_ICS_KVM_TASK_210213_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_TASK_210213_ERROR'],
        'VM_ICS_KVM_TASK_109013_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_TASK_109013_ERROR'],
        'VM_ICS_KVM_TASK_210025_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_TASK_210025_ERROR'],
        'VM_ICS_KVM_TASK_20020_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_TASK_20020_ERROR'],
        'VM_ICS_KVM_TASK_107012_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_TASK_107012_ERROR'],

        'KVM_SANGFOR_GET_API_VERSION_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_SANGFOR_GET_API_VERSION_ERROR'],
        'KVM_SANGFOR_GET_TASK_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_SANGFOR_GET_TASK_ERROR'],
        'KVM_SANGFOR_GET_RESOURE_POOL_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_SANGFOR_GET_RESOURE_POOL_ERROR'],
        'KVM_SANGFOR_GET_HOST_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_SANGFOR_GET_HOST_ERROR'],
        'KVM_SANGFOR_GET_VM_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_SANGFOR_GET_VM_ERROR'],
        'KVM_SANGFOR_GET_SNAPSHOT_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_SANGFOR_GET_SNAPSHOT_ERROR'],
        'KVM_SANGFOR_GET_VM_GROUP_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_SANGFOR_GET_VM_GROUP_ERROR'],
        'KVM_SANGFOR_GET_STORAGE_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_SANGFOR_GET_STORAGE_ERROR'],
        'KVM_SANGFOR_DELETE_STORAGE_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_SANGFOR_DELETE_STORAGE_ERROR'],
        'KVM_SANGFOR_GET_NETWORK_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_SANGFOR_GET_NETWORK_ERROR'],
        'KVM_SANGFOR_NEED_CLEAN_ALL_SNAPSHOT_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_SANGFOR_NEED_CLEAN_ALL_SNAPSHOT_ERROR'],
        'KVM_SANGFOR_CREATE_SNAPSHOT_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_SANGFOR_CREATE_SNAPSHOT_ERROR'],
        'KVM_SANGFOR_DELETE_SNAPSHOT_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_SANGFOR_DELETE_SNAPSHOT_ERROR'],
        'KVM_SANGFOR_DELETE_VM_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_SANGFOR_DELETE_VM_ERROR'],
        'KVM_SANGFOR_POWEROFF_VM_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_SANGFOR_POWEROFF_VM_ERROR'],
        'KVM_SANGFOR_POWERON_VM_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_SANGFOR_POWERON_VM_ERROR'],
        'KVM_SANGFOR_CREATE_STORAGE_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_SANGFOR_CREATE_STORAGE_ERROR'],
        'KVM_SANGFOR_CREATE_VM_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_SANGFOR_CREATE_VM_ERROR'],
        'KVM_SANGFOR_VM_IN_DISASTER_RECOVERY_PLAN_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_SANGFOR_VM_IN_DISASTER_RECOVERY_PLAN_ERROR'],
        'KVM_SANGFOR_CHECK_IS_SUPPORT_CBT_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_SANGFOR_CHECK_IS_SUPPORT_CBT_ERROR'],
        'KVM_SANGFOR_NOT_SUPPORT_CBT_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_SANGFOR_NOT_SUPPORT_CBT_ERROR'],
        'KVM_SANGFOR_GET_CBT_STATUS_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_SANGFOR_GET_CBT_STATUS_ERROR'],
        'KVM_SANGFOR_UPDATE_CBT_STATUS_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_SANGFOR_UPDATE_CBT_STATUS_ERROR'],
        'KVM_SANGFOR_CREATE_CBT_RESOURCE_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_SANGFOR_CREATE_CBT_RESOURCE_ERROR'],
        'KVM_SANGFOR_GET_CBT_RESOURCE_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_SANGFOR_GET_CBT_RESOURCE_ERROR'],
        'KVM_SANGFOR_DELETE_CBT_RESOURCE_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_SANGFOR_DELETE_CBT_RESOURCE_ERROR'],
        'KVM_SANGFOR_UPDATE_CBT_RESOURCE_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_SANGFOR_UPDATE_CBT_RESOURCE_ERROR'],
        'KVM_SANGFOR_INIT_SDK_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_SANGFOR_INIT_SDK_ERROR'],
        'KVM_SANGFOR_CONNECT_PLATFORM_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_SANGFOR_CONNECT_PLATFORM_ERROR'],
        'KVM_SANGFOR_DISCONNECT_PLATFORM_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_SANGFOR_DISCONNECT_PLATFORM_ERROR'],
        'KVM_SANGFOR_OPEN_DISK_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_SANGFOR_OPEN_DISK_ERROR'],
        'KVM_SANGFOR_GET_CBT_DIFF_BITMAP_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_SANGFOR_GET_CBT_DIFF_BITMAP_ERROR'],
        'KVM_SANGFOR_CBT_INVALID_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_SANGFOR_CBT_INVALID_ERROR'],
        'KVM_SANGFOR_NOT_SUITABLE_STORAGE_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_SANGFOR_NOT_SUITABLE_STORAGE_ERROR'],
        'KVM_SANGFOR_GET_CLUSTER_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_SANGFOR_GET_CLUSTER_ERROR'],
        'KVM_SANGFOR_REBASE_BACKING_FILE_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_SANGFOR_REBASE_BACKING_FILE_ERROR'],
        'KVM_SANGFOR_MERGE_BACKUP_DATA_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_SANGFOR_MERGE_BACKUP_DATA_ERROR'],
        'KVM_SANGFOR_UPDATE_VM_CONFIG_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_SANGFOR_UPDATE_VM_CONFIG_ERROR'],

        'VM_NOT_SUPPORT_AUTH_TYPE_ERROR' => Xphp::$_lang['WEB_ERROR_VM_NOT_SUPPORT_AUTH_TYPE_ERROR'],
        'VM_NOT_SUPPORT_PLATFORM_TYPE_ERROR' => Xphp::$_lang['WEB_ERROR_VM_NOT_SUPPORT_PLATFORM_TYPE_ERROR'],
        'VM_CLOSE_DISK_ERROR' => Xphp::$_lang['WEB_ERROR_VM_CLOSE_DISK_ERROR'],

        'KVM_SANGFOR_CALCULATE_HMAC_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_SANGFOR_CALCULATE_HMAC_ERROR'],
        'KVM_SANGFOR_DISK_NUM_REACHED_MAX_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_SANGFOR_DISK_NUM_REACHED_MAX_ERROR'],
        'KVM_SANGFOR_CBT_CHANGE_RANGE_EXCEED_BOUND_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_SANGFOR_CBT_CHANGE_RANGE_EXCEED_BOUND_ERROR'],
        'KVM_SANGFOR_CBT_INVALID_HANDLE_COUNT_REACHED_MAX_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_SANGFOR_CBT_INVALID_HANDLE_COUNT_REACHED_MAX_ERROR'],
        'KVM_SANGFOR_VM_TYPE_NOT_SUPPORT_FOR_BACKUP_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_SANGFOR_VM_TYPE_NOT_SUPPORT_FOR_BACKUP_ERROR'],

        'VM_ICS_KVM_TASK_210126_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_TASK_210126_ERROR'],
        'VMWARE_SET_VM_BOOT_OPTION_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_SET_VM_BOOT_OPTION_ERROR'],
        'PUBLIC_CLOUD_DEPENDENT_SNAPSHOT_DOES_NOT_EXIST' => Xphp::$_lang['WEB_ERROR_PUBLIC_CLOUD_DEPENDENT_SNAPSHOT_DOES_NOT_EXIST'],
        'PUBLIC_CLOUD_LOCAL_TIME_ERROR' => Xphp::$_lang['WEB_ERROR_PUBLIC_CLOUD_LOCAL_TIME_ERROR'],
        'PUBLIC_CLOUD_TRY_TO_GET_NTPTIME_ERROR' => Xphp::$_lang['WEB_ERROR_PUBLIC_CLOUD_TRY_TO_GET_NTPTIME_ERROR'],
        'PUBLIC_CLOUD_RECOVERY_NOT_ROOT_DISK_ERROR' => Xphp::$_lang['WEB_ERROR_PUBLIC_CLOUD_RECOVERY_NOT_ROOT_DISK_ERROR'],
        'PUBLIC_CLOUD_BACKUP_VM_NOT_CHOSE_DISKS' => Xphp::$_lang['WEB_ERROR_PUBLIC_CLOUD_BACKUP_VM_NOT_CHOSE_DISKS'],
        'PUBLIC_CLOUD_RECOVERY_INSTACE_TYPE_NOT_SUPPORT_THE_CONFIG' => Xphp::$_lang['WEB_ERROR_PUBLIC_CLOUD_RECOVERY_INSTACE_TYPE_NOT_SUPPORT_THE_CONFIG'],
        'VM_VIRTUAL_LAB_PARAM_NOT_CHANGE_WARN' => Xphp::$_lang['WEB_ERROR_VM_VIRTUAL_LAB_PARAM_NOT_CHANGE_WARN'],
        'KVM_SANGFOR_SCP_IMAGE_ID_EMPTY_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_SANGFOR_SCP_IMAGE_ID_EMPTY_ERROR'],
        'PUBLIC_CLOUD_BACKUP_LEVEL_CHANGE' => Xphp::$_lang['WEB_ERROR_PUBLIC_CLOUD_BACKUP_LEVEL_CHANGE'],

        'KVM_H3C_CAS_CVD_ENABLE_VM_OPERATION_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_H3C_CAS_CVD_ENABLE_VM_OPERATION_ERROR'],
        'KVM_H3C_CAS_CVD_FORBID_VM_OPERATION_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_H3C_CAS_CVD_FORBID_VM_OPERATION_ERROR'],
        'PUBLIC_CLOUD_DB_VCENTER_ALREADY_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_PUBLIC_CLOUD_DB_VCENTER_ALREADY_EXIST_ERROR'],
        'PUBLIC_CLOUD_LOGIN_AK_SK_ERROR' => Xphp::$_lang['WEB_ERROR_PUBLIC_CLOUD_LOGIN_AK_SK_ERROR'],
        'KVM_SANGFOR_SCP_VM_IS_POWER_ON_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_SANGFOR_SCP_VM_IS_POWER_ON_ERROR'],


        'PUBLIC_CLOUD_SHARING_IMAGE_QUOTA_FULL_ERROR' => Xphp::$_lang['WEB_ERROR_PUBLIC_CLOUD_SHARING_IMAGE_QUOTA_FULL_ERROR'],
        'KVM_LENOVO_AIO_GET_VERSION_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LENOVO_AIO_GET_VERSION_ERROR'],
        'KVM_LENOVO_AIO_GET_VM_CONFIG_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LENOVO_AIO_GET_VM_CONFIG_ERROR'],
        'KVM_LENOVO_AIO_GET_VM_STATUS_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LENOVO_AIO_GET_VM_STATUS_ERROR'],
        'KVM_LENOVO_AIO_CREATE_SNAPSHOT_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LENOVO_AIO_CREATE_SNAPSHOT_ERROR'],
        'KVM_LENOVO_AIO_UPDATE_SNAPSHOT_PATH_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LENOVO_AIO_UPDATE_SNAPSHOT_PATH_ERROR'],
        'KVM_LENOVO_AIO_DELETE_SNAPSHOT_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LENOVO_AIO_DELETE_SNAPSHOT_ERROR'],
        'KVM_LENOVO_AIO_POWEROFF_VM_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LENOVO_AIO_POWEROFF_VM_ERROR'],
        'KVM_LENOVO_AIO_POWERON_VM_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LENOVO_AIO_POWERON_VM_ERROR'],
        'KVM_LENOVO_AIO_CREATE_VM_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LENOVO_AIO_CREATE_VM_ERROR'],
        'KVM_LENOVO_AIO_DELETE_VM_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LENOVO_AIO_DELETE_VM_ERROR'],
        'KVM_LENOVO_AIO_GET_VM_SNAPSHOT_LIST_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LENOVO_AIO_GET_VM_SNAPSHOT_LIST_ERROR'],
        'KVM_LENOVO_AIO_CBT_CHANGE_RANGE_EXCEED_BOUND_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LENOVO_AIO_CBT_CHANGE_RANGE_EXCEED_BOUND_ERROR'],
        'KVM_LENOVO_AIO_CTREATE_NFS_STORAGE_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LENOVO_AIO_CTREATE_NFS_STORAGE_ERROR'],
        'KVM_LENOVO_AIO_GET_HOST_LIST_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LENOVO_AIO_GET_HOST_LIST_ERROR'],
        'KVM_LENOVO_GET_CLUSTER_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LENOVO_GET_CLUSTER_ERROR'],
        'KVM_LENOVO_GET_VM_HARDWARE_INFO_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LENOVO_GET_VM_HARDWARE_INFO_ERROR'],
        'KVM_LENOVO_GET_VM_DISKS_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LENOVO_GET_VM_DISKS_ERROR'],
        'KVM_LENOVO_GET_VM_LIST_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LENOVO_GET_VM_LIST_ERROR'],
        'KVM_LENOVO_CREATE_VM_SNAPSHOT_BITMAP_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LENOVO_CREATE_VM_SNAPSHOT_BITMAP_ERROR'],
        'KVM_LENOVO_DELETE_VM_SNAPSHOT_BITMAP_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LENOVO_DELETE_VM_SNAPSHOT_BITMAP_ERROR'],
        'KVM_LENOVO_GET_CBT_DIFF_BITMAP_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LENOVO_GET_CBT_DIFF_BITMAP_ERROR'],
        'KVM_LENOVO_UMOUNT_STORAGE_IN_HOST_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LENOVO_UMOUNT_STORAGE_IN_HOST_ERROR'],
        'KVM_LENOVO_MOUNT_STORAGE_IN_HOST_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LENOVO_MOUNT_STORAGE_IN_HOST_ERROR'],
        'KVM_LENOVO_GET_TASK_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LENOVO_GET_TASK_ERROR'],
        'KVM_LENOVO_GET_CLUSTER_PORT_GROUP_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LENOVO_GET_CLUSTER_PORT_GROUP_ERROR'],
        'KVM_LENOVO_GET_STORAGE_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LENOVO_GET_STORAGE_ERROR'],
        'KVM_LENOVO_DELETE_STORAGE_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LENOVO_DELETE_STORAGE_ERROR'],
        'KVM_LENOVO_CREATE_NBD_DEVICE_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LENOVO_CREATE_NBD_DEVICE_ERROR'],
        'KVM_LENOVO_DELETE_NBD_DEVICE_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LENOVO_DELETE_NBD_DEVICE_ERROR'],
        'KVM_LENOVO_CREATE_NBD_KIT_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LENOVO_CREATE_NBD_KIT_ERROR'],
        'KVM_LENOVO_DELETE_NBD_KIT_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LENOVO_DELETE_NBD_KIT_ERROR'],
        'KVM_ICS_VVDK_GET_LUKS_DISK_HEADER_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_ICS_VVDK_GET_LUKS_DISK_HEADER_ERROR'],
        'KVM_ICS_VVDK_SET_LUKS_DISK_HEADER_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_ICS_VVDK_SET_LUKS_DISK_HEADER_ERROR'],
        'KVM_H3C_CAS_CVD_CBT_CHANGE_RANGE_EXCEED_BOUND_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_H3C_CAS_CVD_CBT_CHANGE_RANGE_EXCEED_BOUND_ERROR'],
        'KVM_H3C_CAS_CVD_DELETE_CBT_RESOURCE_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_H3C_CAS_CVD_DELETE_CBT_RESOURCE_ERROR'],
        'KVM_H3C_CAS_CVD_GET_CBT_STATUS_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_H3C_CAS_CVD_GET_CBT_STATUS_ERROR'],
        'KVM_H3C_CAS_CVD_CHECK_CBT_INVALID_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_H3C_CAS_CVD_CHECK_CBT_INVALID_ERROR'],
        'KVM_H3C_CAS_CVD_CHECK_VM_DISK_BASE_IMAGE_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_H3C_CAS_CVD_CHECK_VM_DISK_BASE_IMAGE_ERROR'],
        'KVM_H3C_CAS_CVD_GET_CBT_CHANEGE_BLOCK_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_H3C_CAS_CVD_GET_CBT_CHANEGE_BLOCK_ERROR'],
        'KVM_H3C_CAS_CVD_GET_CBT_VM_BITMAP_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_H3C_CAS_CVD_GET_CBT_VM_BITMAP_ERROR'],
        'VMWARE_ASYNC_READ_REMOTE_DISK_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_ASYNC_READ_REMOTE_DISK_ERROR'],
        'VMWARE_ASYNC_WRITE_REMOTE_DISK_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_ASYNC_WRITE_REMOTE_DISK_ERROR'],
        'VMWARE_SCAN_VCENTER_SKIP_WARN' => Xphp::$_lang['WEB_ERROR_VMWARE_SCAN_VCENTER_SKIP_WARN'],
        'VM_CONTAIN_INSTANT_RECOVERY_AND_OTHER_DISKS' => Xphp::$_lang['WEB_ERROR_VM_CONTAIN_INSTANT_RECOVERY_AND_OTHER_DISKS'],
        'VM_ICS_KVM_TASK_210526_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_TASK_210526_ERROR'],
        'VM_ICS_KVM_TASK_210765_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_TASK_210765_ERROR'],
        'VM_FC_KVM_PUB_10100110_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_PUB_10100110_ERROR'],
        'KVM_OPENSTACK_GET_PRODUCT_ID_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_OPENSTACK_GET_PRODUCT_ID_ERROR'],
        'KVM_OPENSTACK_GET_ORDER_RESOURCE_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_OPENSTACK_GET_ORDER_RESOURCE_ERROR'],
        'KVM_OPENSTACK_SUBSCRIPTION_TIMEOUT_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_OPENSTACK_SUBSCRIPTION_TIMEOUT_ERROR'],
        'KVM_OPENSTACK_SUBSCRIPTION_FAILED_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_OPENSTACK_SUBSCRIPTION_FAILED_ERROR'],
        'VM_THREAD_NUM_INFO_INVALID_ERROR' => Xphp::$_lang['WEB_ERROR_VM_THREAD_NUM_INFO_INVALID_ERROR'],
        'KVM_VOLCANO_VESTACK_GET_CLUSTER_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_VOLCANO_VESTACK_GET_CLUSTER_ERROR'],
        'KVM_VOLCANO_VESTACK_GET_HOST_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_VOLCANO_VESTACK_GET_HOST_ERROR'],
        'KVM_VOLCANO_VESTACK_GET_INSTANCE_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_VOLCANO_VESTACK_GET_INSTANCE_ERROR'],
        'KVM_VOLCANO_VESTACK_DELETE_INSTANCE_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_VOLCANO_VESTACK_DELETE_INSTANCE_ERROR'],
        'KVM_VOLCANO_VESTACK_STOP_INSTANCE_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_VOLCANO_VESTACK_STOP_INSTANCE_ERROR'],
        'KVM_VOLCANO_VESTACK_START_INSTANCE_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_VOLCANO_VESTACK_START_INSTANCE_ERROR'],
        'KVM_VOLCANO_VESTACK_GET_SNAPSHOT_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_VOLCANO_VESTACK_GET_SNAPSHOT_ERROR'],
        'KVM_VOLCANO_VESTACK_CREATE_SNAPSHOT_GROUP_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_VOLCANO_VESTACK_CREATE_SNAPSHOT_GROUP_ERROR'],
        'KVM_VOLCANO_VESTACK_DELETE_SNAPSHOT_GROUP_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_VOLCANO_VESTACK_DELETE_SNAPSHOT_GROUP_ERROR'],
        'KVM_VOLCANO_VESTACK_GET_SNAPSHOT_GROUP_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_VOLCANO_VESTACK_GET_SNAPSHOT_GROUP_ERROR'],
        'KVM_VOLCANO_VESTACK_DELETE_SNAPSHOT_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_VOLCANO_VESTACK_DELETE_SNAPSHOT_ERROR'],
        'KVM_VOLCANO_VESTACK_CREATE_SNAPSHOT_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_VOLCANO_VESTACK_CREATE_SNAPSHOT_ERROR'],
        'KVM_VOLCANO_VESTACK_CREATE_VOLUME_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_VOLCANO_VESTACK_CREATE_VOLUME_ERROR'],
        'KVM_VOLCANO_VESTACK_MODIFY_VOLUME_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_VOLCANO_VESTACK_MODIFY_VOLUME_ERROR'],
        'KVM_VOLCANO_VESTACK_DELETE_VOLUME_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_VOLCANO_VESTACK_DELETE_VOLUME_ERROR'],
        'KVM_VOLCANO_VESTACK_ATTACH_VOLUME_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_VOLCANO_VESTACK_ATTACH_VOLUME_ERROR'],
        'KVM_VOLCANO_VESTACK_DETACH_VOLUME_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_VOLCANO_VESTACK_DETACH_VOLUME_ERROR'],
        'KVM_VOLCANO_VESTACK_CREATE_VOLUME_FROM_SNAPSHOT_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_VOLCANO_VESTACK_CREATE_VOLUME_FROM_SNAPSHOT_ERROR'],
        'KVM_VOLCANO_VESTACK_GET_IMAGE_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_VOLCANO_VESTACK_GET_IMAGE_ERROR'],
        'KVM_VOLCANO_VESTACK_CREATE_INSTANCE_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_VOLCANO_VESTACK_CREATE_INSTANCE_ERROR'],
        'KVM_VOLCANO_VESTACK_GET_VOLUME_ERROR'  => Xphp::$_lang['WEB_ERROR_KVM_VOLCANO_VESTACK_GET_VOLUME_ERROR'],
        'KVM_VOLCANO_VESTACK_NOT_VALID_VOLUME_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_VOLCANO_VESTACK_NOT_VALID_VOLUME_ERROR'],
        'KVM_VOLCANO_VESTACK_GET_VPC_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_VOLCANO_VESTACK_GET_VPC_ERROR'],
        'KVM_VOLCANO_VESTACK_GET_SUBNET_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_VOLCANO_VESTACK_GET_SUBNET_ERROR'],
        'KVM_VOLCANO_VESTACK_GET_INSTANCE_TYPE_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_VOLCANO_VESTACK_GET_INSTANCE_TYPE_ERROR'],
        'KVM_VOLCANO_VESTACK_GET_PROJECT_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_VOLCANO_VESTACK_GET_PROJECT_ERROR'],
        'KVM_VOLCANO_VESTACK_GET_REGION_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_VOLCANO_VESTACK_GET_REGION_ERROR'],
        'KVM_VOLCANO_VESTACK_GET_ZONE_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_VOLCANO_VESTACK_GET_ZONE_ERROR'],
        'KVM_VOLCANO_VESTACK_NOT_FIND_AGENT_VM_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_VOLCANO_VESTACK_NOT_FIND_AGENT_VM_ERROR'],
        'KVM_VOLCANO_VESTACK_GET_VOLUME_ATTACH_PATH_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_VOLCANO_VESTACK_GET_VOLUME_ATTACH_PATH_ERROR'],
        'KVM_VOLCANO_VESTACK_GET_ACCOUNT_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_VOLCANO_VESTACK_GET_ACCOUNT_ERROR'],
        'KVM_VOLCANO_VESTACK_RESOURCE_NOT_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_VOLCANO_VESTACK_RESOURCE_NOT_EXIST_ERROR'],
        'KVM_VOLCANO_VESTACK_CALCULATE_HMAC_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_VOLCANO_VESTACK_CALCULATE_HMAC_ERROR'],
        'KVM_VOLCANO_VESTACK_IMPORT_IAMGE_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_VOLCANO_VESTACK_IMPORT_IAMGE_ERROR'],
        'KVM_VOLCANO_VESTACK_CREATE_MULTIPART_UPLOAD_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_VOLCANO_VESTACK_CREATE_MULTIPART_UPLOAD_ERROR'],
        'KVM_VOLCANO_VESTACK_DELETE_MULTIPART_UPLOAD_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_VOLCANO_VESTACK_DELETE_MULTIPART_UPLOAD_ERROR'],
        'KVM_VOLCANO_VESTACK_UPLOAD_SHARD_DATA_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_VOLCANO_VESTACK_UPLOAD_SHARD_DATA_ERROR'],
        'KVM_VOLCANO_VESTACK_MERGE_SHARD_DATA_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_VOLCANO_VESTACK_MERGE_SHARD_DATA_ERROR'],
        'KVM_VOLCANO_VESTACK_IMPORT_IMAGE_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_VOLCANO_VESTACK_IMPORT_IMAGE_ERROR'],
        'KVM_VOLCANO_VESTACK_DELETE_IMAGE_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_VOLCANO_VESTACK_DELETE_IMAGE_ERROR'],
        'KVM_VOLCANO_VESTACK_DELETE_OBJECT_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_VOLCANO_VESTACK_DELETE_OBJECT_ERROR'],
        'VM_INCREMENTAL_MODE_IS_CLOSED_ERROR' => Xphp::$_lang['WEB_ERROR_VM_INCREMENTAL_MODE_IS_CLOSED_ERROR'],
        'VM_GET_CPU_ARCH_ERROR' => Xphp::$_lang['WEB_ERROR_VM_GET_CPU_ARCH_ERROR'],
        'VM_GET_NETWORK_ADAPTER_ERROR' => Xphp::$_lang['WEB_ERROR_VM_GET_NETWORK_ADAPTER_ERROR'],
        'VM_DISABLE_NETWORK_ADAPTER_ERROR' => Xphp::$_lang['WEB_ERROR_VM_DISABLE_NETWORK_ADAPTER_ERROR'],
        'VM_GET_OS_INFO_LIST_ERROR' => Xphp::$_lang['WEB_ERROR_VM_GET_OS_INFO_LIST_ERROR'],
        'VM_SYSTEM_REBOOT_ABNORMAL_OR_NODE_TRANSFER' => Xphp::$_lang['WEB_ERROR_VM_SYSTEM_REBOOT_ABNORMAL_OR_NODE_TRANSFER'],
        'VM_BACKUP_INFO_NOT_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_VM_BACKUP_INFO_NOT_EXIST_ERROR'],
        'VM_DISK_INFO_NOT_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_VM_DISK_INFO_NOT_EXIST_ERROR'],
        'VM_BACKUP_INFO_HANDLE_FINISH' => Xphp::$_lang['WEB_ERROR_VM_BACKUP_INFO_HANDLE_FINISH'],
        'VM_BACKUP_INFO_HANDLE_NO_FINISH' => Xphp::$_lang['WEB_ERROR_VM_BACKUP_INFO_HANDLE_NO_FINISH'],
        'VM_WEIGHT_LIST_IS_EMPTY' => Xphp::$_lang['WEB_ERROR_VM_WEIGHT_LIST_IS_EMPTY'],
        'VM_WEIGHT_IS_INVALID' => Xphp::$_lang['WEB_ERROR_VM_WEIGHT_IS_INVALID'],
        'VM_IS_REMOVED_FROM_TASK' => Xphp::$_lang['WEB_ERROR_VM_IS_REMOVED_FROM_TASK'],
        'VM_SNAPSHOT_IS_OCCUPIED' => Xphp::$_lang['WEB_ERROR_VM_SNAPSHOT_IS_OCCUPIED'],
        'VM_FC_KVM_VM_10430041_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_VM_10430041_ERROR'],
        
        'KVM_SANGFOR_SYSTEM_BUSY_TRY_AGAIN_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_SANGFOR_SYSTEM_BUSY_TRY_AGAIN_ERROR'],

        'LIBVIRT_LOGIN_ERROR' => Xphp::$_lang['WEB_ERROR_LIBVIRT_LOGIN_ERROR'],
        'LIBVIRT_CREATE_VM_ERROR' => Xphp::$_lang['WEB_ERROR_LIBVIRT_CREATE_VM_ERROR'],
        'LIBVIRT_DEFINE_VM_CONFIG_XML_ERROR' => Xphp::$_lang['WEB_ERROR_LIBVIRT_DEFINE_VM_CONFIG_XML_ERROR'],
        'LIBVIRT_SET_VM_VNC_PORT_ERROR' => Xphp::$_lang['WEB_ERROR_LIBVIRT_SET_VM_VNC_PORT_ERROR'],
        'LIBVIRT_START_VM_ERROR' => Xphp::$_lang['WEB_ERROR_LIBVIRT_START_VM_ERROR'],
        'LIBVIRT_STOP_VM_ERROR' => Xphp::$_lang['WEB_ERROR_LIBVIRT_STOP_VM_ERROR'],
        'LIBVIRT_DELETE_VM_ERROR' => Xphp::$_lang['WEB_ERROR_LIBVIRT_DELETE_VM_ERROR'],
        'LIBVIRT_GET_VM_STATUS_ERROR' => Xphp::$_lang['WEB_ERROR_LIBVIRT_GET_VM_STATUS_ERROR'],
        'LIBVIRT_DEFINE_SWITCH_CONFIG_XML_ERROR' => Xphp::$_lang['WEB_ERROR_LIBVIRT_DEFINE_SWITCH_CONFIG_XML_ERROR'],
        'LIBVIRT_CREATE_SWITCH_ERROR' => Xphp::$_lang['WEB_ERROR_LIBVIRT_CREATE_SWITCH_ERROR'],
        'LIBVIRT_ACTIVE_SWITCH_ERROR' => Xphp::$_lang['WEB_ERROR_LIBVIRT_ACTIVE_SWITCH_ERROR'],
        'LIBVIRT_SHUTDOWN_SWITCH_ERROR' => Xphp::$_lang['WEB_ERROR_LIBVIRT_SHUTDOWN_SWITCH_ERROR'],
        'LIBVIRT_DELETE_SWITCH_ERROR' => Xphp::$_lang['WEB_ERROR_LIBVIRT_DELETE_SWITCH_ERROR'],
        'LIBVIRT_QUERY_SWITCH_ERROR' => Xphp::$_lang['WEB_ERROR_LIBVIRT_QUERY_SWITCH_ERROR'],
        'LIBVIRT_DEFINE_PORT_GROUP_XML_ERROR' => Xphp::$_lang['WEB_ERROR_LIBVIRT_DEFINE_PORT_GROUP_XML_ERROR'],
        'LIBVIRT_ADD_PORT_GROUP_ERROR' => Xphp::$_lang['WEB_ERROR_LIBVIRT_ADD_PORT_GROUP_ERROR'],
        'LIBVIRT_GENERIC_XML_ERROR' => Xphp::$_lang['WEB_ERROR_LIBVIRT_GENERIC_XML_ERROR'],
        'LIBVIRT_ADD_DEVICE_ERROR' => Xphp::$_lang['WEB_ERROR_LIBVIRT_ADD_DEVICE_ERROR'],
        'LIBVIRT_DEL_DEVICE_ERROR' => Xphp::$_lang['WEB_ERROR_LIBVIRT_DEL_DEVICE_ERROR'],
        'LIBVIRT_PARSE_XML_ERROR' => Xphp::$_lang['WEB_ERROR_LIBVIRT_PARSE_XML_ERROR'],
        'LIBVIRT_QUERY_VM_CONFIG_ERROR' => Xphp::$_lang['WEB_ERROR_LIBVIRT_QUERY_VM_CONFIG_ERROR'],
        'LIBVIRT_RECONFIG_VM_CONFIG_ERROR' => Xphp::$_lang['WEB_ERROR_LIBVIRT_RECONFIG_VM_CONFIG_ERROR'],
        'LIBVIRT_ALLOCATE_DEVICE_TARGET_ERROR' => Xphp::$_lang['WEB_ERROR_LIBVIRT_ALLOCATE_DEVICE_TARGET_ERROR'],
        'LIBVIRT_RECONFIG_VM_OS_ERROR' => Xphp::$_lang['WEB_ERROR_LIBVIRT_RECONFIG_VM_OS_ERROR'],
        'LIBVIRT_SCREEN_SHOT_ERROR' => Xphp::$_lang['WEB_ERROR_LIBVIRT_SCREEN_SHOT_ERROR'],

        'EMD_PREFIX_SUCCESS' => Xphp::$_lang['WEB_ERROR_EMD_PREFIX_SUCCESS'],
        'EMD_PREFIX_TIMEOUT' => Xphp::$_lang['WEB_ERROR_EMD_PREFIX_TIMEOUT'],
        'EMD_PREFIX_CANT_FIND_PARA_DISK' => Xphp::$_lang['WEB_ERROR_EMD_PREFIX_CANT_FIND_PARA_DISK'],
        'EMD_PREFIX_CANT_PARSE_FIX_MSG_INFO' => Xphp::$_lang['WEB_ERROR_EMD_PREFIX_CANT_PARSE_FIX_MSG_INFO'],
        'EMD_PREFIX_CANT_DO_BOOT_FIX_FAILD' => Xphp::$_lang['WEB_ERROR_EMD_PREFIX_CANT_DO_BOOT_FIX_FAILD'],
        'EMD_PREFIX_CANT_DO_AGENT_FIX_FAILD' => Xphp::$_lang['WEB_ERROR_EMD_PREFIX_CANT_DO_AGENT_FIX_FAILD'],
        'EMD_PREFIX_UNKNOWN_OS_TYPE' => Xphp::$_lang['WEB_ERROR_EMD_PREFIX_UNKNOWN_OS_TYPE'],
        'EMD_PREFIX_ADD_PARA_DISK_FAILED' => Xphp::$_lang['WEB_ERROR_EMD_PREFIX_ADD_PARA_DISK_FAILED'],
        'EMD_PREFIX_ADD_FIX_IMG_FAILED' => Xphp::$_lang['WEB_ERROR_EMD_PREFIX_ADD_FIX_IMG_FAILED'],
        'EMD_PREFIX_RECONFIG_VM_BOOT_BY_CDROM_FAILED' => Xphp::$_lang['WEB_ERROR_EMD_PREFIX_RECONFIG_VM_BOOT_BY_CDROM_FAILED'],
        'EMD_PREFIX_START_VM_BOOT_BY_CDROM_FAILED' => Xphp::$_lang['WEB_ERROR_EMD_PREFIX_START_VM_BOOT_BY_CDROM_FAILED'],
        'EMD_PREFIX_STOP_VM_BOOT_BY_CDROM_FAILED' => Xphp::$_lang['WEB_ERROR_EMD_PREFIX_STOP_VM_BOOT_BY_CDROM_FAILED'],
        'EMD_PREFIX_RECONFIG_VM_BOOT_BY_DISK_FAILED' => Xphp::$_lang['WEB_ERROR_EMD_PREFIX_RECONFIG_VM_BOOT_BY_DISK_FAILED'],
        'EMD_PREFIX_START_VM_BOOT_BY_DISK_FAILED' => Xphp::$_lang['WEB_ERROR_EMD_PREFIX_START_VM_BOOT_BY_DISK_FAILED'],

        'DB_CDP_GENERIC_SUCCESS' => Xphp::$_lang['WEB_ERROR_DB_CDP_GENERIC_SUCCESS'],
        'DB_CDP_GENERIC_ERROR' => Xphp::$_lang['WEB_ERROR_DB_CDP_GENERIC_ERROR'],
        'DB_CDP_UNKNOWN_ERROR' => Xphp::$_lang['WEB_ERROR_DB_CDP_UNKNOWN_ERROR'],
        'DB_CDP_ERROR_PACKET_ORDER' => Xphp::$_lang['WEB_ERROR_DB_CDP_ERROR_PACKET_ORDER'],
        'DB_CDP_ERROR_PACKET_ABNORMAL' => Xphp::$_lang['WEB_ERROR_DB_CDP_ERROR_PACKET_ABNORMAL'],
        'DB_CDP_ERROR_UNKNOWN_OP_CODE' => Xphp::$_lang['WEB_ERROR_DB_CDP_ERROR_UNKNOWN_OP_CODE'],
        'DB_CDP_ERROR_TASK_NOT_EXIST' => Xphp::$_lang['WEB_ERROR_DB_CDP_ERROR_TASK_NOT_EXIST'],
        'DB_CDP_ERROR_LOAD_TASK_INFO_FAILED' => Xphp::$_lang['WEB_ERROR_DB_CDP_ERROR_LOAD_TASK_INFO_FAILED'],
        'DB_CDP_ERROR_TASK_NOT_IN_TAKEOVER_STAGE' => Xphp::$_lang['WEB_ERROR_DB_CDP_ERROR_TASK_NOT_IN_TAKEOVER_STAGE'],
        'DB_CDP_ERROR_TASK_NOT_IN_RUNNING_STATUS' => Xphp::$_lang['WEB_ERROR_DB_CDP_ERROR_TASK_NOT_IN_RUNNING_STATUS'],
        'DB_CDP_ERROR_TASK_NOT_IN_FAULT_RESUME_STAGE' => Xphp::$_lang['WEB_ERROR_DB_CDP_ERROR_TASK_NOT_IN_FAULT_RESUME_STAGE'],
        'DB_CDP_ERROR_NOT_FIND_RUNNING_TASK' => Xphp::$_lang['WEB_ERROR_DB_CDP_ERROR_NOT_FIND_RUNNING_TASK'],
        'DB_CDP_ERROR_AGENT_HANDSHAKE_FAILED' => Xphp::$_lang['WEB_ERROR_DB_CDP_ERROR_AGENT_HANDSHAKE_FAILED'],
        'DB_CDP_ERROR_NOTIFY_AGENT_PERFORM_ACTION' => Xphp::$_lang['WEB_ERROR_DB_CDP_ERROR_NOTIFY_AGENT_PERFORM_ACTION'],
        'DB_CDP_ERROR_WAIT_AGENT_PERFORM_ACTION' => Xphp::$_lang['WEB_ERROR_DB_CDP_ERROR_WAIT_AGENT_PERFORM_ACTION'],
        'DB_CDP_ERROR_WAIT_ACK_TIMEOUT' => Xphp::$_lang['WEB_ERROR_DB_CDP_ERROR_WAIT_ACK_TIMEOUT'],
        'DB_CDP_ERROR_CONTROL_SENDER_NOT_INIT' => Xphp::$_lang['WEB_ERROR_DB_CDP_ERROR_CONTROL_SENDER_NOT_INIT'],
        'DB_CDP_ERROR_AGENT_LINK_LOST' => Xphp::$_lang['WEB_ERROR_DB_CDP_ERROR_AGENT_LINK_LOST'],
        'DB_CDP_ERROR_FIND_TASK_CHILD_PROCESS' => Xphp::$_lang['WEB_ERROR_DB_CDP_ERROR_FIND_TASK_CHILD_PROCESS'],
        'DB_CDP_ERROR_AGENT_NET_FAULT' => Xphp::$_lang['WEB_ERROR_DB_CDP_ERROR_AGENT_NET_FAULT'],
        'DB_CDP_ERROR_REALTIME_BACKUP_LICENSE_EXHAUST' => Xphp::$_lang['WEB_ERROR_DB_CDP_ERROR_REALTIME_BACKUP_LICENSE_EXHAUST'],
        'DB_CDP_ERROR_AUTO_TAKEOVER_LICENSE_EXHAUST' => Xphp::$_lang['WEB_ERROR_DB_CDP_ERROR_AUTO_TAKEOVER_LICENSE_EXHAUST'],
        'DB_CDP_ERROR_ASSOCIATED_TASK_EXIST_ON_THE_HOST' => Xphp::$_lang['WEB_ERROR_DB_CDP_ERROR_ASSOCIATED_TASK_EXIST_ON_THE_HOST'],
        'DB_CDP_ERROR_AUTHORIZED_CAPACITY_INSUFFICIENT' => Xphp::$_lang['WEB_ERROR_DB_CDP_ERROR_AUTHORIZED_CAPACITY_INSUFFICIENT'],
        'DB_CDP_ERROR_AUTHORIZED_CAPACITY_INSUFFICIENT_TO_BACKUP' => Xphp::$_lang['WEB_ERROR_DB_CDP_ERROR_AUTHORIZED_CAPACITY_INSUFFICIENT_TO_BACKUP'],
        'DB_CDP_ERROR_BACKUP_STORAGE_ROOT_PATH_NOT_EXIST' => Xphp::$_lang['WEB_ERROR_DB_CDP_ERROR_BACKUP_STORAGE_ROOT_PATH_NOT_EXIST'],
        'DB_CDP_ERROR_UNSUPPORTED_DATABASE_TYPE' => Xphp::$_lang['WEB_ERROR_DB_CDP_ERROR_UNSUPPORTED_DATABASE_TYPE'],
        'DB_CDP_ERROR_TASK_ALREADY_RUNNING' => Xphp::$_lang['WEB_ERROR_DB_CDP_ERROR_TASK_ALREADY_RUNNING'],
        'DB_CDP_ERROR_AGENT_CLIENT_INTERNAL' => Xphp::$_lang['WEB_ERROR_DB_CDP_ERROR_AGENT_CLIENT_INTERNAL'],
        'DB_CDP_ERROR_APP_SERVICE_NOT_RUNNING' => Xphp::$_lang['WEB_ERROR_DB_CDP_ERROR_APP_SERVICE_NOT_RUNNING'],
        'DB_CDP_ERROR_TASK_NOT_IN_LOG_SYNC' => Xphp::$_lang['WEB_ERROR_DB_CDP_ERROR_TASK_NOT_IN_LOG_SYNC'],
        'DB_CDP_ERROR_TASK_NOT_IN_LOG_SYNC_OR_TAKEOVER' => Xphp::$_lang['WEB_ERROR_DB_CDP_ERROR_TASK_NOT_IN_LOG_SYNC_OR_TAKEOVER'],
        'DB_CDP_ERROR_TASK_NOT_IN_FAILBACK_LOG_SYNC' => Xphp::$_lang['WEB_ERROR_DB_CDP_ERROR_TASK_NOT_IN_FAILBACK_LOG_SYNC'],
        'DB_CDP_ERROR_AGENT_APP_STATUS_ABNORMAL' => Xphp::$_lang['WEB_ERROR_DB_CDP_ERROR_AGENT_APP_STATUS_ABNORMAL'],
        'DB_CDP_ERROR_APP_LOG_REPLAY_COMPLEX_NEST_TYPE_NOT_SUPPORTED' => Xphp::$_lang['WEB_ERROR_DB_CDP_ERROR_APP_LOG_REPLAY_COMPLEX_NEST_TYPE_NOT_SUPPORTED'],
        'DB_CDP_ERROR_APP_IMPORT_TABLE_DATA_BUFFER_UNDERRUN' => Xphp::$_lang['WEB_ERROR_DB_CDP_ERROR_APP_IMPORT_TABLE_DATA_BUFFER_UNDERRUN'],
        'DB_CDP_ERROR_AGENT_APP_TIME_INCONSISTENT' => Xphp::$_lang['WEB_ERROR_DB_CDP_ERROR_AGENT_APP_TIME_INCONSISTENT'],
        'DB_CDP_ERROR_RECOVERY_TARGET_DATETIME_INVALID' => Xphp::$_lang['DB_CDP_ERROR_RECOVERY_TARGET_DATETIME_INVALID'],
        'DB_CDP_ERROR_AGENT_NIC_CONFIG_ERROR' => Xphp::$_lang['WEB_ERROR_DB_CDP_ERROR_AGENT_NIC_CONFIG_ERROR'],
        'DB_CDP_ERROR_AGENT_FAILBACK_NIC_CONIFG_ERROR' => Xphp::$_lang['WEB_ERROR_DB_CDP_ERROR_AGENT_FAILBACK_NIC_CONIFG_ERROR'],

        'DB_CDP_ERROR_AGENT_APP_TIME_ZONE_INCONSISTENT' => Xphp::$_lang['WEB_ERROR_DB_CDP_ERROR_AGENT_APP_TIME_ZONE_INCONSISTENT'],
        'DB_CDP_ERROR_APP_LOG_MODE_NOARCHIVELOG' => Xphp::$_lang['WEB_ERROR_DB_CDP_ERROR_APP_LOG_MODE_NOARCHIVELOG'],
        'DB_CDP_ERROR_APP_SUPPLEMENTAL_LOG_CONFIG_ERROR' => Xphp::$_lang['WEB_ERROR_DB_CDP_ERROR_APP_SUPPLEMENTAL_LOG_CONFIG_ERROR'],
        'DB_CDP_ERROR_APP_ASM_CONFIG_ERROR' => Xphp::$_lang['WEB_ERROR_DB_CDP_ERROR_APP_ASM_CONFIG_ERROR'],
        'DB_CDP_ERROR_AGENT_CACHE_USED_SPACE_EXCESS' => Xphp::$_lang['WEB_ERROR_DB_CDP_ERROR_AGENT_CACHE_USED_SPACE_EXCESS'],
        'DB_CDP_ERROR_AGENT_ENABLED_SIZE_NOT_ENOUGH' => Xphp::$_lang['WEB_ERROR_DB_CDP_ERROR_AGENT_ENABLED_SIZE_NOT_ENOUGH'],
        'DB_CDP_ERROR_APP_TABLESPACE_DIR_PERMISSION_ERROR' => Xphp::$_lang['WEB_ERROR_DB_CDP_ERROR_APP_TABLESPACE_DIR_PERMISSION_ERROR'],
        'DB_CDP_ERROR_AGENT_CLUSTER_CONFIG_ERROR' => Xphp::$_lang['WEB_ERROR_DB_CDP_ERROR_AGENT_CLUSTER_CONFIG_ERROR'],
        'DB_CDP_ERROR_AGENT_SELECT_COPY_USERS_ERROR' => Xphp::$_lang['WEB_ERROR_DB_CDP_ERROR_AGENT_SELECT_COPY_USERS_ERROR'],
        'DB_CDP_ERROR_AGENT_USER_TABLESPACE_STATUS_ERROR' => Xphp::$_lang['WEB_ERROR_DB_CDP_ERROR_AGENT_USER_TABLESPACE_STATUS_ERROR'],
        'DB_CDP_ERROR_AGENT_PDB_OPEN_MODE_ERROR' => Xphp::$_lang['WEB_ERROR_DB_CDP_ERROR_AGENT_PDB_OPEN_MODE_ERROR'],
        'DB_CDP_ERROR_AGENT_PDB_CONFIG_ERROR' => Xphp::$_lang['WEB_ERROR_DB_CDP_ERROR_AGENT_PDB_CONFIG_ERROR'],
        'DB_CDP_ERROR_LOAD_BALANCING_CHANGE_NODE' => Xphp::$_lang['WEB_ERROR_DB_CDP_ERROR_LOAD_BALANCING_CHANGE_NODE'],
        'DB_CDP_ERROR_AGENT_PORT_OPEN_STATUS_ERROR' => Xphp::$_lang['WEB_ERROR_DB_CDP_ERROR_AGENT_PORT_OPEN_STATUS_ERROR'],
        'DB_CDP_ERROR_AGENT_MEMORY_EXCESS_THRESHOLD' => Xphp::$_lang['WEB_ERROR_DB_CDP_ERROR_AGENT_MEMORY_EXCESS_THRESHOLD'],
        'DB_CDP_ERROR_RECOVERY_TARGET_SCN_INVALID' => Xphp::$_lang['WEB_ERROR_DB_CDP_ERROR_RECOVERY_TARGET_SCN_INVALID'],
        'DB_CDP_ERROR_RECOVERY_BACKUP_INFO_INVALID' => Xphp::$_lang['WEB_ERROR_DB_CDP_ERROR_RECOVERY_BACKUP_INFO_INVALID'],
        'DB_CDP_ERROR_APP_TABLESPACE_SIZE_NOT_ENOUGH' => Xphp::$_lang['WEB_ERROR_DB_CDP_ERROR_APP_TABLESPACE_SIZE_NOT_ENOUGH'],
        'DB_CDP_ERROR_AGENT_CACHE_ENABLE_SIZE_BELOW_THRESHOLD' => Xphp::$_lang['WEB_ERROR_DB_CDP_ERROR_AGENT_CACHE_ENABLE_SIZE_BELOW_THRESHOLD'],
        'DB_CDP_ERROR_APP_GENERIC_ERROR' => Xphp::$_lang['WEB_ERROR_DB_CDP_ERROR_APP_GENERIC_ERROR'],
        'DB_CDP_ERROR_APP_EXPORT_DICTIONARY_ERROR' => Xphp::$_lang['WEB_ERROR_DB_CDP_ERROR_APP_EXPORT_DICTIONARY_ERROR'],
        'DB_CDP_ERROR_APP_IMPORT_DICTIONARY_ERROR' => Xphp::$_lang['WEB_ERROR_DB_CDP_ERROR_APP_IMPORT_DICTIONARY_ERROR'],
        'DB_CDP_ERROR_APP_EXPORT_TABLE_DATA_ERROR' => Xphp::$_lang['WEB_ERROR_DB_CDP_ERROR_APP_EXPORT_TABLE_DATA_ERROR'],
        'DB_CDP_ERROR_APP_IMPORT_TABLE_DATA_ERROR' => Xphp::$_lang['WEB_ERROR_DB_CDP_ERROR_APP_IMPORT_TABLE_DATA_ERROR'],
        'DB_CDP_ERROR_APP_IMPORT_CONSTRAINTS_ERROR' => Xphp::$_lang['WEB_ERROR_DB_CDP_ERROR_APP_IMPORT_CONSTRAINTS_ERROR'],
        'DB_CDP_ERROR_APP_DISABLE_JOBS_AND_TRIGGER_ERROR' => Xphp::$_lang['WEB_ERROR_DB_CDP_ERROR_APP_DISABLE_JOBS_AND_TRIGGER_ERROR'],
        'DB_CDP_ERROR_APP_IMPORT_SEQUENCES_ERROR' => Xphp::$_lang['WEB_ERROR_DB_CDP_ERROR_APP_IMPORT_SEQUENCES_ERROR'],
        'DB_CDP_ERROR_APP_ENABLE_JOBS_AND_TRIGGER_ERROR' => Xphp::$_lang['WEB_ERROR_DB_CDP_ERROR_APP_ENABLE_JOBS_AND_TRIGGER_ERROR'],
        'DB_CDP_ERROR_APP_EXPORT_DICTIONARY_SUCCESS' => Xphp::$_lang['WEB_ERROR_DB_CDP_ERROR_APP_EXPORT_DICTIONARY_SUCCESS'],
        'DB_CDP_ERROR_APP_IMPORT_DICTIONARY_SUCCESS' => Xphp::$_lang['WEB_ERROR_DB_CDP_ERROR_APP_IMPORT_DICTIONARY_SUCCESS'],
        'DB_CDP_ERROR_APP_EXPORT_DATA_SUCCESS' => Xphp::$_lang['WEB_ERROR_DB_CDP_ERROR_APP_EXPORT_DATA_SUCCESS'],
        'DB_CDP_ERROR_APP_IMPORT_DATA_SUCCESS' => Xphp::$_lang['WEB_ERROR_DB_CDP_ERROR_APP_IMPORT_DATA_SUCCESS'],
        'DB_CDP_ERROR_APP_IMPORT_CONSTRAINT_SUCCESS' => Xphp::$_lang['WEB_ERROR_DB_CDP_ERROR_APP_IMPORT_CONSTRAINT_SUCCESS'],
        'DB_CDP_ERROR_APP_IMPORT_SEQUENCE_SUCCESS' => Xphp::$_lang['WEB_ERROR_DB_CDP_ERROR_APP_IMPORT_SEQUENCE_SUCCESS'],
        'DB_CDP_ERROR_APP_ENABLE_JOB_AND_TRIGGER_SUCCESS' => Xphp::$_lang['WEB_ERROR_DB_CDP_ERROR_APP_ENABLE_JOB_AND_TRIGGER_SUCCESS'],
        'DB_CDP_ERROR_APP_DISABLE_JOB_AND_TRIGGER_SUCCESS' => Xphp::$_lang['WEB_ERROR_DB_CDP_ERROR_APP_DISABLE_JOB_AND_TRIGGER_SUCCESS'],
        'DB_CDP_ERROR_APP_TAKEOVER_SUCCESS' => Xphp::$_lang['WEB_ERROR_DB_CDP_ERROR_APP_TAKEOVER_SUCCESS'],
        'DB_CDP_ERROR_APP_RECOVERY_SUCCESS' => Xphp::$_lang['WEB_ERROR_DB_CDP_ERROR_APP_RECOVERY_SUCCESS'],
        'DB_CDP_ERROR_APP_TRANSACTION_REPLAY_ERROR' => Xphp::$_lang['WEB_ERROR_DB_CDP_ERROR_APP_TRANSACTION_REPLAY_ERROR'],
        'DB_CDP_ERROR_APP_GET_REDO_LOG_INFO_ERROR' => Xphp::$_lang['WEB_ERROR_DB_CDP_ERROR_APP_GET_REDO_LOG_INFO_ERROR'],
        'DB_CDP_ERROR_APP_LOG_CAPTURE_ERROR' => Xphp::$_lang['WEB_ERROR_DB_CDP_ERROR_APP_LOG_CAPTURE_ERROR'],
        'DB_CDP_ERROR_APP_LOAD_LOG_ANALYSIS_LIB_ERROR' => Xphp::$_lang['WEB_ERROR_DB_CDP_ERROR_APP_LOAD_LOG_ANALYSIS_LIB_ERROR'],
        'DB_CDP_ERROR_APP_REDO_LOG_ANALYSIS_ERROR' => Xphp::$_lang['WEB_ERROR_DB_CDP_ERROR_APP_REDO_LOG_ANALYSIS_ERROR'],
        'DB_CDP_ERROR_APP_REDO_LOG_CACHE_THREAD_ERROR' => Xphp::$_lang['WEB_ERROR_DB_CDP_ERROR_APP_REDO_LOG_CACHE_THREAD_ERROR'],
        'DB_CDP_ERROR_APP_INTI_LOG_REPLAY_THREAD_ERROR' => Xphp::$_lang['WEB_ERROR_DB_CDP_ERROR_APP_INTI_LOG_REPLAY_THREAD_ERROR'],
        'DB_CDP_ERROR_APP_REDO_LOG_WRITE_THREAD_ERROR' => Xphp::$_lang['WEB_ERROR_DB_CDP_ERROR_APP_REDO_LOG_WRITE_THREAD_ERROR'],
        'DB_CDP_ERROR_APP_RECEIVE_DICTIONARY_DATA_SUCCESS' => Xphp::$_lang['WEB_ERROR_DB_CDP_ERROR_APP_RECEIVE_DICTIONARY_DATA_SUCCESS'],
        'DB_CDP_ERROR_APP_AGENT_TO_AGENT_CONNECT_ERROR' => Xphp::$_lang['WEB_ERROR_DB_CDP_ERROR_APP_AGENT_TO_AGENT_CONNECT_ERROR'],

        'DB_CDP_ERROR_APP_REDO_LOG_ANALYSIS_NOT_RESPONDED_ERROR' => Xphp::$_lang['WEB_ERROR_DB_CDP_ERROR_APP_REDO_LOG_ANALYSIS_NOT_RESPONDED_ERROR'],
        'DB_CDP_ERROR_APP_SEND_RECOVERY_CACHE_SUCCESS' => Xphp::$_lang['WEB_ERROR_DB_CDP_ERROR_APP_SEND_RECOVERY_CACHE_SUCCESS'],
        'DB_CDP_ERROR_APP_SEND_RECOVERY_CACHE_ERROR' => Xphp::$_lang['WEB_ERROR_DB_CDP_ERROR_APP_SEND_RECOVERY_CACHE_ERROR'],
        'DB_CDP_ERROR_APP_RECEIVE_RECOVERY_CACHE_SUCCESS' => Xphp::$_lang['WEB_ERROR_DB_CDP_ERROR_APP_RECEIVE_RECOVERY_CACHE_SUCCESS'],
        'DB_CDP_ERROR_APP_RECEIVE_RECOVERY_CACHE_ERROR' => Xphp::$_lang['WEB_ERROR_DB_CDP_ERROR_APP_RECEIVE_RECOVERY_CACHE_ERROR'],
        'DB_CDP_ERROR_APP_RECOVERY_CACHE_REMAP_SUCCESS' => Xphp::$_lang['WEB_ERROR_DB_CDP_ERROR_APP_RECOVERY_CACHE_REMAP_SUCCESS'],
        'DB_CDP_ERROR_APP_RECOVERY_CACHE_REMAP_ERROR' => Xphp::$_lang['WEB_ERROR_DB_CDP_ERROR_APP_RECOVERY_CACHE_REMAP_ERROR'],
        'DB_CDP_ERROR_APP_EXPORT_OLR_SCHEMA_SUCCESS' => Xphp::$_lang['WEB_ERROR_DB_CDP_ERROR_APP_EXPORT_OLR_SCHEMA_SUCCESS'],
        'DB_CDP_ERROR_APP_EXPORT_OLR_SCHEMA_ERROR' => Xphp::$_lang['WEB_ERROR_DB_CDP_ERROR_APP_EXPORT_OLR_SCHEMA_ERROR'],
        'DB_CDP_ERROR_APP_SEND_OLR_SCHEMA_SUCCESS' => Xphp::$_lang['WEB_ERROR_DB_CDP_ERROR_APP_SEND_OLR_SCHEMA_SUCCESS'],
        'DB_CDP_ERROR_APP_SEND_OLR_SCHEMA_ERROR' => Xphp::$_lang['WEB_ERROR_DB_CDP_ERROR_APP_SEND_OLR_SCHEMA_ERROR'],
        'DB_CDP_ERROR_APP_RECEIVE_OLR_SCHEMA_SUCCESS' => Xphp::$_lang['WEB_ERROR_DB_CDP_ERROR_APP_RECEIVE_OLR_SCHEMA_SUCCESS'],
        'DB_CDP_ERROR_APP_RECEIVE_OLR_SCHEMA_ERROR' => Xphp::$_lang['WEB_ERROR_DB_CDP_ERROR_APP_RECEIVE_OLR_SCHEMA_ERROR'],
        'DB_CDP_ERROR_APP_TRANSMIT_OFFLINE_RESIDUAL_DATA_ERROR' => Xphp::$_lang['WEB_ERROR_DB_CDP_ERROR_APP_TRANSMIT_OFFLINE_RESIDUAL_DATA_ERROR'],
        'DB_CDP_ERROR_APP_TRANSMIT_OFFLINE_RESIDUAL_DATA_SUCCESS' => Xphp::$_lang['WEB_ERROR_DB_CDP_ERROR_APP_TRANSMIT_OFFLINE_RESIDUAL_DATA_SUCCESS'],

        'ELITE_RCVY_INIT_ERROR' => Xphp::$_lang['WEB_ERROR_ELITE_RCVY_INIT_ERROR'],
        'ELITE_RCVY_PARSE_CONFIG_ERROR' => Xphp::$_lang['WEB_ERROR_ELITE_RCVY_PARSE_CONFIG_ERROR'],
        'ELITE_RCVY_MAGIC_ERROR' => Xphp::$_lang['WEB_ERROR_ELITE_RCVY_MAGIC_ERROR'],
        'ELITE_RCVY_OPCODE_INVALID_ERROR' => Xphp::$_lang['WEB_ERROR_ELITE_RCVY_OPCODE_INVALID_ERROR'],
        'ELITE_RCVY_NO_DETECTED_CONNECTION_ERROR' => Xphp::$_lang['WEB_ERROR_ELITE_RCVY_NO_DETECTED_CONNECTION_ERROR'],
        'ELITE_RCVY_CREATE_WORKER_PROCESS_ERROR' => Xphp::$_lang['WEB_ERROR_ELITE_RCVY_CREATE_WORKER_PROCESS_ERROR'],
        'ELITE_RCVY_CHECK_WORKER_PROCESS_LISTEN_ERROR' => Xphp::$_lang['WEB_ERROR_ELITE_RCVY_CHECK_WORKER_PROCESS_LISTEN_ERROR'],
        'ELITE_RCVY_LOAD_AGENT_CONF_ERROR' => Xphp::$_lang['WEB_ERROR_ELITE_RCVY_LOAD_AGENT_CONF_ERROR'],
        'ELITE_RCVY_START_FUSE_THREAD_ERROR' => Xphp::$_lang['WEB_ERROR_ELITE_RCVY_START_FUSE_THREAD_ERROR'],
        'ELITE_RCVY_RESOURCE_NOT_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_ELITE_RCVY_RESOURCE_NOT_EXIST_ERROR'],
        'ELITE_RCVY_BUILD_CACHE_PATH_ERROR' => Xphp::$_lang['WEB_ERROR_ELITE_RCVY_BUILD_CACHE_PATH_ERROR'],
        'ELITE_RCVY_OPERATION_TIMEOUT_ERROR' => Xphp::$_lang['WEB_ERROR_ELITE_RCVY_OPERATION_TIMEOUT_ERROR'],
        'ELITE_RCVY_FUSE_UNEXPECTED_READ_REQUEST_ERROR' => Xphp::$_lang['WEB_ERROR_ELITE_RCVY_FUSE_UNEXPECTED_READ_REQUEST_ERROR'],
        'ELITE_RCVY_MOUNT_POINT_ALREADY_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_ELITE_RCVY_MOUNT_POINT_ALREADY_EXIST_ERROR'],
        'ELITE_RCVY_CREATE_SMB_USER_ERROR' => Xphp::$_lang['WEB_ERROR_ELITE_RCVY_CREATE_SMB_USER_ERROR'],
        'ELITE_RCVY_DELETE_SMB_USER_ERROR' => Xphp::$_lang['WEB_ERROR_ELITE_RCVY_DELETE_SMB_USER_ERROR'],
        'ELITE_RCVY_COPY_CONFIG_FILE_ERROR' => Xphp::$_lang['WEB_ERROR_ELITE_RCVY_COPY_CONFIG_FILE_ERROR'],
        'ELITE_RCVY_FILE_PROTOCOL_NOT_SUPPORT_ERROR' => Xphp::$_lang['WEB_ERROR_ELITE_RCVY_FILE_PROTOCOL_NOT_SUPPORT_ERROR'],
        'ELITE_RCVY_NO_FILES_SCANNED_ERROR' => Xphp::$_lang['WEB_ERROR_ELITE_RCVY_NO_FILES_SCANNED_ERROR'],
        'ELITE_RCVY_EMBED_RESOURCE_INSUFFICIENT_ERROR' => Xphp::$_lang['WEB_ERROR_ELITE_RCVY_EMBED_RESOURCE_INSUFFICIENT_ERROR'],
        'ELITE_RCVY_TWO_LIST_SIZE_MISMATCH_ERROR' => Xphp::$_lang['WEB_ERROR_ELITE_RCVY_TWO_LIST_SIZE_MISMATCH_ERROR'],
        'ELITE_RCVY_CATCH_EXCEPTION_ERROR' => Xphp::$_lang['WEB_ERROR_ELITE_RCVY_CATCH_EXCEPTION_ERROR'],
        'ELITE_RCVY_CLUSTER_SIZE_NOT_DIVISIBLE_ERROR' => Xphp::$_lang['WEB_ERROR_ELITE_RCVY_CLUSTER_SIZE_NOT_DIVISIBLE_ERROR'],
        'ELITE_RCVY_CLUSTER_SIZE_NOT_EQUAL_ERROR' => Xphp::$_lang['WEB_ERROR_ELITE_RCVY_CLUSTER_SIZE_NOT_EQUAL_ERROR'],
        'ELITE_RCVY_DISK_TYPE_NOT_SUPPORT_ERROR' => Xphp::$_lang['WEB_ERROR_ELITE_RCVY_DISK_TYPE_NOT_SUPPORT_ERROR'],
        'ELITE_RCVY_FUSE_DATA_MAPPING_TYPE_NOT_SUPPORT_ERROR' => Xphp::$_lang['WEB_ERROR_ELITE_RCVY_FUSE_DATA_MAPPING_TYPE_NOT_SUPPORT_ERROR'],
        'ELITE_RCVY_MODULE_TYPE_NOT_SUPPORT_ERROR' => Xphp::$_lang['WEB_ERROR_ELITE_RCVY_MODULE_TYPE_NOT_SUPPORT_ERROR'],
        'ELITE_RCVY_VM_HYPERVISOR_TYPE_NOT_SUPPORT_ERROR' => Xphp::$_lang['WEB_ERROR_ELITE_RCVY_VM_HYPERVISOR_TYPE_NOT_SUPPORT_ERROR'],
        'ELITE_RCVY_CLUSTER_SIZE_INVALID_ERROR' => Xphp::$_lang['WEB_ERROR_ELITE_RCVY_CLUSTER_SIZE_INVALID_ERROR'],
        'ELITE_RCVY_MAP_KEY_NOT_FOUND_ERROR' => Xphp::$_lang['WEB_ERROR_ELITE_RCVY_MAP_KEY_NOT_FOUND_ERROR'],
        'ELITE_RCVY_RESOURCE_RESIDUE_ERROR' => Xphp::$_lang['WEB_ERROR_ELITE_RCVY_RESOURCE_RESIDUE_ERROR'],
        'ELITE_RCVY_ADD_DISK_TO_EMBED_VM_ERROR' => Xphp::$_lang['WEB_ERROR_ELITE_RCVY_ADD_DISK_TO_EMBED_VM_ERROR'],
        'ELITE_RCVY_CONVERT_PTR_ERROR' => Xphp::$_lang['WEB_ERROR_ELITE_RCVY_CONVERT_PTR_ERROR'],
        'ELITE_RCVY_CONNECT_EMBED_VM_AGENT_ERROR' => Xphp::$_lang['WEB_ERROR_ELITE_RCVY_CONNECT_EMBED_VM_AGENT_ERROR'],
        'ELITE_RCVY_RESOURCE_CROSSED_ERROR' => Xphp::$_lang['WEB_ERROR_ELITE_RCVY_RESOURCE_CROSSED_ERROR'],
        'ELITE_RCVY_MOUNT_DISK_PARTITION_ERROR' => Xphp::$_lang['WEB_ERROR_ELITE_RCVY_MOUNT_DISK_PARTITION_ERROR'],
        'ELITE_RCVY_UMOUNT_ERROR' => Xphp::$_lang['WEB_ERROR_ELITE_RCVY_UMOUNT_ERROR'],
        'ELITE_RCVY_COMPRESS_FILES_ERROR' => Xphp::$_lang['WEB_ERROR_ELITE_RCVY_COMPRESS_FILES_ERROR'],
        'ELITE_RCVY_SMB_USER_ALREADY_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_ELITE_RCVY_SMB_USER_ALREADY_EXIST_ERROR'],
        'ELITE_RCVY_TASK_IS_NON_RUNNING_ERROR' => Xphp::$_lang['WEB_ERROR_ELITE_RCVY_TASK_IS_NON_RUNNING_ERROR'],
        'ELITE_RCVY_RESIDUAL_WORKER_NO_CLEAN_FINISH_ERROR' => Xphp::$_lang['WEB_ERROR_ELITE_RCVY_RESIDUAL_WORKER_NO_CLEAN_FINISH_ERROR'],
        'ELITE_RCVY_CHECK_WORKER_NETWORK_LISTEN_ERROR' => Xphp::$_lang['WEB_ERROR_ELITE_RCVY_CHECK_WORKER_NETWORK_LISTEN_ERROR'],
        'ELITE_RCVY_KEY_REPET_ERROR' => Xphp::$_lang['WEB_ERROR_ELITE_RCVY_KEY_REPET_ERROR'],


        'TIMEPOINT_INIT_ERROR' => Xphp::$_lang['WEB_ERROR_TIMEPOINT_INIT_ERROR'],
        'TIMEPOINT_PARSE_CONFIG_ERROR' => Xphp::$_lang['WEB_ERROR_TIMEPOINT_PARSE_CONFIG_ERROR'],
        'TIMEPOINT_MAGIC_ERROR' => Xphp::$_lang['WEB_ERROR_TIMEPOINT_MAGIC_ERROR'],
        'TIMEPOINT_OPCODE_INVALID_ERROR' => Xphp::$_lang['WEB_ERROR_TIMEPOINT_OPCODE_INVALID_ERROR'],
        'TIMEPOINT_NO_DETECTED_CONNECTION_ERROR' => Xphp::$_lang['WEB_ERROR_TIMEPOINT_NO_DETECTED_CONNECTION_ERROR'],
        'TIMEPOINT_CREATE_WORKER_PROCESS_ERROR' => Xphp::$_lang['WEB_ERROR_TIMEPOINT_CREATE_WORKER_PROCESS_ERROR'],
        'TIMEPOINT_CHECK_WORKER_PROCESS_LISTEN_ERROR' => Xphp::$_lang['WEB_ERROR_TIMEPOINT_CHECK_WORKER_PROCESS_LISTEN_ERROR'],
        'TIMEPOINT_LOAD_AGENT_CONF_ERROR' => Xphp::$_lang['WEB_ERROR_TIMEPOINT_LOAD_AGENT_CONF_ERROR'],
        'TIMEPOINT_RESOURCE_NOT_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_TIMEPOINT_RESOURCE_NOT_EXIST_ERROR'],
        'TIMEPOINT_UPDATE_OPERATION_STATUS_ERROR' => Xphp::$_lang['WEB_ERROR_TIMEPOINT_UPDATE_OPERATION_STATUS_ERROR'],
        'TIMEPOINT_UNSUPPORTED_MODULE_TYPE_ERROR' => Xphp::$_lang['WEB_ERROR_TIMEPOINT_UNSUPPORTED_MODULE_TYPE_ERROR'],
        'TIMEPOINT_UNSUPPORTED_TIMEPOINT_OPERATION_ERROR' => Xphp::$_lang['WEB_ERROR_TIMEPOINT_UNSUPPORTED_TIMEPOINT_OPERATION_ERROR'],
        'TIMEPOINT_DELETE_TIMEPOINT_HAS_DIFF_TIMEPOINT_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_TIMEPOINT_DELETE_TIMEPOINT_HAS_DIFF_TIMEPOINT_EXIST_ERROR'],
        'TIMEPOINT_EXTEND_WORM_FOR_BACKUP_CHAIN_ERROR' => Xphp::$_lang['WEB_ERROR_TIMEPOINT_EXTEND_WORM_FOR_BACKUP_CHAIN_ERROR'],
        'TIMEPOINT_MERGE_OPERATION_STATUS_RESIDUE' => Xphp::$_lang['WEB_ERROR_TIMEPOINT_MERGE_OPERATION_STATUS_RESIDUE'],
        'TIMEPOINT_BACKUP_POINT_SET_WORM_CANNOT_DELETE' => Xphp::$_lang['WEB_ERROR_TIMEPOINT_BACKUP_POINT_SET_WORM_CANNOT_DELETE'],
        'TIMEPOINT_RESIDUAL_WORKER_NO_CLEAN_FINISH_ERROR' => Xphp::$_lang['WEB_ERROR_TIMEPOINT_RESIDUAL_WORKER_NO_CLEAN_FINISH_ERROR'],
        'TIMEPOINT_CHECK_WORKER_NETWORK_LISTEN_ERROR' => Xphp::$_lang['WEB_ERROR_TIMEPOINT_CHECK_WORKER_NETWORK_LISTEN_ERROR'],
        'TIMEPOINT_GET_GLOBAL_LOCK_TIMEOUT_ERROR' => Xphp::$_lang['WEB_ERROR_TIMEPOINT_GET_GLOBAL_LOCK_TIMEOUT_ERROR'],
        'TIMEPOINT_BACKUP_POINT_IS_MERGING_CANNOT_UPDATE_OPERATION_STATUS_ERROR' => Xphp::$_lang['WEB_ERROR_TIMEPOINT_BACKUP_POINT_IS_MERGING_CANNOT_UPDATE_OPERATION_STATUS_ERROR'],
        'TIMEPOINT_BACKUP_POINT_IS_DELETING_CANNOT_UPDATE_OPERATION_STATUS_ERROR' => Xphp::$_lang['WEB_ERROR_TIMEPOINT_BACKUP_POINT_IS_DELETING_CANNOT_UPDATE_OPERATION_STATUS_ERROR'],
        'TIMEPOINT_BACKUP_POINT_IS_SCANNING_CANNOT_UPDATE_OPERATION_STATUS_ERROR' => Xphp::$_lang['WEB_ERROR_TIMEPOINT_BACKUP_POINT_IS_SCANNING_CANNOT_UPDATE_OPERATION_STATUS_ERROR'],
        'TIMEPOINT_BACKUP_POINT_IS_CHECKING_CANNOT_UPDATE_OPERATION_STATUS_ERROR' => Xphp::$_lang['WEB_ERROR_TIMEPOINT_BACKUP_POINT_IS_CHECKING_CANNOT_UPDATE_OPERATION_STATUS_ERROR'],
        'TIMEPOINT_ROLL_BACK_OF_MERGE_ERROR' => Xphp::$_lang['WEB_ERROR_TIMEPOINT_ROLL_BACK_OF_MERGE_ERROR'],
        'TIMEPOIN_NO_NODE_CAN_DELETE_TIMEPOINT_ERROR' => Xphp::$_lang['WEB_ERROR_TIMEPOIN_NO_NODE_CAN_DELETE_TIMEPOINT_ERROR'],
        'TIMEPOINT_GLOBAL_RESERVED_STRATEGY_ALREADY_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_TIMEPOINT_GLOBAL_RESERVED_STRATEGY_ALREADY_EXIST_ERROR'],
       
        'SR_DEPLOY_VIRTUAL_LAB_ERROR' => Xphp::$_lang['WEB_ERROR_SR_DEPLOY_VIRTUAL_LAB_ERROR'],
        'SR_GET_VIRTUAL_LAB_INFO_ERROR' => Xphp::$_lang['WEB_ERROR_SR_GET_VIRTUAL_LAB_INFO_ERROR'],
        'SR_GET_HOST_CLUSTER_ERROR' => Xphp::$_lang['WEB_ERROR_SR_GET_HOST_CLUSTER_ERROR'],
        'SR_PING_TEST_VM_ERROR' => Xphp::$_lang['WEB_ERROR_SR_PING_TEST_VM_ERROR'],
        'SR_BUILD_SURE_BACKUP_ITEM_LIST_ERROR' => Xphp::$_lang['WEB_ERROR_SR_BUILD_SURE_BACKUP_ITEM_LIST_ERROR'],
        'SR_DEPLOY_SURE_BACKUP_NETWORK_ERROR' => Xphp::$_lang['WEB_ERROR_SR_DEPLOY_SURE_BACKUP_NETWORK_ERROR'], 
        'SR_DELETE_NETWORK_MAP_ERROR' => Xphp::$_lang['WEB_ERROR_SR_DELETE_NETWORK_MAP_ERROR'],
        'SR_REMOVE_FOLDER_ERROR' => Xphp::$_lang['WEB_ERROR_SR_REMOVE_FOLDER_ERROR'],
        'SR_DELETE_VIRTUAL_LAB_ERROR' => Xphp::$_lang['WEB_ERROR_SR_DELETE_VIRTUAL_LAB_ERROR'],
        'SR_DELETE_APPLICATION_GROUP_ERROR' => Xphp::$_lang['WEB_ERROR_SR_DELETE_APPLICATION_GROUP_ERROR'],
        'SR_EDIT_APPLICATION_GROUP_ERROR' => Xphp::$_lang['WEB_ERROR_SR_EDIT_APPLICATION_GROUP_ERROR'],
        'SR_GET_ITEM_MODULE_TYPE_ERROR' => Xphp::$_lang['WEB_ERROR_SR_GET_ITEM_MODULE_TYPE_ERROR'],
        'SR_UNKNOW_SUREBACKUP_TYPE_ERROR' => Xphp::$_lang['WEB_ERROR_SR_UNKNOW_SUREBACKUP_TYPE_ERROR'],
        'SR_INTEGRITY_CHECK_VM_ERROR' => Xphp::$_lang['WEB_ERROR_SR_INTEGRITY_CHECK_VM_ERROR'],
        'SR_VIRUS_SCAN_VM_ERROR' => Xphp::$_lang['WEB_ERROR_SR_VIRUS_SCAN_VM_ERROR'],


        'SR_HEARTBEAT_TEST_VM_ERROR' => Xphp::$_lang['WEB_ERROR_SR_HEARTBEAT_TEST_VM_ERROR'],
        'SR_DOWNLOAD_SCREEN_SHOT_ERROR' => Xphp::$_lang['WEB_ERROR_SR_DOWNLOAD_SCREEN_SHOT_ERROR'],
        'SR_DOCUMENT_CONSISTENCY_VM_ERROR' => Xphp::$_lang['WEB_ERROR_SR_DOCUMENT_CONSISTENCY_VM_ERROR'],
        'SR_CACULATE_ISOLATED_IP_ADDRESS_ERROR' => Xphp::$_lang['WEB_ERROR_SR_CACULATE_ISOLATED_IP_ADDRESS_ERROR'],
        'SR_VM_NOT_GENERATE_TIMEPOINT_ERROR' => Xphp::$_lang['WEB_ERROR_SR_VM_NOT_GENERATE_TIMEPOINT_ERROR'],
        'SR_OS_NOT_GENERATE_TIMEPOINT_ERROR' => Xphp::$_lang['WEB_ERROR_SR_OS_NOT_GENERATE_TIMEPOINT_ERROR'],
        'SR_FS_NOT_GENERATE_TIMEPOINT_ERROR' => Xphp::$_lang['WEB_ERROR_SR_FS_NOT_GENERATE_TIMEPOINT_ERROR'],
        'SR_DB_NOT_GENERATE_TIMEPOINT_ERROR' => Xphp::$_lang['WEB_ERROR_SR_DB_NOT_GENERATE_TIMEPOINT_ERROR'],
        'SR_INSTANT_RECOVERY_NFS_SERVER_IP_NOT_MATCH_ERROR' => Xphp::$_lang['WEB_ERROR_SR_INSTANT_RECOVERY_NFS_SERVER_IP_NOT_MATCH_ERROR'],
        'SR_VIRTUAL_ENVIRMENT_OFFLINE_ERROR' => Xphp::$_lang['WEB_ERROR_SR_VIRTUAL_ENVIRMENT_OFFLINE_ERROR'],
        'SR_VIRTUAL_LAB_PROXY_CREATE_FLOPPY_FILE_ERROR' => Xphp::$_lang['WEB_ERROR_SR_VIRTUAL_LAB_PROXY_CREATE_FLOPPY_FILE_ERROR'],
        'SR_ADD_IP_ROUTE_ERROR' => Xphp::$_lang['WEB_ERROR_SR_ADD_IP_ROUTE_ERROR'],
        'SR_DEL_IP_ROUTE_ERROR' => Xphp::$_lang['WEB_ERROR_SR_DEL_IP_ROUTE_ERROR'],
        'SR_NOT_SUPPORT_HYPERVISOR_ERROR' => Xphp::$_lang['WEB_ERROR_SR_NOT_SUPPORT_HYPERVISOR_ERROR'],
        'SR_NOT_SUPPORT_MODULE_TYPE_ERROR' => Xphp::$_lang['WEB_ERROR_SR_NOT_SUPPORT_MODULE_TYPE_ERROR'],



        'VM_DEPLOY_SURE_BACKUP_NETWORK_ERROR' => Xphp::$_lang['WEB_ERROR_VM_DEPLOY_SURE_BACKUP_NETWORK_ERROR'],
        'VM_ADD_IP_ROUTE_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ADD_IP_ROUTE_ERROR'],
        'VM_DEL_IP_ROUTE_ERROR' => Xphp::$_lang['WEB_ERROR_VM_DEL_IP_ROUTE_ERROR'],
        'VM_GET_VIRTUAL_LAB_INFO_ERROR' => Xphp::$_lang['WEB_ERROR_VM_GET_VIRTUAL_LAB_INFO_ERROR'],
        'PUBLIC_CLOUD_REGION_FORBIDDENT' => Xphp::$_lang['WEB_ERROR_PUBLIC_CLOUD_REGION_FORBIDDENT'],
        'PUBLIC_CLOUD_INSTANCETYPE_NOT_AVALIABLE' => Xphp::$_lang['WEB_ERROR_PUBLIC_CLOUD_INSTANCETYPE_NOT_AVALIABLE'],
        'KVM_H3C_CAS_CVD_CREATE_CONNECTION_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_H3C_CAS_CVD_CREATE_CONNECTION_ERROR'],


        'KVM_LIBVIRT_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LIBVIRT_ERROR'],
        'KVM_LOGIN_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LOGIN_ERROR'],
        'KVM_LIBVIRT_NOT_CONNECT_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LIBVIRT_NOT_CONNECT_ERROR'],
        'KVM_LOGIN_USERNAME_PASSWORD_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LOGIN_USERNAME_PASSWORD_ERROR'],
        'KVM_NOT_SUPPORT_EXTERNAL_SNAPSHOT_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_NOT_SUPPORT_EXTERNAL_SNAPSHOT_ERROR'],
        'KVM_DISK_OPEN_MODE_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_DISK_OPEN_MODE_ERROR'],
        'KVM_QCOW2_BACKINGSTORE_NOT_FOUND_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_QCOW2_BACKINGSTORE_NOT_FOUND_ERROR'],
        'KVM_NOT_SUPPORT_DISK_TYPE_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_NOT_SUPPORT_DISK_TYPE_ERROR'],
        'KVM_DISK_NUM_CHANGE_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_DISK_NUM_CHANGE_ERROR'],

        'VM_QCOW2_READ_CLUSTER_ERROR' => Xphp::$_lang['WEB_ERROR_VM_QCOW2_READ_CLUSTER_ERROR'],
        'VM_QCOW2_READ_QCOW2_ERROR' => Xphp::$_lang['WEB_ERROR_VM_QCOW2_READ_QCOW2_ERROR'],
        'VM_QCOW2_CLUSTER_ERROR' => Xphp::$_lang['WEB_ERROR_VM_QCOW2_CLUSTER_ERROR'],
        'VM_QCOW2_DESIGNATEDDATA_ERROR' => Xphp::$_lang['WEB_ERROR_VM_QCOW2_DESIGNATEDDATA_ERROR'],
        'VM_QCOW2_INTERNALSNAPSHOT_ERROR' => Xphp::$_lang['WEB_ERROR_VM_QCOW2_INTERNALSNAPSHOT_ERROR'],
        'VM_QCOW2_L2TABLEOFFSET_ERROR' => Xphp::$_lang['WEB_ERROR_VM_QCOW2_L2TABLEOFFSET_ERROR'],
        'VM_QCOW2_UNCOMPRESSDATA_ERROR' => Xphp::$_lang['WEB_ERROR_VM_QCOW2_UNCOMPRESSDATA_ERROR'],
        'VM_QCOW2_OPEN_QCOW2FILE_ERROR' => Xphp::$_lang['WEB_ERROR_VM_QCOW2_OPEN_QCOW2FILE_ERROR'],
        'VM_QCOW2_HEADERWRONG_ERROR' => Xphp::$_lang['WEB_ERROR_VM_QCOW2_HEADERWRONG_ERROR'],
        'VM_QCOW2_LOAD_L1TABLE_ERROR' => Xphp::$_lang['WEB_ERROR_VM_QCOW2_LOAD_L1TABLE_ERROR'],
        'VM_QCOW2_CLUSTER_BITMAP_NOT_MATCH_ERROR' => Xphp::$_lang['WEB_ERROR_VM_QCOW2_CLUSTER_BITMAP_NOT_MATCH_ERROR'],

        'KVM_DISK_CLUSTER_SIZE_NOT_EQUAL_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_DISK_CLUSTER_SIZE_NOT_EQUAL_ERROR'],
        'KVM_NOT_SUPPORT_BACKUP_LEVEL_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_NOT_SUPPORT_BACKUP_LEVEL_ERROR'],
        'KVM_CONTAINER_FULL_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_CONTAINER_FULL_ERROR'],
        'KVM_DISK_VIRTUAL_SIZE_CHANGE_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_DISK_VIRTUAL_SIZE_CHANGE_ERROR'],
        'KVM_DISK_NOT_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_DISK_NOT_EXIST_ERROR'],
        'KVM_STORAGE_POOL_NOT_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_STORAGE_POOL_NOT_EXIST_ERROR'],
        'KVM_ADVANCE_RECOVERY_VM_CONFIG_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_ADVANCE_RECOVERY_VM_CONFIG_ERROR'],
        'KVM_NO_PHYSICAL_NETWORK_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_NO_PHYSICAL_NETWORK_ERROR'],

        // kvm libvirt error wrapper
        'KVM_LIBVIRT_NO_MEMORY_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LIBVIRT_NO_MEMORY_ERROR'],
        'KVM_LIBVIRT_NOT_SUPPORT_FUNCTION_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LIBVIRT_NOT_SUPPORT_FUNCTION_ERROR'],
        'KVM_LIBVIRT_UNKNOW_HOST_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LIBVIRT_UNKNOW_HOST_ERROR'],
        'KVM_LIBVIRT_CONNECT_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LIBVIRT_CONNECT_ERROR'],
        'KVM_LIBVIRT_INVALID_DOMAIN_OBJECT_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LIBVIRT_INVALID_DOMAIN_OBJECT_ERROR'],
        'KVM_LIBVIRT_INVALID_PARAM_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LIBVIRT_INVALID_PARAM_ERROR'],
        'KVM_LIBVIRT_OPERATION_FAILED_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LIBVIRT_OPERATION_FAILED_ERROR'],
        'KVM_LIBVIRT_HTTP_GET_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LIBVIRT_HTTP_GET_ERROR'],
        'KVM_LIBVIRT_HTTP_POST_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LIBVIRT_HTTP_POST_ERROR'],
        'KVM_LIBVIRT_HTTP_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LIBVIRT_HTTP_ERROR'],
        'KVM_LIBVIRT_SEXPR_SERIAL_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LIBVIRT_SEXPR_SERIAL_ERROR'],
        'KVM_LIBVIRT_UNKNOW_OS_TYPE_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LIBVIRT_UNKNOW_OS_TYPE_ERROR'],
        'KVM_LIBVIRT_NO_KERNAL_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LIBVIRT_NO_KERNAL_ERROR'],
        'KVM_LIBVIRT_NO_ROOT_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LIBVIRT_NO_ROOT_ERROR'],
        'KVM_LIBVIRT_NO_SOURCE_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LIBVIRT_NO_SOURCE_ERROR'],
        'KVM_LIBVIRT_NO_TARGET_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LIBVIRT_NO_TARGET_ERROR'],
        'KVM_LIBVIRT_NO_DOMAIN_NAME_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LIBVIRT_NO_DOMAIN_NAME_ERROR'],
        'KVM_LIBVIRT_NO_DOMAIN_OS_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LIBVIRT_NO_DOMAIN_OS_ERROR'],
        'KVM_LIBVIRT_NO_DOMAIN_DEVICE_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LIBVIRT_NO_DOMAIN_DEVICE_ERROR'],
        'KVM_LIBVIRT_TOO_MANY_DRIVER_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LIBVIRT_TOO_MANY_DRIVER_ERROR'],
        'KVM_LIBVIRT_NOT_SUPPORT_BY_DRIVERS_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LIBVIRT_NOT_SUPPORT_BY_DRIVERS_ERROR'],
        'KVM_LIBVIRT_XML_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LIBVIRT_XML_ERROR'],
        'KVM_LIBVIRT_DOMAIN_ALREADY_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LIBVIRT_DOMAIN_ALREADY_EXIST_ERROR'],
        'KVM_LIBVIRT_OPERATION_DENIED_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LIBVIRT_OPERATION_DENIED_ERROR'],
        'KVM_LIBVIRT_OPEN_CONF_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LIBVIRT_OPEN_CONF_ERROR'],
        'KVM_LIBVIRT_READ_CONF_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LIBVIRT_READ_CONF_ERROR'],
        'KVM_LIBVIRT_PARSE_CONF_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LIBVIRT_PARSE_CONF_ERROR'],
        'KVM_LIBVIRT_CONF_SYNTAX_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LIBVIRT_CONF_SYNTAX_ERROR'],
        'KVM_LIBVIRT_WRITE_CONF_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LIBVIRT_WRITE_CONF_ERROR'],
        'KVM_LIBVIRT_DETAIL_XML_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LIBVIRT_DETAIL_XML_ERROR'],
        'KVM_LIBVIRT_INVALID_NETWORK_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LIBVIRT_INVALID_NETWORK_ERROR'],
        'KVM_LIBVIRT_NETWORK_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LIBVIRT_NETWORK_EXIST_ERROR'],
        'KVM_LIBVIRT_SYSTEM_CALL_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LIBVIRT_SYSTEM_CALL_ERROR'],
        'KVM_LIBVIRT_RPC_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LIBVIRT_RPC_ERROR'],
        'KVM_LIBVIRT_GNUTLS_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LIBVIRT_GNUTLS_ERROR'],
        'KVM_LIBVIRT_START_NETWORK_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LIBVIRT_START_NETWORK_ERROR'],
        'KVM_LIBVIRT_NO_DOMAIN_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LIBVIRT_NO_DOMAIN_ERROR'],
        'KVM_LIBVIRT_NO_NETWORK_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LIBVIRT_NO_NETWORK_ERROR'],
        'KVM_LIBVIRT_INVALID_MAC_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LIBVIRT_INVALID_MAC_ERROR'],
        'KVM_LIBVIRT_AUTH_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LIBVIRT_AUTH_ERROR'],
        'KVM_LIBVIRT_INVALID_STORAGE_POOL_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LIBVIRT_INVALID_STORAGE_POOL_ERROR'],
        'KVM_LIBVIRT_INVALID_STORAGE_VOL_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LIBVIRT_INVALID_STORAGE_VOL_ERROR'],
        'KVM_LIBVIRT_START_STORAGE_POOL_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LIBVIRT_START_STORAGE_POOL_ERROR'],
        'KVM_LIBVIRT_NO_STORAGE_POOL_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LIBVIRT_NO_STORAGE_POOL_ERROR'],
        'KVM_LIBVIRT_NO_STORAGE_VOL_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LIBVIRT_NO_STORAGE_VOL_ERROR'],
        'KVM_LIBVIRT_START_NODE_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LIBVIRT_START_NODE_ERROR'],
        'KVM_LIBVIRT_INVALID_NODE_DEVICE_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LIBVIRT_INVALID_NODE_DEVICE_ERROR'],
        'KVM_LIBVIRT_NO_NODE_DEVICE_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LIBVIRT_NO_NODE_DEVICE_ERROR'],
        'KVM_LIBVIRT_NO_SECURITY_MODEL_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LIBVIRT_NO_SECURITY_MODEL_ERROR'],
        'KVM_LIBVIRT_OPERATION_INVALID_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LIBVIRT_OPERATION_INVALID_ERROR'],
        'KVM_LIBVIRT_START_INTERFACE_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LIBVIRT_START_INTERFACE_ERROR'],
        'KVM_LIBVIRT_NO_INTERFACE_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LIBVIRT_NO_INTERFACE_ERROR'],
        'KVM_LIBVIRT_INVALID_INTERFACE_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LIBVIRT_INVALID_INTERFACE_ERROR'],
        'KVM_LIBVIRT_MULTIPLE_INTERFACE_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LIBVIRT_MULTIPLE_INTERFACE_ERROR'],
        'KVM_LIBVIRT_START_NWFILTER_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LIBVIRT_START_NWFILTER_ERROR'],
        'KVM_LIBVIRT_INVALID_NWFILTER_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LIBVIRT_INVALID_NWFILTER_ERROR'],
        'KVM_LIBVIRT_NO_NWFILTER_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LIBVIRT_NO_NWFILTER_ERROR'],
        'KVM_LIBVIRT_BUILD_FIREWALL_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LIBVIRT_BUILD_FIREWALL_ERROR'],
        'KVM_LIBVIRT_START_SECRET_STORAGE_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LIBVIRT_START_SECRET_STORAGE_ERROR'],
        'KVM_LIBVIRT_INVALID_SECRET_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LIBVIRT_INVALID_SECRET_ERROR'],
        'KVM_LIBVIRT_NO_SECRET_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LIBVIRT_NO_SECRET_ERROR'],
        'KVM_LIBVIRT_NOT_SUPPORT_CONFIG_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LIBVIRT_NOT_SUPPORT_CONFIG_ERROR'],
        'KVM_LIBVIRT_OPERATION_TIMEOUT_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LIBVIRT_OPERATION_TIMEOUT_ERROR'],
        'KVM_LIBVIRT_MIGRATE_PERSIST_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LIBVIRT_MIGRATE_PERSIST_ERROR'],
        'KVM_LIBVIRT_HOOK_SCRIPT_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LIBVIRT_HOOK_SCRIPT_ERROR'],
        'KVM_LIBVIRT_INVALID_DOMAIN_SNAPSHOT_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LIBVIRT_INVALID_DOMAIN_SNAPSHOT_ERROR'],
        'KVM_LIBVIRT_NO_DOMAIN_SNAPSHOT_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LIBVIRT_NO_DOMAIN_SNAPSHOT_ERROR'],
        'KVM_LIBVIRT_INVALID_STREAM_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LIBVIRT_INVALID_STREAM_ERROR'],
        'KVM_LIBVIRT_ARGUMENT_UNSUPPORTED_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LIBVIRT_ARGUMENT_UNSUPPORTED_ERROR'],
        'KVM_LIBVIRT_STORAGE_PROBE_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LIBVIRT_STORAGE_PROBE_ERROR'],
        'KVM_LIBVIRT_STORAGE_ALREADY_BUILD_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LIBVIRT_STORAGE_ALREADY_BUILD_ERROR'],
        'KVM_LIBVIRT_SNAPSHOT_REVERT_RISKY_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LIBVIRT_SNAPSHOT_REVERT_RISKY_ERROR'],
        'KVM_LIBVIRT_OPERATION_ABORTED_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LIBVIRT_OPERATION_ABORTED_ERROR'],
        'KVM_LIBVIRT_AUTH_CANCELLED_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LIBVIRT_AUTH_CANCELLED_ERROR'],
        'KVM_LIBVIRT_NO_DOMAIN_METADATA_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LIBVIRT_NO_DOMAIN_METADATA_ERROR'],
        'KVM_LIBVIRT_MIGRATE_UNSAFE_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LIBVIRT_MIGRATE_UNSAFE_ERROR'],
        'KVM_LIBVIRT_OVERFLOW_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LIBVIRT_OVERFLOW_ERROR'],
        'KVM_LIBVIRT_BLOCK_COPY_ACTIVE_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LIBVIRT_BLOCK_COPY_ACTIVE_ERROR'],
        'KVM_LIBVIRT_OPERATION_UNSUPPORTED_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LIBVIRT_OPERATION_UNSUPPORTED_ERROR'],
        'KVM_LIBVIRT_SSH_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LIBVIRT_SSH_ERROR'],
        'KVM_LIBVIRT_AGENT_UNRESPNSIVE_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LIBVIRT_AGENT_UNRESPNSIVE_ERROR'],
        'KVM_LIBVIRT_RESOURCE_BUSY_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LIBVIRT_RESOURCE_BUSY_ERROR'],
        'KVM_LIBVIRT_ACCESS_DENIED_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LIBVIRT_ACCESS_DENIED_ERROR'],
        'KVM_LIBVIRT_DBUS_SERVICE_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LIBVIRT_DBUS_SERVICE_ERROR'],
        'KVM_LIBVIRT_STORAGE_VOLUME_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LIBVIRT_STORAGE_VOLUME_EXIST_ERROR'],
        'KVM_LIBVIRT_CPU_INCOMPATIBLE_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LIBVIRT_CPU_INCOMPATIBLE_ERROR'],
        'KVM_LIBVIRT_XML_INVALID_SCHEMA_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LIBVIRT_XML_INVALID_SCHEMA_ERROR'],
        'KVM_LIBVIRT_AUTH_UNAVAILABLE_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LIBVIRT_AUTH_UNAVAILABLE_ERROR'],
        'KVM_LIBVIRT_NO_SERVER_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LIBVIRT_NO_SERVER_ERROR'],
        'KVM_LIBVIRT_NO_CLIENT_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LIBVIRT_NO_CLIENT_ERROR'],
        'KVM_LIBVIRT_AGENT_UNSYNCED_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LIBVIRT_AGENT_UNSYNCED_ERROR'],
        'KVM_LIBVIRT_LIBSSH_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_LIBVIRT_LIBSSH_ERROR'],

        'KVM_NOT_SUPPORT_STORAGE_POOL_TYPE_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_NOT_SUPPORT_STORAGE_POOL_TYPE_ERROR'],
        'XENSERVER_ACTIVATION_WHILE_NOT_FREE' => Xphp::$_lang['WEB_ERROR_XENSERVER_ACTIVATION_WHILE_NOT_FREE'],
        'XENSERVER_AUTH_ALREADY_ENABLED' => Xphp::$_lang['WEB_ERROR_XENSERVER_AUTH_ALREADY_ENABLED'],
        'XENSERVER_AUTH_DISABLE_FAILED' => Xphp::$_lang['WEB_ERROR_XENSERVER_AUTH_DISABLE_FAILED'],
        'XENSERVER_AUTH_DISABLE_FAILED_PERMISSION_DENIED' => Xphp::$_lang['WEB_ERROR_XENSERVER_AUTH_DISABLE_FAILED_PERMISSION_DENIED'],
        'XENSERVER_AUTH_DISABLE_FAILED_WRONG_CREDENTIALS' => Xphp::$_lang['WEB_ERROR_XENSERVER_AUTH_DISABLE_FAILED_WRONG_CREDENTIALS'],
        'XENSERVER_AUTH_ENABLE_FAILED' => Xphp::$_lang['WEB_ERROR_XENSERVER_AUTH_ENABLE_FAILED'],
        'XENSERVER_AUTH_ENABLE_FAILED_DOMAIN_LOOKUP_FAILED' => Xphp::$_lang['WEB_ERROR_XENSERVER_AUTH_ENABLE_FAILED_DOMAIN_LOOKUP_FAILED'],
        'XENSERVER_AUTH_ENABLE_FAILED_PERMISSION_DENIED' => Xphp::$_lang['WEB_ERROR_XENSERVER_AUTH_ENABLE_FAILED_PERMISSION_DENIED'],
        'XENSERVER_AUTH_ENABLE_FAILED_UNAVAILABLE' => Xphp::$_lang['WEB_ERROR_XENSERVER_AUTH_ENABLE_FAILED_UNAVAILABLE'],
        'XENSERVER_AUTH_ENABLE_FAILED_WRONG_CREDENTIALS' => Xphp::$_lang['WEB_ERROR_XENSERVER_AUTH_ENABLE_FAILED_WRONG_CREDENTIALS'],
        'XENSERVER_AUTH_IS_DISABLED' => Xphp::$_lang['WEB_ERROR_XENSERVER_AUTH_IS_DISABLED'],
        'XENSERVER_AUTH_SERVICE_ERROR' => Xphp::$_lang['WEB_ERROR_XENSERVER_AUTH_SERVICE_ERROR'],
        'XENSERVER_AUTH_UNKNOWN_TYPE' => Xphp::$_lang['WEB_ERROR_XENSERVER_AUTH_UNKNOWN_TYPE'],
        'XENSERVER_BACKUP_SCRIPT_FAILED' => Xphp::$_lang['WEB_ERROR_XENSERVER_BACKUP_SCRIPT_FAILED'],
        'XENSERVER_BOOTLOADER_FAILED' => Xphp::$_lang['WEB_ERROR_XENSERVER_BOOTLOADER_FAILED'],
        'XENSERVER_BRIDGE_NOT_AVAILABLE' => Xphp::$_lang['WEB_ERROR_XENSERVER_BRIDGE_NOT_AVAILABLE'],
        'XENSERVER_CANNOT_ADD_TUNNEL_TO_BOND_SLAVE' => Xphp::$_lang['WEB_ERROR_XENSERVER_CANNOT_ADD_TUNNEL_TO_BOND_SLAVE'],
        'XENSERVER_CANNOT_ADD_VLAN_TO_BOND_SLAVE' => Xphp::$_lang['WEB_ERROR_XENSERVER_CANNOT_ADD_VLAN_TO_BOND_SLAVE'],
        'XENSERVER_CANNOT_CHANGE_PIF_PROPERTIES' => Xphp::$_lang['WEB_ERROR_XENSERVER_CANNOT_CHANGE_PIF_PROPERTIES'],
        'XENSERVER_CANNOT_CONTACT_HOST' => Xphp::$_lang['WEB_ERROR_XENSERVER_CANNOT_CONTACT_HOST'],
        'XENSERVER_CANNOT_CREATE_STATE_FILE' => Xphp::$_lang['WEB_ERROR_XENSERVER_CANNOT_CREATE_STATE_FILE'],
        'XENSERVER_CANNOT_DESTROY_DISASTER_RECOVERY_TASK' => Xphp::$_lang['WEB_ERROR_XENSERVER_CANNOT_DESTROY_DISASTER_RECOVERY_TASK'],
        'XENSERVER_CANNOT_DESTROY_SYSTEM_NETWORK' => Xphp::$_lang['WEB_ERROR_XENSERVER_CANNOT_DESTROY_SYSTEM_NETWORK'],
        'XENSERVER_CANNOT_ENABLE_REDO_LOG' => Xphp::$_lang['WEB_ERROR_XENSERVER_CANNOT_ENABLE_REDO_LOG'],
        'XENSERVER_CANNOT_EVACUATE_HOST' => Xphp::$_lang['WEB_ERROR_XENSERVER_CANNOT_EVACUATE_HOST'],
        'XENSERVER_CANNOT_FETCH_PATCH' => Xphp::$_lang['WEB_ERROR_XENSERVER_CANNOT_FETCH_PATCH'],
        'XENSERVER_CANNOT_FIND_OEM_BACKUP_PARTITION' => Xphp::$_lang['WEB_ERROR_XENSERVER_CANNOT_FIND_OEM_BACKUP_PARTITION'],
        'XENSERVER_CANNOT_FIND_PATCH' => Xphp::$_lang['WEB_ERROR_XENSERVER_CANNOT_FIND_PATCH'],
        'XENSERVER_CANNOT_FIND_STATE_PARTITION' => Xphp::$_lang['WEB_ERROR_XENSERVER_CANNOT_FIND_STATE_PARTITION'],
        'XENSERVER_CANNOT_PLUG_BOND_SLAVE' => Xphp::$_lang['WEB_ERROR_XENSERVER_CANNOT_PLUG_BOND_SLAVE'],
        'XENSERVER_CANNOT_PLUG_VIF' => Xphp::$_lang['WEB_ERROR_XENSERVER_CANNOT_PLUG_VIF'],
        'XENSERVER_CANNOT_RESET_CONTROL_DOMAIN' => Xphp::$_lang['WEB_ERROR_XENSERVER_CANNOT_RESET_CONTROL_DOMAIN'],
        'XENSERVER_CERTIFICATE_ALREADY_EXISTS' => Xphp::$_lang['WEB_ERROR_XENSERVER_CERTIFICATE_ALREADY_EXISTS'],
        'XENSERVER_CERTIFICATE_CORRUPT' => Xphp::$_lang['WEB_ERROR_XENSERVER_CERTIFICATE_CORRUPT'],
        'XENSERVER_CERTIFICATE_DOES_NOT_EXIST' => Xphp::$_lang['WEB_ERROR_XENSERVER_CERTIFICATE_DOES_NOT_EXIST'],
        'XENSERVER_CERTIFICATE_LIBRARY_CORRUPT' => Xphp::$_lang['WEB_ERROR_XENSERVER_CERTIFICATE_LIBRARY_CORRUPT'],
        'XENSERVER_CERTIFICATE_NAME_INVALID' => Xphp::$_lang['WEB_ERROR_XENSERVER_CERTIFICATE_NAME_INVALID'],
        'XENSERVER_CHANGE_PASSWORD_REJECTED' => Xphp::$_lang['WEB_ERROR_XENSERVER_CHANGE_PASSWORD_REJECTED'],
        'XENSERVER_COULD_NOT_FIND_NETWORK_INTERFACE_WITH_SPECIFIED_DEVICE_NAME_AND_MAC_ADDRESS' => Xphp::$_lang['WEB_ERROR_XENSERVER_COULD_NOT_FIND_NETWORK_INTERFACE_WITH_SPECIFIED_DEVICE_NAME_AND_MAC_ADDRESS'],
        'XENSERVER_COULD_NOT_IMPORT_DATABASE' => Xphp::$_lang['WEB_ERROR_XENSERVER_COULD_NOT_IMPORT_DATABASE'],
        'XENSERVER_CPU_FEATURE_MASKING_NOT_SUPPORTED' => Xphp::$_lang['WEB_ERROR_XENSERVER_CPU_FEATURE_MASKING_NOT_SUPPORTED'],
        'XENSERVER_CRL_ALREADY_EXISTS' => Xphp::$_lang['WEB_ERROR_XENSERVER_CRL_ALREADY_EXISTS'],
        'XENSERVER_CRL_CORRUPT' => Xphp::$_lang['WEB_ERROR_XENSERVER_CRL_CORRUPT'],
        'XENSERVER_CRL_DOES_NOT_EXIST' => Xphp::$_lang['WEB_ERROR_XENSERVER_CRL_DOES_NOT_EXIST'],
        'XENSERVER_CRL_NAME_INVALID' => Xphp::$_lang['WEB_ERROR_XENSERVER_CRL_NAME_INVALID'],
        'XENSERVER_DB_UNIQUENESS_CONSTRAINT_VIOLATION' => Xphp::$_lang['WEB_ERROR_XENSERVER_DB_UNIQUENESS_CONSTRAINT_VIOLATION'],
        'XENSERVER_DEFAULT_SR_NOT_FOUND' => Xphp::$_lang['WEB_ERROR_XENSERVER_DEFAULT_SR_NOT_FOUND'],
        'XENSERVER_DEVICE_ALREADY_ATTACHED' => Xphp::$_lang['WEB_ERROR_XENSERVER_DEVICE_ALREADY_ATTACHED'],
        'XENSERVER_DEVICE_ALREADY_DETACHED' => Xphp::$_lang['WEB_ERROR_XENSERVER_DEVICE_ALREADY_DETACHED'],
        'XENSERVER_DEVICE_ALREADY_EXISTS' => Xphp::$_lang['WEB_ERROR_XENSERVER_DEVICE_ALREADY_EXISTS'],
        'XENSERVER_DEVICE_ATTACH_TIMEOUT' => Xphp::$_lang['WEB_ERROR_XENSERVER_DEVICE_ATTACH_TIMEOUT'],
        'XENSERVER_DEVICE_DETACH_REJECTED' => Xphp::$_lang['WEB_ERROR_XENSERVER_DEVICE_DETACH_REJECTED'],
        'XENSERVER_DEVICE_DETACH_TIMEOUT' => Xphp::$_lang['WEB_ERROR_XENSERVER_DEVICE_DETACH_TIMEOUT'],
        'XENSERVER_DEVICE_NOT_ATTACHED' => Xphp::$_lang['WEB_ERROR_XENSERVER_DEVICE_NOT_ATTACHED'],
        'XENSERVER_DISK_VBD_MUST_BE_READWRITE_FOR_HVM' => Xphp::$_lang['WEB_ERROR_XENSERVER_DISK_VBD_MUST_BE_READWRITE_FOR_HVM'],
        'XENSERVER_DOMAIN_BUILDER_ERROR' => Xphp::$_lang['WEB_ERROR_XENSERVER_DOMAIN_BUILDER_ERROR'],
        'XENSERVER_DOMAIN_EXISTS' => Xphp::$_lang['WEB_ERROR_XENSERVER_DOMAIN_EXISTS'],
        'XENSERVER_DUPLICATE_PIF_DEVICE_NAME' => Xphp::$_lang['WEB_ERROR_XENSERVER_DUPLICATE_PIF_DEVICE_NAME'],
        'XENSERVER_DUPLICATE_VM' => Xphp::$_lang['WEB_ERROR_XENSERVER_DUPLICATE_VM'],
        'XENSERVER_EVENTS_LOST' => Xphp::$_lang['WEB_ERROR_XENSERVER_EVENTS_LOST'],
        'XENSERVER_EVENT_FROM_TOKEN_PARSE_FAILURE' => Xphp::$_lang['WEB_ERROR_XENSERVER_EVENT_FROM_TOKEN_PARSE_FAILURE'],
        'XENSERVER_EVENT_SUBSCRIPTION_PARSE_FAILURE' => Xphp::$_lang['WEB_ERROR_XENSERVER_EVENT_SUBSCRIPTION_PARSE_FAILURE'],
        'XENSERVER_FEATURE_REQUIRES_HVM' => Xphp::$_lang['WEB_ERROR_XENSERVER_FEATURE_REQUIRES_HVM'],
        'XENSERVER_FEATURE_RESTRICTED' => Xphp::$_lang['WEB_ERROR_XENSERVER_FEATURE_RESTRICTED'],
        'XENSERVER_FIELD_TYPE_ERROR' => Xphp::$_lang['WEB_ERROR_XENSERVER_FIELD_TYPE_ERROR'],
        'XENSERVER_GPU_GROUP_CONTAINS_NO_PGPUS' => Xphp::$_lang['WEB_ERROR_XENSERVER_GPU_GROUP_CONTAINS_NO_PGPUS'],
        'XENSERVER_GPU_GROUP_CONTAINS_PGPU' => Xphp::$_lang['WEB_ERROR_XENSERVER_GPU_GROUP_CONTAINS_PGPU'],
        'XENSERVER_GPU_GROUP_CONTAINS_VGPU' => Xphp::$_lang['WEB_ERROR_XENSERVER_GPU_GROUP_CONTAINS_VGPU'],
        'XENSERVER_HA_ABORT_NEW_MASTER' => Xphp::$_lang['WEB_ERROR_XENSERVER_HA_ABORT_NEW_MASTER'],
        'XENSERVER_HA_CANNOT_CHANGE_BOND_STATUS_OF_MGMT_IFACE' => Xphp::$_lang['WEB_ERROR_XENSERVER_HA_CANNOT_CHANGE_BOND_STATUS_OF_MGMT_IFACE'],
        'XENSERVER_HA_CONSTRAINT_VIOLATION_NETWORK_NOT_SHARED' => Xphp::$_lang['WEB_ERROR_XENSERVER_HA_CONSTRAINT_VIOLATION_NETWORK_NOT_SHARED'],
        'XENSERVER_HA_CONSTRAINT_VIOLATION_SR_NOT_SHARED' => Xphp::$_lang['WEB_ERROR_XENSERVER_HA_CONSTRAINT_VIOLATION_SR_NOT_SHARED'],
        'XENSERVER_HA_FAILED_TO_FORM_LIVESET' => Xphp::$_lang['WEB_ERROR_XENSERVER_HA_FAILED_TO_FORM_LIVESET'],
        'XENSERVER_HA_HEARTBEAT_DAEMON_STARTUP_FAILED' => Xphp::$_lang['WEB_ERROR_XENSERVER_HA_HEARTBEAT_DAEMON_STARTUP_FAILED'],
        'XENSERVER_HA_HOST_CANNOT_ACCESS_STATEFILE' => Xphp::$_lang['WEB_ERROR_XENSERVER_HA_HOST_CANNOT_ACCESS_STATEFILE'],
        'XENSERVER_HA_HOST_CANNOT_SEE_PEERS' => Xphp::$_lang['WEB_ERROR_XENSERVER_HA_HOST_CANNOT_SEE_PEERS'],
        'XENSERVER_HA_HOST_IS_ARMED' => Xphp::$_lang['WEB_ERROR_XENSERVER_HA_HOST_IS_ARMED'],
        'XENSERVER_HA_IS_ENABLED' => Xphp::$_lang['WEB_ERROR_XENSERVER_HA_IS_ENABLED'],
        'XENSERVER_HA_LOST_STATEFILE' => Xphp::$_lang['WEB_ERROR_XENSERVER_HA_LOST_STATEFILE'],
        'XENSERVER_HA_NOT_ENABLED' => Xphp::$_lang['WEB_ERROR_XENSERVER_HA_NOT_ENABLED'],
        'XENSERVER_HA_NOT_INSTALLED' => Xphp::$_lang['WEB_ERROR_XENSERVER_HA_NOT_INSTALLED'],
        'XENSERVER_HA_NO_PLAN' => Xphp::$_lang['WEB_ERROR_XENSERVER_HA_NO_PLAN'],
        'XENSERVER_HA_OPERATION_WOULD_BREAK_FAILOVER_PLAN' => Xphp::$_lang['WEB_ERROR_XENSERVER_HA_OPERATION_WOULD_BREAK_FAILOVER_PLAN'],
        'XENSERVER_HA_POOL_IS_ENABLED_BUT_HOST_IS_DISABLED' => Xphp::$_lang['WEB_ERROR_XENSERVER_HA_POOL_IS_ENABLED_BUT_HOST_IS_DISABLED'],
        'XENSERVER_HA_SHOULD_BE_FENCED' => Xphp::$_lang['WEB_ERROR_XENSERVER_HA_SHOULD_BE_FENCED'],
        'XENSERVER_HA_TOO_FEW_HOSTS' => Xphp::$_lang['WEB_ERROR_XENSERVER_HA_TOO_FEW_HOSTS'],
        'XENSERVER_HOSTS_NOT_COMPATIBLE' => Xphp::$_lang['WEB_ERROR_XENSERVER_HOSTS_NOT_COMPATIBLE'],
        'XENSERVER_HOSTS_NOT_HOMOGENEOUS' => Xphp::$_lang['WEB_ERROR_XENSERVER_HOSTS_NOT_HOMOGENEOUS'],
        'XENSERVER_HOST_BROKEN' => Xphp::$_lang['WEB_ERROR_XENSERVER_HOST_BROKEN'],
        'XENSERVER_HOST_CANNOT_ATTACH_NETWORK' => Xphp::$_lang['WEB_ERROR_XENSERVER_HOST_CANNOT_ATTACH_NETWORK'],
        'XENSERVER_HOST_CANNOT_DESTROY_SELF' => Xphp::$_lang['WEB_ERROR_XENSERVER_HOST_CANNOT_DESTROY_SELF'],
        'XENSERVER_HOST_CANNOT_READ_METRICS' => Xphp::$_lang['WEB_ERROR_XENSERVER_HOST_CANNOT_READ_METRICS'],
        'XENSERVER_HOST_CD_DRIVE_EMPTY' => Xphp::$_lang['WEB_ERROR_XENSERVER_HOST_CD_DRIVE_EMPTY'],
        'XENSERVER_HOST_DISABLED' => Xphp::$_lang['WEB_ERROR_XENSERVER_HOST_DISABLED'],
        'XENSERVER_HOST_DISABLED_UNTIL_REBOOT' => Xphp::$_lang['WEB_ERROR_XENSERVER_HOST_DISABLED_UNTIL_REBOOT'],
        'XENSERVER_HOST_EVACUATE_IN_PROGRESS' => Xphp::$_lang['WEB_ERROR_XENSERVER_HOST_EVACUATE_IN_PROGRESS'],
        'XENSERVER_HOST_HAS_NO_MANAGEMENT_IP' => Xphp::$_lang['WEB_ERROR_XENSERVER_HOST_HAS_NO_MANAGEMENT_IP'],
        'XENSERVER_HOST_HAS_RESIDENT_VMS' => Xphp::$_lang['WEB_ERROR_XENSERVER_HOST_HAS_RESIDENT_VMS'],
        'XENSERVER_HOST_IN_EMERGENCY_MODE' => Xphp::$_lang['WEB_ERROR_XENSERVER_HOST_IN_EMERGENCY_MODE'],
        'XENSERVER_HOST_IN_USE' => Xphp::$_lang['WEB_ERROR_XENSERVER_HOST_IN_USE'],
        'XENSERVER_HOST_IS_LIVE' => Xphp::$_lang['WEB_ERROR_XENSERVER_HOST_IS_LIVE'],
        'XENSERVER_HOST_ITS_OWN_SLAVE' => Xphp::$_lang['WEB_ERROR_XENSERVER_HOST_ITS_OWN_SLAVE'],
        'XENSERVER_HOST_MASTER_CANNOT_TALK_BACK' => Xphp::$_lang['WEB_ERROR_XENSERVER_HOST_MASTER_CANNOT_TALK_BACK'],
        'XENSERVER_HOST_NAME_INVALID' => Xphp::$_lang['WEB_ERROR_XENSERVER_HOST_NAME_INVALID'],
        'XENSERVER_HOST_NOT_DISABLED' => Xphp::$_lang['WEB_ERROR_XENSERVER_HOST_NOT_DISABLED'],
        'XENSERVER_HOST_NOT_ENOUGH_FREE_MEMORY' => Xphp::$_lang['WEB_ERROR_XENSERVER_HOST_NOT_ENOUGH_FREE_MEMORY'],
        'XENSERVER_HOST_NOT_LIVE' => Xphp::$_lang['WEB_ERROR_XENSERVER_HOST_NOT_LIVE'],
        'XENSERVER_HOST_OFFLINE' => Xphp::$_lang['WEB_ERROR_XENSERVER_HOST_OFFLINE'],
        'XENSERVER_HOST_POWER_ON_MODE_DISABLED' => Xphp::$_lang['WEB_ERROR_XENSERVER_HOST_POWER_ON_MODE_DISABLED'],
        'XENSERVER_HOST_STILL_BOOTING' => Xphp::$_lang['WEB_ERROR_XENSERVER_HOST_STILL_BOOTING'],
        'XENSERVER_HOST_UNKNOWN_TO_MASTER' => Xphp::$_lang['WEB_ERROR_XENSERVER_HOST_UNKNOWN_TO_MASTER'],
        'XENSERVER_ILLEGAL_VBD_DEVICE' => Xphp::$_lang['WEB_ERROR_XENSERVER_ILLEGAL_VBD_DEVICE'],
        'XENSERVER_IMPORT_ERROR' => Xphp::$_lang['WEB_ERROR_XENSERVER_IMPORT_ERROR'],
        'XENSERVER_IMPORT_ERROR_ATTACHED_DISKS_NOT_FOUND' => Xphp::$_lang['WEB_ERROR_XENSERVER_IMPORT_ERROR_ATTACHED_DISKS_NOT_FOUND'],
        'XENSERVER_IMPORT_ERROR_CANNOT_HANDLE_CHUNKED' => Xphp::$_lang['WEB_ERROR_XENSERVER_IMPORT_ERROR_CANNOT_HANDLE_CHUNKED'],
        'XENSERVER_IMPORT_ERROR_FAILED_TO_FIND_OBJECT' => Xphp::$_lang['WEB_ERROR_XENSERVER_IMPORT_ERROR_FAILED_TO_FIND_OBJECT'],
        'XENSERVER_IMPORT_ERROR_PREMATURE_EOF' => Xphp::$_lang['WEB_ERROR_XENSERVER_IMPORT_ERROR_PREMATURE_EOF'],
        'XENSERVER_IMPORT_ERROR_SOME_CHECKSUMS_FAILED' => Xphp::$_lang['WEB_ERROR_XENSERVER_IMPORT_ERROR_SOME_CHECKSUMS_FAILED'],
        'XENSERVER_IMPORT_ERROR_UNEXPECTED_FILE' => Xphp::$_lang['WEB_ERROR_XENSERVER_IMPORT_ERROR_UNEXPECTED_FILE'],
        'XENSERVER_IMPORT_INCOMPATIBLE_VERSION' => Xphp::$_lang['WEB_ERROR_XENSERVER_IMPORT_INCOMPATIBLE_VERSION'],
        'XENSERVER_INCOMPATIBLE_PIF_PROPERTIES' => Xphp::$_lang['WEB_ERROR_XENSERVER_INCOMPATIBLE_PIF_PROPERTIES'],
        'XENSERVER_INTERFACE_HAS_NO_IP' => Xphp::$_lang['WEB_ERROR_XENSERVER_INTERFACE_HAS_NO_IP'],
        'XENSERVER_INTERNAL_ERROR' => Xphp::$_lang['WEB_ERROR_XENSERVER_INTERNAL_ERROR'],
        'XENSERVER_INVALID_DEVICE' => Xphp::$_lang['WEB_ERROR_XENSERVER_INVALID_DEVICE'],
        'XENSERVER_INVALID_EDITION' => Xphp::$_lang['WEB_ERROR_XENSERVER_INVALID_EDITION'],
        'XENSERVER_INVALID_FEATURE_STRING' => Xphp::$_lang['WEB_ERROR_XENSERVER_INVALID_FEATURE_STRING'],
        'XENSERVER_INVALID_IP_ADDRESS_SPECIFIED' => Xphp::$_lang['WEB_ERROR_XENSERVER_INVALID_IP_ADDRESS_SPECIFIED'],
        'XENSERVER_INVALID_PATCH' => Xphp::$_lang['WEB_ERROR_XENSERVER_INVALID_PATCH'],
        'XENSERVER_INVALID_PATCH_WITH_LOG' => Xphp::$_lang['WEB_ERROR_XENSERVER_INVALID_PATCH_WITH_LOG'],
        'XENSERVER_INVALID_VALUE' => Xphp::$_lang['WEB_ERROR_XENSERVER_INVALID_VALUE'],
        'XENSERVER_IS_TUNNEL_ACCESS_PIF' => Xphp::$_lang['WEB_ERROR_XENSERVER_IS_TUNNEL_ACCESS_PIF'],
        'XENSERVER_JOINING_HOST_CANNOT_BE_MASTER_OF_OTHER_HOSTS' => Xphp::$_lang['WEB_ERROR_XENSERVER_JOINING_HOST_CANNOT_BE_MASTER_OF_OTHER_HOSTS'],
        'XENSERVER_JOINING_HOST_CANNOT_CONTAIN_SHARED_SRS' => Xphp::$_lang['WEB_ERROR_XENSERVER_JOINING_HOST_CANNOT_CONTAIN_SHARED_SRS'],
        'XENSERVER_JOINING_HOST_CANNOT_HAVE_RUNNING_OR_SUSPENDED_VMS' => Xphp::$_lang['WEB_ERROR_XENSERVER_JOINING_HOST_CANNOT_HAVE_RUNNING_OR_SUSPENDED_VMS'],
        'XENSERVER_JOINING_HOST_CANNOT_HAVE_RUNNING_VMS' => Xphp::$_lang['WEB_ERROR_XENSERVER_JOINING_HOST_CANNOT_HAVE_RUNNING_VMS'],
        'XENSERVER_JOINING_HOST_CANNOT_HAVE_VMS_WITH_CURRENT_OPERATIONS' => Xphp::$_lang['WEB_ERROR_XENSERVER_JOINING_HOST_CANNOT_HAVE_VMS_WITH_CURRENT_OPERATIONS'],
        'XENSERVER_JOINING_HOST_CONNECTION_FAILED' => Xphp::$_lang['WEB_ERROR_XENSERVER_JOINING_HOST_CONNECTION_FAILED'],
        'XENSERVER_JOINING_HOST_SERVICE_FAILED' => Xphp::$_lang['WEB_ERROR_XENSERVER_JOINING_HOST_SERVICE_FAILED'],
        'XENSERVER_LICENCE_RESTRICTION' => Xphp::$_lang['WEB_ERROR_XENSERVER_LICENCE_RESTRICTION'],
        'XENSERVER_LICENSE_CANNOT_DOWNGRADE_WHILE_IN_POOL' => Xphp::$_lang['WEB_ERROR_XENSERVER_LICENSE_CANNOT_DOWNGRADE_WHILE_IN_POOL'],
        'XENSERVER_LICENSE_CHECKOUT_ERROR' => Xphp::$_lang['WEB_ERROR_XENSERVER_LICENSE_CHECKOUT_ERROR'],
        'XENSERVER_LICENSE_DOES_NOT_SUPPORT_POOLING' => Xphp::$_lang['WEB_ERROR_XENSERVER_LICENSE_DOES_NOT_SUPPORT_POOLING'],
        'XENSERVER_LICENSE_DOES_NOT_SUPPORT_XHA' => Xphp::$_lang['WEB_ERROR_XENSERVER_LICENSE_DOES_NOT_SUPPORT_XHA'],
        'XENSERVER_LICENSE_EXPIRED' => Xphp::$_lang['WEB_ERROR_XENSERVER_LICENSE_EXPIRED'],
        'XENSERVER_LICENSE_FILE_DEPRECATED' => Xphp::$_lang['WEB_ERROR_XENSERVER_LICENSE_FILE_DEPRECATED'],
        'XENSERVER_LICENSE_HOST_POOL_MISMATCH' => Xphp::$_lang['WEB_ERROR_XENSERVER_LICENSE_HOST_POOL_MISMATCH'],
        'XENSERVER_LICENSE_PROCESSING_ERROR' => Xphp::$_lang['WEB_ERROR_XENSERVER_LICENSE_PROCESSING_ERROR'],
        'XENSERVER_LOCATION_NOT_UNIQUE' => Xphp::$_lang['WEB_ERROR_XENSERVER_LOCATION_NOT_UNIQUE'],
        'XENSERVER_MAC_DOES_NOT_EXIST' => Xphp::$_lang['WEB_ERROR_XENSERVER_MAC_DOES_NOT_EXIST'],
        'XENSERVER_MAC_INVALID' => Xphp::$_lang['WEB_ERROR_XENSERVER_MAC_INVALID'],
        'XENSERVER_MAC_STILL_EXISTS' => Xphp::$_lang['WEB_ERROR_XENSERVER_MAC_STILL_EXISTS'],
        'XENSERVER_MAP_DUPLICATE_KEY' => Xphp::$_lang['WEB_ERROR_XENSERVER_MAP_DUPLICATE_KEY'],
        'XENSERVER_MESSAGE_DEPRECATED' => Xphp::$_lang['WEB_ERROR_XENSERVER_MESSAGE_DEPRECATED'],
        'XENSERVER_MESSAGE_METHOD_UNKNOWN' => Xphp::$_lang['WEB_ERROR_XENSERVER_MESSAGE_METHOD_UNKNOWN'],
        'XENSERVER_MESSAGE_PARAMETER_COUNT_MISMATCH' => Xphp::$_lang['WEB_ERROR_XENSERVER_MESSAGE_PARAMETER_COUNT_MISMATCH'],
        'XENSERVER_MESSAGE_REMOVED' => Xphp::$_lang['WEB_ERROR_XENSERVER_MESSAGE_REMOVED'],
        'XENSERVER_MIRROR_FAILED' => Xphp::$_lang['WEB_ERROR_XENSERVER_MIRROR_FAILED'],
        'XENSERVER_MISSING_CONNECTION_DETAILS' => Xphp::$_lang['WEB_ERROR_XENSERVER_MISSING_CONNECTION_DETAILS'],
        'XENSERVER_NETWORK_ALREADY_CONNECTED' => Xphp::$_lang['WEB_ERROR_XENSERVER_NETWORK_ALREADY_CONNECTED'],
        'XENSERVER_NETWORK_CONTAINS_PIF' => Xphp::$_lang['WEB_ERROR_XENSERVER_NETWORK_CONTAINS_PIF'],
        'XENSERVER_NETWORK_CONTAINS_VIF' => Xphp::$_lang['WEB_ERROR_XENSERVER_NETWORK_CONTAINS_VIF'],
        'XENSERVER_NOT_ALLOWED_ON_OEM_EDITION' => Xphp::$_lang['WEB_ERROR_XENSERVER_NOT_ALLOWED_ON_OEM_EDITION'],
        'XENSERVER_NOT_IMPLEMENTED' => Xphp::$_lang['WEB_ERROR_XENSERVER_NOT_IMPLEMENTED'],
        'XENSERVER_NOT_IN_EMERGENCY_MODE' => Xphp::$_lang['WEB_ERROR_XENSERVER_NOT_IN_EMERGENCY_MODE'],
        'XENSERVER_NOT_SUPPORTED_DURING_UPGRADE' => Xphp::$_lang['WEB_ERROR_XENSERVER_NOT_SUPPORTED_DURING_UPGRADE'],
        'XENSERVER_NOT_SYSTEM_DOMAIN' => Xphp::$_lang['WEB_ERROR_XENSERVER_NOT_SYSTEM_DOMAIN'],
        'XENSERVER_NO_HOSTS_AVAILABLE' => Xphp::$_lang['WEB_ERROR_XENSERVER_NO_HOSTS_AVAILABLE'],
        'XENSERVER_NO_MORE_REDO_LOGS_ALLOWED' => Xphp::$_lang['WEB_ERROR_XENSERVER_NO_MORE_REDO_LOGS_ALLOWED'],
        'XENSERVER_OBJECT_NOLONGER_EXISTS' => Xphp::$_lang['WEB_ERROR_XENSERVER_OBJECT_NOLONGER_EXISTS'],
        'XENSERVER_ONLY_ALLOWED_ON_OEM_EDITION' => Xphp::$_lang['WEB_ERROR_XENSERVER_ONLY_ALLOWED_ON_OEM_EDITION'],
        'XENSERVER_OPENVSWITCH_NOT_ACTIVE' => Xphp::$_lang['WEB_ERROR_XENSERVER_OPENVSWITCH_NOT_ACTIVE'],
        'XENSERVER_OPERATION_BLOCKED' => Xphp::$_lang['WEB_ERROR_XENSERVER_OPERATION_BLOCKED'],
        'XENSERVER_OPERATION_NOT_ALLOWED' => Xphp::$_lang['WEB_ERROR_XENSERVER_OPERATION_NOT_ALLOWED'],
        'XENSERVER_OPERATION_PARTIALLY_FAILED' => Xphp::$_lang['WEB_ERROR_XENSERVER_OPERATION_PARTIALLY_FAILED'],
        'XENSERVER_OTHER_OPERATION_IN_PROGRESS' => Xphp::$_lang['WEB_ERROR_XENSERVER_OTHER_OPERATION_IN_PROGRESS'],
        'XENSERVER_OUT_OF_SPACE' => Xphp::$_lang['WEB_ERROR_XENSERVER_OUT_OF_SPACE'],
        'XENSERVER_PATCH_ALREADY_APPLIED' => Xphp::$_lang['WEB_ERROR_XENSERVER_PATCH_ALREADY_APPLIED'],
        'XENSERVER_PATCH_ALREADY_EXISTS' => Xphp::$_lang['WEB_ERROR_XENSERVER_PATCH_ALREADY_EXISTS'],
        'XENSERVER_PATCH_APPLY_FAILED' => Xphp::$_lang['WEB_ERROR_XENSERVER_PATCH_APPLY_FAILED'],
        'XENSERVER_PATCH_IS_APPLIED' => Xphp::$_lang['WEB_ERROR_XENSERVER_PATCH_IS_APPLIED'],
        'XENSERVER_PATCH_PRECHECK_FAILED_ISO_MOUNTED' => Xphp::$_lang['WEB_ERROR_XENSERVER_PATCH_PRECHECK_FAILED_ISO_MOUNTED'],
        'XENSERVER_PATCH_PRECHECK_FAILED_PREREQUISITE_MISSING' => Xphp::$_lang['WEB_ERROR_XENSERVER_PATCH_PRECHECK_FAILED_PREREQUISITE_MISSING'],
        'XENSERVER_PATCH_PRECHECK_FAILED_UNKNOWN_ERROR' => Xphp::$_lang['WEB_ERROR_XENSERVER_PATCH_PRECHECK_FAILED_UNKNOWN_ERROR'],
        'XENSERVER_PATCH_PRECHECK_FAILED_VM_RUNNING' => Xphp::$_lang['WEB_ERROR_XENSERVER_PATCH_PRECHECK_FAILED_VM_RUNNING'],
        'XENSERVER_PATCH_PRECHECK_FAILED_WRONG_SERVER_BUILD' => Xphp::$_lang['WEB_ERROR_XENSERVER_PATCH_PRECHECK_FAILED_WRONG_SERVER_BUILD'],
        'XENSERVER_PATCH_PRECHECK_FAILED_WRONG_SERVER_VERSION' => Xphp::$_lang['WEB_ERROR_XENSERVER_PATCH_PRECHECK_FAILED_WRONG_SERVER_VERSION'],
        'XENSERVER_PBD_EXISTS' => Xphp::$_lang['WEB_ERROR_XENSERVER_PBD_EXISTS'],
        'XENSERVER_PERMISSION_DENIED' => Xphp::$_lang['WEB_ERROR_XENSERVER_PERMISSION_DENIED'],
        'XENSERVER_PGPU_INSUFFICIENT_CAPACITY_FOR_VGPU' => Xphp::$_lang['WEB_ERROR_XENSERVER_PGPU_INSUFFICIENT_CAPACITY_FOR_VGPU'],
        'XENSERVER_PGPU_IN_USE_BY_VM' => Xphp::$_lang['WEB_ERROR_XENSERVER_PGPU_IN_USE_BY_VM'],
        'XENSERVER_PGPU_NOT_COMPATIBLE_WITH_GPU_GROUP' => Xphp::$_lang['WEB_ERROR_XENSERVER_PGPU_NOT_COMPATIBLE_WITH_GPU_GROUP'],
        'XENSERVER_PIF_ALREADY_BONDED' => Xphp::$_lang['WEB_ERROR_XENSERVER_PIF_ALREADY_BONDED'],
        'XENSERVER_PIF_BOND_NEEDS_MORE_MEMBERS' => Xphp::$_lang['WEB_ERROR_XENSERVER_PIF_BOND_NEEDS_MORE_MEMBERS'],
        'XENSERVER_PIF_CANNOT_BOND_CROSS_HOST' => Xphp::$_lang['WEB_ERROR_XENSERVER_PIF_CANNOT_BOND_CROSS_HOST'],
        'XENSERVER_PIF_CONFIGURATION_ERROR' => Xphp::$_lang['WEB_ERROR_XENSERVER_PIF_CONFIGURATION_ERROR'],
        'XENSERVER_PIF_DEVICE_NOT_FOUND' => Xphp::$_lang['WEB_ERROR_XENSERVER_PIF_DEVICE_NOT_FOUND'],
        'XENSERVER_PIF_DOES_NOT_ALLOW_UNPLUG' => Xphp::$_lang['WEB_ERROR_XENSERVER_PIF_DOES_NOT_ALLOW_UNPLUG'],
        'XENSERVER_PIF_HAS_NO_NETWORK_CONFIGURATION' => Xphp::$_lang['WEB_ERROR_XENSERVER_PIF_HAS_NO_NETWORK_CONFIGURATION'],
        'XENSERVER_PIF_HAS_NO_V6_NETWORK_CONFIGURATION' => Xphp::$_lang['WEB_ERROR_XENSERVER_PIF_HAS_NO_V6_NETWORK_CONFIGURATION'],
        'XENSERVER_PIF_INCOMPATIBLE_PRIMARY_ADDRESS_TYPE' => Xphp::$_lang['WEB_ERROR_XENSERVER_PIF_INCOMPATIBLE_PRIMARY_ADDRESS_TYPE'],
        'XENSERVER_PIF_IS_MANAGEMENT_INTERFACE' => Xphp::$_lang['WEB_ERROR_XENSERVER_PIF_IS_MANAGEMENT_INTERFACE'],
        'XENSERVER_PIF_IS_PHYSICAL' => Xphp::$_lang['WEB_ERROR_XENSERVER_PIF_IS_PHYSICAL'],
        'XENSERVER_PIF_IS_VLAN' => Xphp::$_lang['WEB_ERROR_XENSERVER_PIF_IS_VLAN'],
        'XENSERVER_PIF_TUNNEL_STILL_EXISTS' => Xphp::$_lang['WEB_ERROR_XENSERVER_PIF_TUNNEL_STILL_EXISTS'],
        'XENSERVER_PIF_UNMANAGED' => Xphp::$_lang['WEB_ERROR_XENSERVER_PIF_UNMANAGED'],
        'XENSERVER_PIF_VLAN_EXISTS' => Xphp::$_lang['WEB_ERROR_XENSERVER_PIF_VLAN_EXISTS'],
        'XENSERVER_PIF_VLAN_STILL_EXISTS' => Xphp::$_lang['WEB_ERROR_XENSERVER_PIF_VLAN_STILL_EXISTS'],
        'XENSERVER_POOL_AUTH_ALREADY_ENABLED' => Xphp::$_lang['WEB_ERROR_XENSERVER_POOL_AUTH_ALREADY_ENABLED'],
        'XENSERVER_POOL_AUTH_DISABLE_FAILED' => Xphp::$_lang['WEB_ERROR_XENSERVER_POOL_AUTH_DISABLE_FAILED'],
        'XENSERVER_POOL_AUTH_DISABLE_FAILED_PERMISSION_DENIED' => Xphp::$_lang['WEB_ERROR_XENSERVER_POOL_AUTH_DISABLE_FAILED_PERMISSION_DENIED'],
        'XENSERVER_POOL_AUTH_DISABLE_FAILED_WRONG_CREDENTIALS' => Xphp::$_lang['WEB_ERROR_XENSERVER_POOL_AUTH_DISABLE_FAILED_WRONG_CREDENTIALS'],
        'XENSERVER_POOL_AUTH_ENABLE_FAILED' => Xphp::$_lang['WEB_ERROR_XENSERVER_POOL_AUTH_ENABLE_FAILED'],
        'XENSERVER_POOL_AUTH_ENABLE_FAILED_DOMAIN_LOOKUP_FAILED' => Xphp::$_lang['WEB_ERROR_XENSERVER_POOL_AUTH_ENABLE_FAILED_DOMAIN_LOOKUP_FAILED'],
        'XENSERVER_POOL_AUTH_ENABLE_FAILED_DUPLICATE_HOSTNAME' => Xphp::$_lang['WEB_ERROR_XENSERVER_POOL_AUTH_ENABLE_FAILED_DUPLICATE_HOSTNAME'],
        'XENSERVER_POOL_AUTH_ENABLE_FAILED_INVALID_ID' => Xphp::$_lang['WEB_ERROR_XENSERVER_POOL_AUTH_ENABLE_FAILED_INVALID_ID'],
        'XENSERVER_POOL_AUTH_ENABLE_FAILED_PERMISSION_DENIED' => Xphp::$_lang['WEB_ERROR_XENSERVER_POOL_AUTH_ENABLE_FAILED_PERMISSION_DENIED'],
        'XENSERVER_POOL_AUTH_ENABLE_FAILED_WRONG_CREDENTIALS' => Xphp::$_lang['WEB_ERROR_XENSERVER_POOL_AUTH_ENABLE_FAILED_WRONG_CREDENTIALS'],
        'XENSERVER_POOL_JOINING_EXTERNAL_AUTH_MISMATCH' => Xphp::$_lang['WEB_ERROR_XENSERVER_POOL_JOINING_EXTERNAL_AUTH_MISMATCH'],
        'XENSERVER_POOL_JOINING_HOST_MUST_HAVE_PHYSICAL_MANAGEMENT_NIC' => Xphp::$_lang['WEB_ERROR_XENSERVER_POOL_JOINING_HOST_MUST_HAVE_PHYSICAL_MANAGEMENT_NIC'],
        'XENSERVER_POOL_JOINING_HOST_MUST_HAVE_SAME_PRODUCT_VERSION' => Xphp::$_lang['WEB_ERROR_XENSERVER_POOL_JOINING_HOST_MUST_HAVE_SAME_PRODUCT_VERSION'],
        'XENSERVER_PROVISION_FAILED_OUT_OF_SPACE' => Xphp::$_lang['WEB_ERROR_XENSERVER_PROVISION_FAILED_OUT_OF_SPACE'],
        'XENSERVER_PROVISION_ONLY_ALLOWED_ON_TEMPLATE' => Xphp::$_lang['WEB_ERROR_XENSERVER_PROVISION_ONLY_ALLOWED_ON_TEMPLATE'],
        'XENSERVER_RBAC_PERMISSION_DENIED' => Xphp::$_lang['WEB_ERROR_XENSERVER_RBAC_PERMISSION_DENIED'],
        'XENSERVER_REDO_LOG_IS_ENABLED' => Xphp::$_lang['WEB_ERROR_XENSERVER_REDO_LOG_IS_ENABLED'],
        'XENSERVER_RESTORE_INCOMPATIBLE_VERSION' => Xphp::$_lang['WEB_ERROR_XENSERVER_RESTORE_INCOMPATIBLE_VERSION'],
        'XENSERVER_RESTORE_SCRIPT_FAILED' => Xphp::$_lang['WEB_ERROR_XENSERVER_RESTORE_SCRIPT_FAILED'],
        'XENSERVER_RESTORE_TARGET_MGMT_IF_NOT_IN_BACKUP' => Xphp::$_lang['WEB_ERROR_XENSERVER_RESTORE_TARGET_MGMT_IF_NOT_IN_BACKUP'],
        'XENSERVER_RESTORE_TARGET_MISSING_DEVICE' => Xphp::$_lang['WEB_ERROR_XENSERVER_RESTORE_TARGET_MISSING_DEVICE'],
        'XENSERVER_ROLE_ALREADY_EXISTS' => Xphp::$_lang['WEB_ERROR_XENSERVER_ROLE_ALREADY_EXISTS'],
        'XENSERVER_ROLE_NOT_FOUND' => Xphp::$_lang['WEB_ERROR_XENSERVER_ROLE_NOT_FOUND'],
        'XENSERVER_SESSION_INVALID' => Xphp::$_lang['WEB_ERROR_XENSERVER_SESSION_INVALID'],
        'XENSERVER_SESSION_NOT_REGISTERED' => Xphp::$_lang['WEB_ERROR_XENSERVER_SESSION_NOT_REGISTERED'],
        'XENSERVER_SLAVE_REQUIRES_MANAGEMENT_INTERFACE' => Xphp::$_lang['WEB_ERROR_XENSERVER_SLAVE_REQUIRES_MANAGEMENT_INTERFACE'],
        'XENSERVER_SM_PLUGIN_COMMUNICATION_FAILURE' => Xphp::$_lang['WEB_ERROR_XENSERVER_SM_PLUGIN_COMMUNICATION_FAILURE'],
        'XENSERVER_SR_ATTACH_FAILED' => Xphp::$_lang['WEB_ERROR_XENSERVER_SR_ATTACH_FAILED'],
        'XENSERVER_SR_BACKEND_FAILURE' => Xphp::$_lang['WEB_ERROR_XENSERVER_SR_BACKEND_FAILURE'],
        'XENSERVER_SR_DEVICE_IN_USE' => Xphp::$_lang['WEB_ERROR_XENSERVER_SR_DEVICE_IN_USE'],
        'XENSERVER_SR_FULL' => Xphp::$_lang['WEB_ERROR_XENSERVER_SR_FULL'],
        'XENSERVER_SR_HAS_MULTIPLE_PBDS' => Xphp::$_lang['WEB_ERROR_XENSERVER_SR_HAS_MULTIPLE_PBDS'],
        'XENSERVER_SR_HAS_NO_PBDS' => Xphp::$_lang['WEB_ERROR_XENSERVER_SR_HAS_NO_PBDS'],
        'XENSERVER_SR_HAS_PBD' => Xphp::$_lang['WEB_ERROR_XENSERVER_SR_HAS_PBD'],
        'XENSERVER_SR_INDESTRUCTIBLE' => Xphp::$_lang['WEB_ERROR_XENSERVER_SR_INDESTRUCTIBLE'],
        'XENSERVER_SR_IS_CACHE_SR' => Xphp::$_lang['WEB_ERROR_XENSERVER_SR_IS_CACHE_SR'],
        'XENSERVER_SR_NOT_ATTACHED' => Xphp::$_lang['WEB_ERROR_XENSERVER_SR_NOT_ATTACHED'],
        'XENSERVER_SR_NOT_EMPTY' => Xphp::$_lang['WEB_ERROR_XENSERVER_SR_NOT_EMPTY'],
        'XENSERVER_SR_NOT_SHARABLE' => Xphp::$_lang['WEB_ERROR_XENSERVER_SR_NOT_SHARABLE'],
        'XENSERVER_SR_OPERATION_NOT_SUPPORTED' => Xphp::$_lang['WEB_ERROR_XENSERVER_SR_OPERATION_NOT_SUPPORTED'],
        'XENSERVER_SR_REQUIRES_UPGRADE' => Xphp::$_lang['WEB_ERROR_XENSERVER_SR_REQUIRES_UPGRADE'],
        'XENSERVER_SR_UNKNOWN_DRIVER' => Xphp::$_lang['WEB_ERROR_XENSERVER_SR_UNKNOWN_DRIVER'],
        'XENSERVER_SR_UUID_EXISTS' => Xphp::$_lang['WEB_ERROR_XENSERVER_SR_UUID_EXISTS'],
        'XENSERVER_SR_VDI_LOCKING_FAILED' => Xphp::$_lang['WEB_ERROR_XENSERVER_SR_VDI_LOCKING_FAILED'],
        'XENSERVER_SSL_VERIFY_ERROR' => Xphp::$_lang['WEB_ERROR_XENSERVER_SSL_VERIFY_ERROR'],
        'XENSERVER_SUBJECT_ALREADY_EXISTS' => Xphp::$_lang['WEB_ERROR_XENSERVER_SUBJECT_ALREADY_EXISTS'],
        'XENSERVER_SUBJECT_CANNOT_BE_RESOLVED' => Xphp::$_lang['WEB_ERROR_XENSERVER_SUBJECT_CANNOT_BE_RESOLVED'],
        'XENSERVER_SYSTEM_STATUS_MUST_USE_TAR_ON_OEM' => Xphp::$_lang['WEB_ERROR_XENSERVER_SYSTEM_STATUS_MUST_USE_TAR_ON_OEM'],
        'XENSERVER_SYSTEM_STATUS_RETRIEVAL_FAILED' => Xphp::$_lang['WEB_ERROR_XENSERVER_SYSTEM_STATUS_RETRIEVAL_FAILED'],
        'XENSERVER_TASK_CANCELLED' => Xphp::$_lang['WEB_ERROR_XENSERVER_TASK_CANCELLED'],
        'XENSERVER_TOO_BUSY' => Xphp::$_lang['WEB_ERROR_XENSERVER_TOO_BUSY'],
        'XENSERVER_TOO_MANY_PENDING_TASKS' => Xphp::$_lang['WEB_ERROR_XENSERVER_TOO_MANY_PENDING_TASKS'],
        'XENSERVER_TOO_MANY_STORAGE_MIGRATES' => Xphp::$_lang['WEB_ERROR_XENSERVER_TOO_MANY_STORAGE_MIGRATES'],
        'XENSERVER_TRANSPORT_PIF_NOT_CONFIGURED' => Xphp::$_lang['WEB_ERROR_XENSERVER_TRANSPORT_PIF_NOT_CONFIGURED'],
        'XENSERVER_UNKNOWN_BOOTLOADER' => Xphp::$_lang['WEB_ERROR_XENSERVER_UNKNOWN_BOOTLOADER'],
        'XENSERVER_USER_IS_NOT_LOCAL_SUPERUSER' => Xphp::$_lang['WEB_ERROR_XENSERVER_USER_IS_NOT_LOCAL_SUPERUSER'],
        'XENSERVER_V6D_FAILURE' => Xphp::$_lang['WEB_ERROR_XENSERVER_V6D_FAILURE'],
        'XENSERVER_VALUE_NOT_SUPPORTED' => Xphp::$_lang['WEB_ERROR_XENSERVER_VALUE_NOT_SUPPORTED'],
        'XENSERVER_VBD_CDS_MUST_BE_READONLY' => Xphp::$_lang['WEB_ERROR_XENSERVER_VBD_CDS_MUST_BE_READONLY'],
        'XENSERVER_VBD_IS_EMPTY' => Xphp::$_lang['WEB_ERROR_XENSERVER_VBD_IS_EMPTY'],
        'XENSERVER_VBD_NOT_EMPTY' => Xphp::$_lang['WEB_ERROR_XENSERVER_VBD_NOT_EMPTY'],
        'XENSERVER_VBD_NOT_REMOVABLE_MEDIA' => Xphp::$_lang['WEB_ERROR_XENSERVER_VBD_NOT_REMOVABLE_MEDIA'],
        'XENSERVER_VBD_NOT_UNPLUGGABLE' => Xphp::$_lang['WEB_ERROR_XENSERVER_VBD_NOT_UNPLUGGABLE'],
        'XENSERVER_VBD_TRAY_LOCKED' => Xphp::$_lang['WEB_ERROR_XENSERVER_VBD_TRAY_LOCKED'],
        'XENSERVER_VDI_CONTAINS_METADATA_OF_THIS_POOL' => Xphp::$_lang['WEB_ERROR_XENSERVER_VDI_CONTAINS_METADATA_OF_THIS_POOL'],
        'XENSERVER_VDI_INCOMPATIBLE_TYPE' => Xphp::$_lang['WEB_ERROR_XENSERVER_VDI_INCOMPATIBLE_TYPE'],
        'XENSERVER_VDI_IN_USE' => Xphp::$_lang['WEB_ERROR_XENSERVER_VDI_IN_USE'],
        'XENSERVER_VDI_IS_A_PHYSICAL_DEVICE' => Xphp::$_lang['WEB_ERROR_XENSERVER_VDI_IS_A_PHYSICAL_DEVICE'],
        'XENSERVER_VDI_IS_NOT_ISO' => Xphp::$_lang['WEB_ERROR_XENSERVER_VDI_IS_NOT_ISO'],
        'XENSERVER_VDI_LOCATION_MISSING' => Xphp::$_lang['WEB_ERROR_XENSERVER_VDI_LOCATION_MISSING'],
        'XENSERVER_VDI_MISSING' => Xphp::$_lang['WEB_ERROR_XENSERVER_VDI_MISSING'],
        'XENSERVER_VDI_NEEDS_VM_FOR_MIGRATE' => Xphp::$_lang['WEB_ERROR_XENSERVER_VDI_NEEDS_VM_FOR_MIGRATE'],
        'XENSERVER_VDI_NOT_AVAILABLE' => Xphp::$_lang['WEB_ERROR_XENSERVER_VDI_NOT_AVAILABLE'],
        'XENSERVER_VDI_NOT_IN_MAP' => Xphp::$_lang['WEB_ERROR_XENSERVER_VDI_NOT_IN_MAP'],
        'XENSERVER_VDI_NOT_MANAGED' => Xphp::$_lang['WEB_ERROR_XENSERVER_VDI_NOT_MANAGED'],
        'XENSERVER_VDI_NOT_SPARSE' => Xphp::$_lang['WEB_ERROR_XENSERVER_VDI_NOT_SPARSE'],
        'XENSERVER_VDI_READONLY' => Xphp::$_lang['WEB_ERROR_XENSERVER_VDI_READONLY'],
        'XENSERVER_VDI_TOO_SMALL' => Xphp::$_lang['WEB_ERROR_XENSERVER_VDI_TOO_SMALL'],
        'XENSERVER_VGPU_TYPE_NOT_COMPATIBLE_WITH_RUNNING_TYPE' => Xphp::$_lang['WEB_ERROR_XENSERVER_VGPU_TYPE_NOT_COMPATIBLE_WITH_RUNNING_TYPE'],
        'XENSERVER_VGPU_TYPE_NOT_ENABLED' => Xphp::$_lang['WEB_ERROR_XENSERVER_VGPU_TYPE_NOT_ENABLED'],
        'XENSERVER_VGPU_TYPE_NOT_SUPPORTED' => Xphp::$_lang['WEB_ERROR_XENSERVER_VGPU_TYPE_NOT_SUPPORTED'],
        'XENSERVER_VIF_IN_USE' => Xphp::$_lang['WEB_ERROR_XENSERVER_VIF_IN_USE'],
        'XENSERVER_VLAN_TAG_INVALID' => Xphp::$_lang['WEB_ERROR_XENSERVER_VLAN_TAG_INVALID'],
        'XENSERVER_VMPP_ARCHIVE_MORE_FREQUENT_THAN_BACKUP' => Xphp::$_lang['WEB_ERROR_XENSERVER_VMPP_ARCHIVE_MORE_FREQUENT_THAN_BACKUP'],
        'XENSERVER_VMPP_HAS_VM' => Xphp::$_lang['WEB_ERROR_XENSERVER_VMPP_HAS_VM'],
        'XENSERVER_VMS_FAILED_TO_COOPERATE' => Xphp::$_lang['WEB_ERROR_XENSERVER_VMS_FAILED_TO_COOPERATE'],
        'XENSERVER_VM_ASSIGNED_TO_PROTECTION_POLICY' => Xphp::$_lang['WEB_ERROR_XENSERVER_VM_ASSIGNED_TO_PROTECTION_POLICY'],
        'XENSERVER_VM_ATTACHED_TO_MORE_THAN_ONE_VDI_WITH_TIMEOFFSET_MARKED_AS_RESET_ON_BOOT' => Xphp::$_lang['WEB_ERROR_XENSERVER_VM_ATTACHED_TO_MORE_THAN_ONE_VDI_WITH_TIMEOFFSET_MARKED_AS_RESET_ON_BOOT'],
        'XENSERVER_VM_BAD_POWER_STATE' => Xphp::$_lang['WEB_ERROR_XENSERVER_VM_BAD_POWER_STATE'],
        'XENSERVER_VM_BIOS_STRINGS_ALREADY_SET' => Xphp::$_lang['WEB_ERROR_XENSERVER_VM_BIOS_STRINGS_ALREADY_SET'],
        'XENSERVER_VM_CANNOT_DELETE_DEFAULT_TEMPLATE' => Xphp::$_lang['WEB_ERROR_XENSERVER_VM_CANNOT_DELETE_DEFAULT_TEMPLATE'],
        'XENSERVER_VM_CHECKPOINT_RESUME_FAILED' => Xphp::$_lang['WEB_ERROR_XENSERVER_VM_CHECKPOINT_RESUME_FAILED'],
        'XENSERVER_VM_CHECKPOINT_SUSPEND_FAILED' => Xphp::$_lang['WEB_ERROR_XENSERVER_VM_CHECKPOINT_SUSPEND_FAILED'],
        'XENSERVER_VM_CRASHED' => Xphp::$_lang['WEB_ERROR_XENSERVER_VM_CRASHED'],
        'XENSERVER_VM_DUPLICATE_VBD_DEVICE' => Xphp::$_lang['WEB_ERROR_XENSERVER_VM_DUPLICATE_VBD_DEVICE'],
        'XENSERVER_VM_FAILED_SHUTDOWN_ACKNOWLEDGMENT' => Xphp::$_lang['WEB_ERROR_XENSERVER_VM_FAILED_SHUTDOWN_ACKNOWLEDGMENT'],
        'XENSERVER_VM_HALTED' => Xphp::$_lang['WEB_ERROR_XENSERVER_VM_HALTED'],
        'XENSERVER_VM_HAS_CHECKPOINT' => Xphp::$_lang['WEB_ERROR_XENSERVER_VM_HAS_CHECKPOINT'],
        'XENSERVER_VM_HAS_PCI_ATTACHED' => Xphp::$_lang['WEB_ERROR_XENSERVER_VM_HAS_PCI_ATTACHED'],
        'XENSERVER_VM_HAS_TOO_MANY_SNAPSHOTS' => Xphp::$_lang['WEB_ERROR_XENSERVER_VM_HAS_TOO_MANY_SNAPSHOTS'],
        'XENSERVER_VM_HAS_VGPU' => Xphp::$_lang['WEB_ERROR_XENSERVER_VM_HAS_VGPU'],
        'XENSERVER_VM_HOST_INCOMPATIBLE_VERSION' => Xphp::$_lang['WEB_ERROR_XENSERVER_VM_HOST_INCOMPATIBLE_VERSION'],
        'XENSERVER_VM_HVM_REQUIRED' => Xphp::$_lang['WEB_ERROR_XENSERVER_VM_HVM_REQUIRED'],
        'XENSERVER_VM_INCOMPATIBLE_WITH_THIS_HOST' => Xphp::$_lang['WEB_ERROR_XENSERVER_VM_INCOMPATIBLE_WITH_THIS_HOST'],
        'XENSERVER_VM_IS_PART_OF_AN_APPLIANCE' => Xphp::$_lang['WEB_ERROR_XENSERVER_VM_IS_PART_OF_AN_APPLIANCE'],
        'XENSERVER_VM_IS_PROTECTED' => Xphp::$_lang['WEB_ERROR_XENSERVER_VM_IS_PROTECTED'],
        'XENSERVER_VM_IS_TEMPLATE' => Xphp::$_lang['WEB_ERROR_XENSERVER_VM_IS_TEMPLATE'],
        'XENSERVER_VM_LACKS_FEATURE_SHUTDOWN' => Xphp::$_lang['WEB_ERROR_XENSERVER_VM_LACKS_FEATURE_SHUTDOWN'],
        'XENSERVER_VM_LACKS_FEATURE_SUSPEND' => Xphp::$_lang['WEB_ERROR_XENSERVER_VM_LACKS_FEATURE_SUSPEND'],
        'XENSERVER_VM_MEMORY_SIZE_TOO_LOW' => Xphp::$_lang['WEB_ERROR_XENSERVER_VM_MEMORY_SIZE_TOO_LOW'],
        'XENSERVERVM_MIGRATE_FAILED' => Xphp::$_lang['WEB_ERROR_XENSERVERVM_MIGRATE_FAILED'],
        'XENSERVER_VM_MISSING_PV_DRIVERS' => Xphp::$_lang['WEB_ERROR_XENSERVER_VM_MISSING_PV_DRIVERS'],
        'XENSERVER_VM_NOT_RESIDENT_HERE' => Xphp::$_lang['WEB_ERROR_XENSERVER_VM_NOT_RESIDENT_HERE'],
        'XENSERVER_VM_NO_CRASHDUMP_SR' => Xphp::$_lang['WEB_ERROR_XENSERVER_VM_NO_CRASHDUMP_SR'],
        'XENSERVER_VM_NO_SUSPEND_SR' => Xphp::$_lang['WEB_ERROR_XENSERVER_VM_NO_SUSPEND_SR'],
        'XENSERVER_VM_NO_VCPUS' => Xphp::$_lang['WEB_ERROR_XENSERVER_VM_NO_VCPUS'],
        'XENSERVER_VM_OLD_PV_DRIVERS' => Xphp::$_lang['WEB_ERROR_XENSERVER_VM_OLD_PV_DRIVERS'],
        'XENSERVER_VM_REBOOTED' => Xphp::$_lang['WEB_ERROR_XENSERVER_VM_REBOOTED'],
        'XENSERVER_VM_REQUIRES_GPU' => Xphp::$_lang['WEB_ERROR_XENSERVER_VM_REQUIRES_GPU'],
        'XENSERVER_VM_REQUIRES_IOMMU' => Xphp::$_lang['WEB_ERROR_XENSERVER_VM_REQUIRES_IOMMU'],
        'XENSERVER_VM_REQUIRES_NETWORK' => Xphp::$_lang['WEB_ERROR_XENSERVER_VM_REQUIRES_NETWORK'],
        'XENSERVER_VM_REQUIRES_SR' => Xphp::$_lang['WEB_ERROR_XENSERVER_VM_REQUIRES_SR'],
        'XENSERVER_VM_REQUIRES_VDI' => Xphp::$_lang['WEB_ERROR_XENSERVER_VM_REQUIRES_VDI'],
        'XENSERVER_VM_REQUIRES_VGPU' => Xphp::$_lang['WEB_ERROR_XENSERVER_VM_REQUIRES_VGPU'],
        'XENSERVER_VM_REVERT_FAILED' => Xphp::$_lang['WEB_ERROR_XENSERVER_VM_REVERT_FAILED'],
        'XENSERVER_VM_SHUTDOWN_TIMEOUT' => Xphp::$_lang['WEB_ERROR_XENSERVER_VM_SHUTDOWN_TIMEOUT'],
        'XENSERVER_VM_SNAPSHOT_WITH_QUIESCE_FAILED' => Xphp::$_lang['WEB_ERROR_XENSERVER_VM_SNAPSHOT_WITH_QUIESCE_FAILED'],
        'XENSERVER_VM_SNAPSHOT_WITH_QUIESCE_NOT_SUPPORTED' => Xphp::$_lang['WEB_ERROR_XENSERVER_VM_SNAPSHOT_WITH_QUIESCE_NOT_SUPPORTED'],
        'XENSERVER_VM_SNAPSHOT_WITH_QUIESCE_PLUGIN_DEOS_NOT_RESPOND' => Xphp::$_lang['WEB_ERROR_XENSERVER_VM_SNAPSHOT_WITH_QUIESCE_PLUGIN_DEOS_NOT_RESPOND'],
        'XENSERVER_VM_SNAPSHOT_WITH_QUIESCE_TIMEOUT' => Xphp::$_lang['WEB_ERROR_XENSERVER_VM_SNAPSHOT_WITH_QUIESCE_TIMEOUT'],
        'XENSERVER_VM_TOO_MANY_VCPUS' => Xphp::$_lang['WEB_ERROR_XENSERVER_VM_TOO_MANY_VCPUS'],
        'XENSERVER_VM_TO_IMPORT_IS_NOT_NEWER_VERSION' => Xphp::$_lang['WEB_ERROR_XENSERVER_VM_TO_IMPORT_IS_NOT_NEWER_VERSION'],
        'XENSERVER_VM_UNSAFE_BOOT' => Xphp::$_lang['WEB_ERROR_XENSERVER_VM_UNSAFE_BOOT'],
        'XENSERVER_WLB_AUTHENTICATION_FAILED' => Xphp::$_lang['WEB_ERROR_XENSERVER_WLB_AUTHENTICATION_FAILED'],
        'XENSERVER_WLB_CONNECTION_REFUSED' => Xphp::$_lang['WEB_ERROR_XENSERVER_WLB_CONNECTION_REFUSED'],
        'XENSERVER_WLB_CONNECTION_RESET' => Xphp::$_lang['WEB_ERROR_XENSERVER_WLB_CONNECTION_RESET'],
        'XENSERVER_WLB_DISABLED' => Xphp::$_lang['WEB_ERROR_XENSERVER_WLB_DISABLED'],
        'XENSERVER_WLB_INTERNAL_ERROR' => Xphp::$_lang['WEB_ERROR_XENSERVER_WLB_INTERNAL_ERROR'],
        'XENSERVER_WLB_MALFORMED_REQUEST' => Xphp::$_lang['WEB_ERROR_XENSERVER_WLB_MALFORMED_REQUEST'],
        'XENSERVER_WLB_MALFORMED_RESPONSE' => Xphp::$_lang['WEB_ERROR_XENSERVER_WLB_MALFORMED_RESPONSE'],
        'XENSERVER_WLB_NOT_INITIALIZED' => Xphp::$_lang['WEB_ERROR_XENSERVER_WLB_NOT_INITIALIZED'],
        'XENSERVER_WLB_TIMEOUT' => Xphp::$_lang['WEB_ERROR_XENSERVER_WLB_TIMEOUT'],
        'XENSERVER_WLB_UNKNOWN_HOST' => Xphp::$_lang['WEB_ERROR_XENSERVER_WLB_UNKNOWN_HOST'],
        'XENSERVER_WLB_URL_INVALID' => Xphp::$_lang['WEB_ERROR_XENSERVER_WLB_URL_INVALID'],
        'XENSERVER_WLB_XENSERVER_AUTHENTICATION_FAILED' => Xphp::$_lang['WEB_ERROR_XENSERVER_WLB_XENSERVER_AUTHENTICATION_FAILED'],
        'XENSERVER_WLB_XENSERVER_CONNECTION_REFUSED' => Xphp::$_lang['WEB_ERROR_XENSERVER_WLB_XENSERVER_CONNECTION_REFUSED'],
        'XENSERVER_WLB_XENSERVER_MALFORMED_RESPONSE' => Xphp::$_lang['WEB_ERROR_XENSERVER_WLB_XENSERVER_MALFORMED_RESPONSE'],
        'XENSERVER_WLB_XENSERVER_TIMEOUT' => Xphp::$_lang['WEB_ERROR_XENSERVER_WLB_XENSERVER_TIMEOUT'],
        'XENSERVER_WLB_XENSERVER_UNKNOWN_HOST' => Xphp::$_lang['WEB_ERROR_XENSERVER_WLB_XENSERVER_UNKNOWN_HOST'],
        'XENSERVER_XAPI_HOOK_FAILED' => Xphp::$_lang['WEB_ERROR_XENSERVER_XAPI_HOOK_FAILED'],
        'XENSERVER_XENAPI_MISSING_PLUGIN' => Xphp::$_lang['WEB_ERROR_XENSERVER_XENAPI_MISSING_PLUGIN'],
        'XENSERVER_XENAPI_PLUGIN_FAILURE' => Xphp::$_lang['WEB_ERROR_XENSERVER_XENAPI_PLUGIN_FAILURE'],
        'XENSERVER_XEN_VSS_REQ_ERROR_ADDING_VOLUME_TO_SNAPSET_FAILED' => Xphp::$_lang['WEB_ERROR_XENSERVER_XEN_VSS_REQ_ERROR_ADDING_VOLUME_TO_SNAPSET_FAILED'],
        'XENSERVER_XEN_VSS_REQ_ERROR_CREATING_SNAPSHOT' => Xphp::$_lang['WEB_ERROR_XENSERVER_XEN_VSS_REQ_ERROR_CREATING_SNAPSHOT'],
        'XENSERVER_XEN_VSS_REQ_ERROR_CREATING_SNAPSHOT_XML_STRING' => Xphp::$_lang['WEB_ERROR_XENSERVER_XEN_VSS_REQ_ERROR_CREATING_SNAPSHOT_XML_STRING'],
        'XENSERVER_XEN_VSS_REQ_ERROR_INIT_FAILED' => Xphp::$_lang['WEB_ERROR_XENSERVER_XEN_VSS_REQ_ERROR_INIT_FAILED'],
        'XENSERVER_XEN_VSS_REQ_ERROR_NO_VOLUMES_SUPPORTED' => Xphp::$_lang['WEB_ERROR_XENSERVER_XEN_VSS_REQ_ERROR_NO_VOLUMES_SUPPORTED'],
        'XENSERVER_XEN_VSS_REQ_ERROR_PREPARING_WRITERS' => Xphp::$_lang['WEB_ERROR_XENSERVER_XEN_VSS_REQ_ERROR_PREPARING_WRITERS'],
        'XENSERVER_XEN_VSS_REQ_ERROR_PROV_NOT_LOADED' => Xphp::$_lang['WEB_ERROR_XENSERVER_XEN_VSS_REQ_ERROR_PROV_NOT_LOADED'],
        'XENSERVER_XEN_VSS_REQ_ERROR_START_SNAPSHOT_SET_FAILED' => Xphp::$_lang['WEB_ERROR_XENSERVER_XEN_VSS_REQ_ERROR_START_SNAPSHOT_SET_FAILED'],
        'XENSERVER_XMLRPC_UNMARSHAL_FAILURE' => Xphp::$_lang['WEB_ERROR_XENSERVER_XMLRPC_UNMARSHAL_FAILURE'],

        'VM_DISK_ALREADY_OPEN_ERROR' => Xphp::$_lang['WEB_ERROR_VM_DISK_ALREADY_OPEN_ERROR'],
        'VM_DISK_NOT_OPEN_ERROR' => Xphp::$_lang['WEB_ERROR_VM_DISK_NOT_OPEN_ERROR'],
        'VM_SNAPSHOT_NOT_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_VM_SNAPSHOT_NOT_EXIST_ERROR'],
        'VM_QCOW_SNAPSHOT_ID_TOO_LONG_ERROR' => Xphp::$_lang['WEB_ERROR_VM_QCOW_SNAPSHOT_ID_TOO_LONG_ERROR'],
        'VM_DISK_OP_NOT_SUPPORT_ERROR' => Xphp::$_lang['WEB_ERROR_VM_DISK_OP_NOT_SUPPORT_ERROR'],
        'VM_DISK_CLUSTER_TYPE_UNEXCEPTED_ERROR' => Xphp::$_lang['WEB_ERROR_VM_DISK_CLUSTER_TYPE_UNEXCEPTED_ERROR'],
        'VM_DISK_OPEN_MODE_ERROR' => Xphp::$_lang['WEB_ERROR_VM_DISK_OPEN_MODE_ERROR'],
        'VM_DISK_DRIVER_NOT_SUPPORT_META_OPERATION' => Xphp::$_lang['WEB_ERROR_VM_DISK_DRIVER_NOT_SUPPORT_META_OPERATION'],

        'VM_OVIRT_HTTP_POST_ERROR' => Xphp::$_lang['WEB_ERROR_VM_OVIRT_HTTP_POST_ERROR'],
        'VM_CREATE_SNAPSHOT_TIMEOUT' => Xphp::$_lang['WEB_ERROR_VM_CREATE_SNAPSHOT_TIMEOUT'],
        'VM_OVIRT_OPERATION_TIME_OUT_ERROR' => Xphp::$_lang['WEB_ERROR_VM_OVIRT_OPERATION_TIME_OUT_ERROR'],
        'VM_HYPERVISOR_TYPE_NOT_COMPATIBLE_ERROR' => Xphp::$_lang['WEB_ERROR_VM_HYPERVISOR_TYPE_NOT_COMPATIBLE_ERROR'],

        'KVM_SANGFOR_FIND_PUBKEY_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_SANGFOR_FIND_PUBKEY_ERROR'],
        'KVM_SANGFOR_RSA_GET_PUBLICKEY_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_SANGFOR_RSA_GET_PUBLICKEY_ERROR'],
        'KVM_SANGFOR_INIT_ENCRYPT_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_SANGFOR_INIT_ENCRYPT_ERROR'],
        'KVM_SANGFOR_BIO_PUBKEY_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_SANGFOR_BIO_PUBKEY_ERROR'],
        'KVM_SANGFOR_ENCRYPT_PASSWORD_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_SANGFOR_ENCRYPT_PASSWORD_ERROR'],
        'KVM_SANGFOR_LOGIN_READ_CSRFPTOKEN_EORROR' => Xphp::$_lang['WEB_ERROR_KVM_SANGFOR_LOGIN_READ_CSRFPTOKEN_EORROR'],
        'KVM_SANGFOR_LOGIN_MODIFY_HEADER_EORROR' => Xphp::$_lang['WEB_ERROR_KVM_SANGFOR_LOGIN_MODIFY_HEADER_EORROR'],
        'KVM_SANGFOR_LOGIN_GET_VMJSONLIST_EORROR' => Xphp::$_lang['WEB_ERROR_KVM_SANGFOR_LOGIN_GET_VMJSONLIST_EORROR'],
        'KVM_SANGFOR_LOGIN_GET_HOSTJSONLIST_EORROR' => Xphp::$_lang['WEB_ERROR_KVM_SANGFOR_LOGIN_GET_HOSTJSONLIST_EORROR'],
        'KVM_SANGFOR_VM_POWEROFFBYUUID_EORROR' => Xphp::$_lang['WEB_ERROR_KVM_SANGFOR_VM_POWEROFFBYUUID_EORROR'],
        'KVM_SANGFOR_VM_POWERONBYUUID_EORROR' => Xphp::$_lang['WEB_ERROR_KVM_SANGFOR_VM_POWERONBYUUID_EORROR'],
        'KVM_SANGFOR_STRING_TO_JSON_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_SANGFOR_STRING_TO_JSON_ERROR'],
        'KVM_SANGFOR_PHYSICALNETWORLK_GETLIST_EORROR' => Xphp::$_lang['WEB_ERROR_KVM_SANGFOR_PHYSICALNETWORLK_GETLIST_EORROR'],
        'KVM_SANGFOR_PHYSICALNETWORLK_GETBYUUID_EORROR' => Xphp::$_lang['WEB_ERROR_KVM_SANGFOR_PHYSICALNETWORLK_GETBYUUID_EORROR'],
        'KVM_SANGFOR_HOST_GETINFO_BYUUID_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_SANGFOR_HOST_GETINFO_BYUUID_ERROR'],
        'KVM_SANGFOR_VM_GETINFO_BYUUID_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_SANGFOR_VM_GETINFO_BYUUID_ERROR'],
        'KVM_SANGFOR_VM_DOBACKUP_EORROR' => Xphp::$_lang['WEB_ERROR_KVM_SANGFOR_VM_DOBACKUP_EORROR'],
        'KVM_SANGFOR_STORAGEPOOL_GETINFOBYUUID_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_SANGFOR_STORAGEPOOL_GETINFOBYUUID_ERROR'],
        'KVM_SANGFOR_VM_GETSNAPSHOT_BYUUID_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_SANGFOR_VM_GETSNAPSHOT_BYUUID_ERROR'],
        'KVM_SANGFOR_VM_DELETESNAPSHOT_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_SANGFOR_VM_DELETESNAPSHOT_ERROR'],
        'KVM_SANGFOR_STORAGEPOOL_BYHOSTUUID_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_SANGFOR_STORAGEPOOL_BYHOSTUUID_ERROR'],
        'KVM_SANGFOR_STORAGEPOOL_LIST_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_SANGFOR_STORAGEPOOL_LIST_ERROR'],
        'KVM_SANGFOR_STORAGEPOOL_DETAIL_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_SANGFOR_STORAGEPOOL_DETAIL_ERROR'],
        'KVM_SANGFOR_VM_DELETE_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_SANGFOR_VM_DELETE_ERROR'],
        'KVM_SANGFOR_VM_CREATE_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_SANGFOR_VM_CREATE_ERROR'],

        'KVM_OPENSTACK_GENERAL_STRINGTOJSON_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_OPENSTACK_GENERAL_STRINGTOJSON_ERROR'],
        'KVM_OPENSTACK_LOGIN_GET_UNSCOPEDTOKENID_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_OPENSTACK_LOGIN_GET_UNSCOPEDTOKENID_ERROR'],
        'KVM_OPENSTACK_MODIFY_HEADER_EORROR' => Xphp::$_lang['WEB_ERROR_KVM_OPENSTACK_MODIFY_HEADER_EORROR'],
        'KVM_OPENSTACK_LOGIN_GET_TENANTNAME_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_OPENSTACK_LOGIN_GET_TENANTNAME_ERROR'],
        'KVM_SANGFOR_LOGIN_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_SANGFOR_LOGIN_ERROR'],
        'XENSERVER_VM_HAS_SNAPSHOT_MERGE_OPERATION' => Xphp::$_lang['WEB_ERROR_XENSERVER_VM_HAS_SNAPSHOT_MERGE_OPERATION'],
        'XENSERVER_PIF_IP_IS_EMPTY_ERROR' => Xphp::$_lang['WEB_ERROR_XENSERVER_PIF_IP_IS_EMPTY_ERROR'],
        'KVINFS_TASK_NOT_FOUND_ERROR' => Xphp::$_lang['WEB_ERROR_KVINFS_TASK_NOT_FOUND_ERROR'],
        'KVINFS_FILTER_DISK_ALREADY_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_KVINFS_FILTER_DISK_ALREADY_EXIST_ERROR'],
        'KVINFS_CREATE_CACHE_DIR_ERROR' => Xphp::$_lang['WEB_ERROR_KVINFS_CREATE_CACHE_DIR_ERROR'],
        'KVINFS_FUSE_UNEXPECTED_READ_REQUEST_ERROR' => Xphp::$_lang['WEB_ERROR_KVINFS_FUSE_UNEXPECTED_READ_REQUEST_ERROR'],
        'KVINFS_FILTER_DISK_NOT_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_KVINFS_FILTER_DISK_NOT_EXIST_ERROR'],
        'VM_CREATE_VM_TIMEOUT_ERROR' => Xphp::$_lang['WEB_ERROR_VM_CREATE_VM_TIMEOUT_ERROR'],
        'KVM_OPENSTACK_GET_VM_INFO_BYUUID_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_OPENSTACK_GET_VM_INFO_BYUUID_ERROR'],
        'KVM_OPENSTACK_GET_FLAVOR_INFO_BYUUID_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_OPENSTACK_GET_FLAVOR_INFO_BYUUID_ERROR'],
        'KVM_OPENSTACK_GET_ALL_TENANTS_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_OPENSTACK_GET_ALL_TENANTS_ERROR'],
        'KVM_OPENSTACK_GET_USER_ON_TENANT_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_OPENSTACK_GET_USER_ON_TENANT_ERROR'],
        'KVM_SANGFOR_GET_VMS_INFO_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_SANGFOR_GET_VMS_INFO_ERROR'],
        'KVM_SANGFOR_POWEROFF_VM_TIMEOUT' => Xphp::$_lang['WEB_ERROR_KVM_SANGFOR_POWEROFF_VM_TIMEOUT'],
        'KVM_SANGFOR_CREATE_VM_TIMEOUT' => Xphp::$_lang['WEB_ERROR_KVM_SANGFOR_CREATE_VM_TIMEOUT'],
        'KVM_SANGFOR_DELETE_VM_TIMEOUT' => Xphp::$_lang['WEB_ERROR_KVM_SANGFOR_DELETE_VM_TIMEOUT'],
        'VMWARE_HAS_NO_UNUSED_PORT_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_HAS_NO_UNUSED_PORT_ERROR'],
        'VMWARE_START_BACKUP_SERVER_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_START_BACKUP_SERVER_ERROR'],
        'VMWARE_CONNECT_TO_BACKUP_SERVER_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_CONNECT_TO_BACKUP_SERVER_ERROR'],
        'VMWARE_RECEIVE_BACKUP_SERVER_MSG_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_RECEIVE_BACKUP_SERVER_MSG_ERROR'],
        'VMWARE_SEND_ACK_TO_VM_SERVER_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_SEND_ACK_TO_VM_SERVER_ERROR'],
        'VMWARE_BACKUP_SERVER_AREADY_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_BACKUP_SERVER_AREADY_EXIST_ERROR'],
        'VMWARE_GET_VCENTER_VERSION_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_GET_VCENTER_VERSION_ERROR'],
        'VMWARE_PORT_IS_USING_BY_BACKUP_SERVER_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_PORT_IS_USING_BY_BACKUP_SERVER_ERROR'],
        'VMWARE_PORT_IS_NOT_USING_BY_BACKUP_SERVER_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_PORT_IS_NOT_USING_BY_BACKUP_SERVER_ERROR'],
        'KVM_INSTANT_RECOVERY_KVINFS_NFS_STORAGE_NOT_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_INSTANT_RECOVERY_KVINFS_NFS_STORAGE_NOT_EXIST_ERROR'],
        'KVM_INSTANT_RECOVERY_KVINFS_NFS_STORAGE_BROKEN_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_INSTANT_RECOVERY_KVINFS_NFS_STORAGE_BROKEN_ERROR'],

        'KVM_OPENSTACK_GET_NETWORKS_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_OPENSTACK_GET_NETWORKS_ERROR'],
        'KVM_OPENSTACK_CREATE_IMAGE_ENTRY_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_OPENSTACK_CREATE_IMAGE_ENTRY_ERROR'],
        'KVM_OPENSTACK_UPLOAD_IMAGE_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_OPENSTACK_UPLOAD_IMAGE_ERROR'],
        'KVM_OPENSTACK_DELETE_IMAGE_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_OPENSTACK_DELETE_IMAGE_ERROR'],
        'KVM_OPENSTACK_CREATE_FLAVOR_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_OPENSTACK_CREATE_FLAVOR_ERROR'],
        'KVM_OPENSTACK_DELETE_FLAVOR_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_OPENSTACK_DELETE_FLAVOR_ERROR'],
        'KVM_OPENSTACK_CREATE_VM_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_OPENSTACK_CREATE_VM_ERROR'],
        'KVM_OPENSTACK_CREATE_VM_TIMEOUT' => Xphp::$_lang['WEB_ERROR_KVM_OPENSTACK_CREATE_VM_TIMEOUT'],
        'KVM_OPENSTACK_GET_IMAGE_INFO_BYUUID_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_OPENSTACK_GET_IMAGE_INFO_BYUUID_ERROR'],
        'KVM_OPENSTACK_CREATE_IMAGE_TIMEOUT' => Xphp::$_lang['WEB_ERROR_KVM_OPENSTACK_CREATE_IMAGE_TIMEOUT'],
        'KVM_OPENSTACK_DELETE_VM_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_OPENSTACK_DELETE_VM_ERROR'],
        'KVM_OPENSTACK_GET_HOSTIP_BY_NAME_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_OPENSTACK_GET_HOSTIP_BY_NAME_ERROR'],

        'KVM_OPENSTACK_RESET_HEADER_EORROR' => Xphp::$_lang['WEB_ERROR_KVM_OPENSTACK_RESET_HEADER_EORROR'],
        'KVM_OPENSTACK_GET_SCOPEDTOKENID_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_OPENSTACK_GET_SCOPEDTOKENID_ERROR'],
        'KVM_OPENSTACK_GET_VM_INFO_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_OPENSTACK_GET_VM_INFO_ERROR'],
        'KVM_OPENSTACK_SERVICE_NOT_EXIST' => Xphp::$_lang['WEB_ERROR_KVM_OPENSTACK_SERVICE_NOT_EXIST'],
        'KVM_OPENSTACK_GET_HOSTS_INFO_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_OPENSTACK_GET_HOSTS_INFO_ERROR'],
        'KVM_OPENSTACK_GET_VM_TENANTID_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_OPENSTACK_GET_VM_TENANTID_ERROR'],
        'KVM_OPENSTACK_GET_VM_NAME_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_OPENSTACK_GET_VM_NAME_ERROR'],
        'KVM_OPENSTACK_GET_VM_STATE_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_OPENSTACK_GET_VM_STATE_ERROR'],
        'KVM_OPENSTACK_REFRESH_TOKENID_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_OPENSTACK_REFRESH_TOKENID_ERROR'],
        'KVM_OPENSTACK_GET_VM_INFO_BY_HOSTNAME_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_OPENSTACK_GET_VM_INFO_BY_HOSTNAME_ERROR'],
        'KVM_OPENSTACK_FIND_ADMIN_ROLE_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_OPENSTACK_FIND_ADMIN_ROLE_ERROR'],
        'KVM_OPENSTACK_VM_POWEROFF_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_OPENSTACK_VM_POWEROFF_ERROR'],
        'KVM_OPENSTACK_VM_POWERON_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_OPENSTACK_VM_POWERON_ERROR'],
        'KVM_OPENSTACK_GET_ALLTENANTS_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_OPENSTACK_GET_ALLTENANTS_ERROR'],
        'KVM_OPENSTACK_GET_VM_INFO_BY_TENANTID_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_OPENSTACK_GET_VM_INFO_BY_TENANTID_ERROR'],
        'KVM_SANGFOR_NFSPOOL_CREATE_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_SANGFOR_NFSPOOL_CREATE_ERROR'],
        'KVM_SANGFOR_NFSPOOL_CREATE_TIMEOUT' => Xphp::$_lang['WEB_ERROR_KVM_SANGFOR_NFSPOOL_CREATE_TIMEOUT'],
        'KVM_SANGFOR_STORAGE_DELETE_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_SANGFOR_STORAGE_DELETE_ERROR'],

        'VM_OPERATION_TIMEOUT_ERROR' => Xphp::$_lang['WEB_ERROR_VM_OPERATION_TIMEOUT_ERROR'],

        'KVM_SANGFOR_GET_VM_SNAPSHOT_LIST_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_SANGFOR_GET_VM_SNAPSHOT_LIST_ERROR'],

        // KVM H3C
        'KVM_H3C_SCAN_HOSTPOOL_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_H3C_SCAN_HOSTPOOL_ERROR'],
        'KVM_H3C_SCAN_CLUSTER_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_H3C_SCAN_CLUSTER_ERROR'],
        'KVM_H3C_SCAN_HOST_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_H3C_SCAN_HOST_ERROR'],
        'KVM_H3C_VM_GETINFO_BYUUID_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_H3C_VM_GETINFO_BYUUID_ERROR'],
        'KVM_H3C_HOST_GETINFO_BYUUID_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_H3C_HOST_GETINFO_BYUUID_ERROR'],
        'KVM_H3C_CLUSTER_GETINFO_BYUUID_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_H3C_CLUSTER_GETINFO_BYUUID_ERROR'],
        'KVM_H3C_POOL_GETINFO_BYUUID_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_H3C_POOL_GETINFO_BYUUID_ERROR'],
        'KVM_H3C_STRING_TO_JSON_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_H3C_STRING_TO_JSON_ERROR'],
        'KVM_H3C_HOST_GETNETWORK_BYUUID_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_H3C_HOST_GETNETWORK_BYUUID_ERROR'],
        'KVM_H3C_EMPTY_INFO_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_H3C_EMPTY_INFO_ERROR'],
        'KVM_H3C_POWEROFF_VM_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_H3C_POWEROFF_VM_ERROR'],
        'KVM_H3C_POWERON_VM_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_H3C_POWERON_VM_ERROR'],
        'KVM_H3C_CREATE_VM_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_H3C_CREATE_VM_ERROR'],
        'KVM_H3C_DELETE_VM_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_H3C_DELETE_VM_ERROR'],
        'KVM_H3C_SNAPSHORT_CREAT_EEROR' => Xphp::$_lang['WEB_ERROR_KVM_H3C_SNAPSHORT_CREAT_EEROR'],
        'KVM_H3C_NFSPOOL_CREATE_TIMEOUT' => Xphp::$_lang['WEB_ERROR_KVM_H3C_NFSPOOL_CREATE_TIMEOUT'],
        'KVM_H3C_MONITORSTASTICS_GET_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_H3C_MONITORSTASTICS_GET_ERROR'],
        'KVM_H3C_STORAGEPOOL_GET_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_H3C_STORAGEPOOL_GET_ERROR'],
        'KVM_H3C_SNAPSHOTLIST_GET_EERROR' => Xphp::$_lang['WEB_ERROR_KVM_H3C_SNAPSHOTLIST_GET_EERROR'],
        'KVM_H3C_SNAPSHOT_DELETE_EERROR' => Xphp::$_lang['WEB_ERROR_KVM_H3C_SNAPSHOT_DELETE_EERROR'],

        'KVM_OPENSTACK_GET_ALL_VOLUMES_INFO_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_OPENSTACK_GET_ALL_VOLUMES_INFO_ERROR'],
        'KVM_OPENSTACK_GET_VOLUME_SIZE_BY_UUID_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_OPENSTACK_GET_VOLUME_SIZE_BY_UUID_ERROR'],
        'KVM_SANGFOR_GET_PROCESS_STATUS_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_SANGFOR_GET_PROCESS_STATUS_ERROR'],
        'KVM_SANGFOR_POWERON_VM_TIMEOUT' => Xphp::$_lang['WEB_ERROR_KVM_SANGFOR_POWERON_VM_TIMEOUT'],
        'KVM_SANGFOR_DELETE_SNAPSHOT_TIMEOUT' => Xphp::$_lang['WEB_ERROR_KVM_SANGFOR_DELETE_SNAPSHOT_TIMEOUT'],
        'KVM_SANGFOR_DELETE_STORAGE_POOL_TIMEOUT' => Xphp::$_lang['WEB_ERROR_KVM_SANGFOR_DELETE_STORAGE_POOL_TIMEOUT'],
        'KVM_H3C_VOLUME_CREATE_EERROR' => Xphp::$_lang['WEB_ERROR_KVM_H3C_VOLUME_CREATE_EERROR'],
        'KVM_SANGFOR_CREATE_VM_SNAPSHOT_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_SANGFOR_CREATE_VM_SNAPSHOT_ERROR'],

        'VMWARE_HAS_DIFF_TIMEPOINT_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_HAS_DIFF_TIMEPOINT_EXIST_ERROR'],
        'VMWARE_DIFF_AND_INC_CAN_NOT_COEXIST_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_DIFF_AND_INC_CAN_NOT_COEXIST_ERROR'],

        'VMWARE_BUILD_EXPORT_VM_TIMEPOINT_LIST_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_BUILD_EXPORT_VM_TIMEPOINT_LIST_ERROR'],
        'VMWARE_GET_EXPORT_VALID_DATA_SIZE_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_GET_EXPORT_VALID_DATA_SIZE_ERROR'],
        'KVM_OVIRT_SNAPSHOT_NOT_CONTAINS_ALL_DISKS_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_OVIRT_SNAPSHOT_NOT_CONTAINS_ALL_DISKS_ERROR'],
        'KVM_OVIRT_SNAPSHOT_HAVE_LOCKED_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_OVIRT_SNAPSHOT_HAVE_LOCKED_ERROR'],
        'VMWARE_BUILD_METADATA_FILE_FOR_FULL_BACKUP_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_BUILD_METADATA_FILE_FOR_FULL_BACKUP_ERROR'],

        //******new******//
        'VMWARE_VDDK_SERVER_REFUSED_CONNECTION_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_VDDK_SERVER_REFUSED_CONNECTION_ERROR'],
        'VMWARE_VDDK_HOST_TCP_CONN_LOST_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_VDDK_HOST_TCP_CONN_LOST_ERROR'],

        'KVM_OPENSTACK_CREATE_VOLUME_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_OPENSTACK_CREATE_VOLUME_ERROR'],
        'KVM_OPENSTACK_GET_VOULUME_INFO_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_OPENSTACK_GET_VOULUME_INFO_ERROR'],
        'KVM_OPENSTACK_CREATE_VOLUME_TIME_OUT' => Xphp::$_lang['WEB_ERROR_KVM_OPENSTACK_CREATE_VOLUME_TIME_OUT'],
        'KVM_OPENSTACK_ATTACH_VOLUME_TO_VM_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_OPENSTACK_ATTACH_VOLUME_TO_VM_ERROR'],
        'KVM_OPENSTACK_DELETE_VOLUME_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_OPENSTACK_DELETE_VOLUME_ERROR'],
        'KVM_OPENSTACK_GET_ALL_BACKEND_POOLS_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_OPENSTACK_GET_ALL_BACKEND_POOLS_ERROR'],
        'KVM_OPENSTACK_GET_ALL_VOLUME_TYPES_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_OPENSTACK_GET_ALL_VOLUME_TYPES_ERROR'],
        'KVM_OPENSTACK_SET_VOLUME_BOOTABLE_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_OPENSTACK_SET_VOLUME_BOOTABLE_ERROR'],

        // --------- osvinfs new error code --------------------------------
        'OSVINFS_TASK_NOT_FOUND_ERROR' => Xphp::$_lang['WEB_ERROR_OSVINFS_TASK_NOT_FOUND_ERROR'],
        'OSVINFS_FILTER_DISK_ALREADY_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_OSVINFS_FILTER_DISK_ALREADY_EXIST_ERROR'],
        'OSVINFS_CREATE_CACHE_DIR_ERROR' => Xphp::$_lang['WEB_ERROR_OSVINFS_CREATE_CACHE_DIR_ERROR'],
        'OSVINFS_FUSE_UNEXPECTED_READ_REQUEST_ERROR' => Xphp::$_lang['WEB_ERROR_OSVINFS_FUSE_UNEXPECTED_READ_REQUEST_ERROR'],
        'OSVINFS_FILTER_DISK_NOT_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_OSVINFS_FILTER_DISK_NOT_EXIST_ERROR'],
        'KVM_INSTANT_RECOVERY_OSVINFS_NFS_STORAGE_NOT_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_INSTANT_RECOVERY_OSVINFS_NFS_STORAGE_NOT_EXIST_ERROR'],
        'KVM_INSTANT_RECOVERY_OSVINFS_NFS_STORAGE_BROKEN_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_INSTANT_RECOVERY_OSVINFS_NFS_STORAGE_BROKEN_ERROR'],

        'KVM_OPENSTACK_GET_VOULUMES_INFO_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_OPENSTACK_GET_VOULUMES_INFO_ERROR'],
        'KVM_OPENSTACK_GET_STORAGE_POOL_INFO_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_OPENSTACK_GET_STORAGE_POOL_INFO_ERROR'],
        'KVM_OPENSTACK_DELETE_VM_TIMEOUT' => Xphp::$_lang['WEB_ERROR_KVM_OPENSTACK_DELETE_VM_TIMEOUT'],
        'KVM_OPENSTACK_DELETE_VOLUME_TIMEOUT' => Xphp::$_lang['WEB_ERROR_KVM_OPENSTACK_DELETE_VOLUME_TIMEOUT'],

        'VMWARE_TASK_NOT_ENABLE_CBT_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_TASK_NOT_ENABLE_CBT_ERROR'],

        'VM_STORAGE_TYPE_NOT_SUPPORT_ERROR' => Xphp::$_lang['WEB_ERROR_VM_STORAGE_TYPE_NOT_SUPPORT_ERROR'],
        'VM_VHD_BLOCK_LEN_INVALID_ERROR' => Xphp::$_lang['WEB_ERROR_VM_VHD_BLOCK_LEN_INVALID_ERROR'],
        'VM_VHD_VDI_UUID_NOT_MATCH_WITH_OPEN_ONE_ERROR' => Xphp::$_lang['WEB_ERROR_VM_VHD_VDI_UUID_NOT_MATCH_WITH_OPEN_ONE_ERROR'],
        'XS_INIT_LANFREE_DRIVER_ERROR' => Xphp::$_lang['WEB_ERROR_XS_INIT_LANFREE_DRIVER_ERROR'],
        'XS_NOT_SUPPORT_BACKUP_LEVEL_ERROR' => Xphp::$_lang['WEB_ERROR_XS_NOT_SUPPORT_BACKUP_LEVEL_ERROR'],
        'XS_SR_NOT_SUPPORT_ERROR' => Xphp::$_lang['WEB_ERROR_XS_SR_NOT_SUPPORT_ERROR'],
        'VM_VHD_DISK_PHYSICAL_LEN_INVALID_ERROR' => Xphp::$_lang['WEB_ERROR_VM_VHD_DISK_PHYSICAL_LEN_INVALID_ERROR'],

        'KVM_OPENSTACK_ISO_ROOT_DISK_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_OPENSTACK_ISO_ROOT_DISK_ERROR'],
        'XS_NO_VALID_NBD_CONNECTION_INFO_FOUND_ERROR' => Xphp::$_lang['WEB_ERROR_XS_NO_VALID_NBD_CONNECTION_INFO_FOUND_ERROR'],
        'VM_GET_IP_BY_DISK_ERROR' => Xphp::$_lang['WEB_ERROR_VM_GET_IP_BY_DISK_ERROR'],
        'VM_DISK_CLUSTER_LEN_INVALID_ERROR' => Xphp::$_lang['WEB_ERROR_VM_DISK_CLUSTER_LEN_INVALID_ERROR'],
        'VM_DEE_IS_CHANGED_ERROR' => Xphp::$_lang['WEB_ERROR_VM_DEE_IS_CHANGED_ERROR'],
        'XS_VDI_IN_USE_ERROR' => Xphp::$_lang['WEB_ERROR_XS_VDI_IN_USE_ERROR'],
        'XS_VDI_IS_NOT_ENABLED_CBT_ERROR' => Xphp::$_lang['WEB_ERROR_XS_VDI_IS_NOT_ENABLED_CBT_ERROR'],
        'KVM_OPENSTACK_NO_USABLE_COMPUTE_IP_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_OPENSTACK_NO_USABLE_COMPUTE_IP_ERROR'],
        'KVM_OPENSTACK_NO_USABLE_CONTROLLER_IP_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_OPENSTACK_NO_USABLE_CONTROLLER_IP_ERROR'],
        'KVM_H3C_GET_VESION_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_H3C_GET_VESION_ERROR'],

        /* Openstack Flex snapshot api */
        'KVM_OPENSTACK_FLEX_CREATE_VM_SNAPSHOT_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_OPENSTACK_FLEX_CREATE_VM_SNAPSHOT_ERROR'],
        'KVM_OPENSTACK_FLEX_CREATE_VOLUME_SNAPSHOT_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_OPENSTACK_FLEX_CREATE_VOLUME_SNAPSHOT_ERROR'],
        'KVM_OPENSTACK_FLEX_GET_VM_SNAPSHOT_STATUS_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_OPENSTACK_FLEX_GET_VM_SNAPSHOT_STATUS_ERROR'],
        'KVM_OPENSTACK_FLEX_GET_VOLUME_SNAPSHOT_STATUS_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_OPENSTACK_FLEX_GET_VOLUME_SNAPSHOT_STATUS_ERROR'],
        'KVM_OPENSTACK_FLEX_DELETE_VM_SNAPSHOT_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_OPENSTACK_FLEX_DELETE_VM_SNAPSHOT_ERROR'],
        'KVM_OPENSTACK_FLEX_DELETE_VOLUME_SNAPSHOT_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_OPENSTACK_FLEX_DELETE_VOLUME_SNAPSHOT_ERROR'],
        'KVM_H3C_GET_PORTPROFILE_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_H3C_GET_PORTPROFILE_ERROR'],
        'KVM_H3C_NO_MACTHED_PORTPROFILE_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_H3C_NO_MACTHED_PORTPROFILE_ERROR'],
        'VM_OPENSTACK_CONTROLLER_TEST_CONNECT_ERROR' => Xphp::$_lang['WEB_ERROR_OPENSTACK_CONTROLLER_TEST_CONNECT_ERROR'],

        'KVM_OPENSTACK_KEYSTONE_VERSION_UNSUPPORT_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_OPENSTACK_KEYSTONE_VERSION_UNSUPPORT_ERROR'],
        'KVM_OPENSTACK_MODIFY_PORT_MACADDR_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_OPENSTACK_MODIFY_PORT_MACADDR_ERROR'],
        'KVM_OPENSTACK_GET_VM_PORT_INFO_BYUUID_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_OPENSTACK_GET_VM_PORT_INFO_BYUUID_ERROR'],
        'KVM_OPENSTACK_CREATE_NEUTRON_PORT_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_OPENSTACK_CREATE_NEUTRON_PORT_ERROR'],
        'KVM_OPENSTACK_DELETE_VM_PORT_BYUUID_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_OPENSTACK_DELETE_VM_PORT_BYUUID_ERROR'],

        // add xenserver sr backen failed
        'XS_SR_BACKEND_ERR_1' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_1'],
        'XS_SR_BACKEND_ERR_100' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_100'],
        'XS_SR_BACKEND_ERR_101' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_101'],
        'XS_SR_BACKEND_ERR_102' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_102'],
        'XS_SR_BACKEND_ERR_103' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_103'],
        'XS_SR_BACKEND_ERR_104' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_104'],
        'XS_SR_BACKEND_ERR_105' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_105'],
        'XS_SR_BACKEND_ERR_106' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_106'],
        'XS_SR_BACKEND_ERR_107' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_107'],
        'XS_SR_BACKEND_ERR_108' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_108'],
        'XS_SR_BACKEND_ERR_109' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_109'],
        'XS_SR_BACKEND_ERR_110' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_110'],
        'XS_SR_BACKEND_ERR_111' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_111'],
        'XS_SR_BACKEND_ERR_112' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_112'],
        'XS_SR_BACKEND_ERR_113' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_113'],
        'XS_SR_BACKEND_ERR_114' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_114'],
        'XS_SR_BACKEND_ERR_115' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_115'],
        'XS_SR_BACKEND_ERR_116' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_116'],
        'XS_SR_BACKEND_ERR_120' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_120'],
        'XS_SR_BACKEND_ERR_1200' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_1200'],
        'XS_SR_BACKEND_ERR_121' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_121'],
        'XS_SR_BACKEND_ERR_122' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_122'],
        'XS_SR_BACKEND_ERR_123' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_123'],
        'XS_SR_BACKEND_ERR_124' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_124'],
        'XS_SR_BACKEND_ERR_125' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_125'],
        'XS_SR_BACKEND_ERR_126' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_126'],
        'XS_SR_BACKEND_ERR_127' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_127'],
        'XS_SR_BACKEND_ERR_128' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_128'],
        'XS_SR_BACKEND_ERR_129' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_129'],
        'XS_SR_BACKEND_ERR_130' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_130'],
        'XS_SR_BACKEND_ERR_131' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_131'],
        'XS_SR_BACKEND_ERR_132' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_132'],
        'XS_SR_BACKEND_ERR_133' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_133'],
        'XS_SR_BACKEND_ERR_134' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_134'],
        'XS_SR_BACKEND_ERR_135' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_135'],
        'XS_SR_BACKEND_ERR_140' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_140'],
        'XS_SR_BACKEND_ERR_141' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_141'],
        'XS_SR_BACKEND_ERR_142' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_142'],
        'XS_SR_BACKEND_ERR_143' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_143'],
        'XS_SR_BACKEND_ERR_144' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_144'],
        'XS_SR_BACKEND_ERR_150' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_150'],
        'XS_SR_BACKEND_ERR_151' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_151'],
        'XS_SR_BACKEND_ERR_152' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_152'],
        'XS_SR_BACKEND_ERR_153' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_153'],
        'XS_SR_BACKEND_ERR_16' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_16'],
        'XS_SR_BACKEND_ERR_160' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_160'],
        'XS_SR_BACKEND_ERR_161' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_161'],
        'XS_SR_BACKEND_ERR_162' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_162'],
        'XS_SR_BACKEND_ERR_163' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_163'],
        'XS_SR_BACKEND_ERR_164' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_164'],
        'XS_SR_BACKEND_ERR_165' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_165'],
        'XS_SR_BACKEND_ERR_166' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_166'],
        'XS_SR_BACKEND_ERR_167' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_167'],
        'XS_SR_BACKEND_ERR_168' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_168'],
        'XS_SR_BACKEND_ERR_169' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_169'],
        'XS_SR_BACKEND_ERR_170' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_170'],
        'XS_SR_BACKEND_ERR_171' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_171'],
        'XS_SR_BACKEND_ERR_172' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_172'],
        'XS_SR_BACKEND_ERR_173' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_173'],
        'XS_SR_BACKEND_ERR_174' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_174'],
        'XS_SR_BACKEND_ERR_175' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_175'],
        'XS_SR_BACKEND_ERR_176' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_176'],
        'XS_SR_BACKEND_ERR_180' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_180'],
        'XS_SR_BACKEND_ERR_181' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_181'],
        'XS_SR_BACKEND_ERR_19' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_19'],
        'XS_SR_BACKEND_ERR_2' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_2'],
        'XS_SR_BACKEND_ERR_20' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_20'],
        'XS_SR_BACKEND_ERR_200' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_200'],
        'XS_SR_BACKEND_ERR_201' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_201'],
        'XS_SR_BACKEND_ERR_202' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_202'],
        'XS_SR_BACKEND_ERR_203' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_203'],
        'XS_SR_BACKEND_ERR_220' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_220'],
        'XS_SR_BACKEND_ERR_221' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_221'],
        'XS_SR_BACKEND_ERR_222' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_222'],
        'XS_SR_BACKEND_ERR_223' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_223'],
        'XS_SR_BACKEND_ERR_224' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_224'],
        'XS_SR_BACKEND_ERR_225' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_225'],
        'XS_SR_BACKEND_ERR_226' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_226'],
        'XS_SR_BACKEND_ERR_227' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_227'],
        'XS_SR_BACKEND_ERR_228' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_228'],
        'XS_SR_BACKEND_ERR_24' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_24'],
        'XS_SR_BACKEND_ERR_37' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_37'],
        'XS_SR_BACKEND_ERR_38' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_38'],
        'XS_SR_BACKEND_ERR_39' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_39'],
        'XS_SR_BACKEND_ERR_40' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_40'],
        'XS_SR_BACKEND_ERR_400' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_400'],
        'XS_SR_BACKEND_ERR_401' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_401'],
        'XS_SR_BACKEND_ERR_402' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_402'],
        'XS_SR_BACKEND_ERR_41' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_41'],
        'XS_SR_BACKEND_ERR_410' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_410'],
        'XS_SR_BACKEND_ERR_411' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_411'],
        'XS_SR_BACKEND_ERR_412' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_412'],
        'XS_SR_BACKEND_ERR_413' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_413'],
        'XS_SR_BACKEND_ERR_414' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_414'],
        'XS_SR_BACKEND_ERR_416' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_416'],
        'XS_SR_BACKEND_ERR_417' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_417'],
        'XS_SR_BACKEND_ERR_418' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_418'],
        'XS_SR_BACKEND_ERR_419' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_419'],
        'XS_SR_BACKEND_ERR_42' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_42'],
        'XS_SR_BACKEND_ERR_420' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_420'],
        'XS_SR_BACKEND_ERR_421' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_421'],
        'XS_SR_BACKEND_ERR_422' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_422'],
        'XS_SR_BACKEND_ERR_423' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_423'],
        'XS_SR_BACKEND_ERR_424' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_424'],
        'XS_SR_BACKEND_ERR_425' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_425'],
        'XS_SR_BACKEND_ERR_426' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_426'],
        'XS_SR_BACKEND_ERR_427' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_427'],
        'XS_SR_BACKEND_ERR_428' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_428'],
        'XS_SR_BACKEND_ERR_429' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_429'],
        'XS_SR_BACKEND_ERR_43' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_43'],
        'XS_SR_BACKEND_ERR_430' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_430'],
        'XS_SR_BACKEND_ERR_431' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_431'],
        'XS_SR_BACKEND_ERR_432' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_432'],
        'XS_SR_BACKEND_ERR_433' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_433'],
        'XS_SR_BACKEND_ERR_434' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_434'],
        'XS_SR_BACKEND_ERR_435' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_435'],
        'XS_SR_BACKEND_ERR_436' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_436'],
        'XS_SR_BACKEND_ERR_437' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_437'],
        'XS_SR_BACKEND_ERR_438' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_438'],
        'XS_SR_BACKEND_ERR_439' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_439'],
        'XS_SR_BACKEND_ERR_44' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_44'],
        'XS_SR_BACKEND_ERR_440' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_440'],
        'XS_SR_BACKEND_ERR_441' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_441'],
        'XS_SR_BACKEND_ERR_442' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_442'],
        'XS_SR_BACKEND_ERR_443' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_443'],
        'XS_SR_BACKEND_ERR_444' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_444'],
        'XS_SR_BACKEND_ERR_445' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_445'],
        'XS_SR_BACKEND_ERR_446' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_446'],
        'XS_SR_BACKEND_ERR_447' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_447'],
        'XS_SR_BACKEND_ERR_448' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_448'],
        'XS_SR_BACKEND_ERR_449' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_449'],
        'XS_SR_BACKEND_ERR_450' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_450'],
        'XS_SR_BACKEND_ERR_451' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_451'],
        'XS_SR_BACKEND_ERR_452' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_452'],
        'XS_SR_BACKEND_ERR_453' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_453'],
        'XS_SR_BACKEND_ERR_454' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_454'],
        'XS_SR_BACKEND_ERR_455' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_455'],
        'XS_SR_BACKEND_ERR_456' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_456'],
        'XS_SR_BACKEND_ERR_457' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_457'],
        'XS_SR_BACKEND_ERR_458' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_458'],
        'XS_SR_BACKEND_ERR_459' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_459'],
        'XS_SR_BACKEND_ERR_46' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_46'],
        'XS_SR_BACKEND_ERR_460' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_460'],
        'XS_SR_BACKEND_ERR_47' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_47'],
        'XS_SR_BACKEND_ERR_48' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_48'],
        'XS_SR_BACKEND_ERR_49' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_49'],
        'XS_SR_BACKEND_ERR_50' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_50'],
        'XS_SR_BACKEND_ERR_51' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_51'],
        'XS_SR_BACKEND_ERR_52' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_52'],
        'XS_SR_BACKEND_ERR_53' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_53'],
        'XS_SR_BACKEND_ERR_54' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_54'],
        'XS_SR_BACKEND_ERR_55' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_55'],
        'XS_SR_BACKEND_ERR_56' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_56'],
        'XS_SR_BACKEND_ERR_57' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_57'],
        'XS_SR_BACKEND_ERR_58' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_58'],
        'XS_SR_BACKEND_ERR_59' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_59'],
        'XS_SR_BACKEND_ERR_60' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_60'],
        'XS_SR_BACKEND_ERR_61' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_61'],
        'XS_SR_BACKEND_ERR_62' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_62'],
        'XS_SR_BACKEND_ERR_63' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_63'],
        'XS_SR_BACKEND_ERR_64' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_64'],
        'XS_SR_BACKEND_ERR_65' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_65'],
        'XS_SR_BACKEND_ERR_66' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_66'],
        'XS_SR_BACKEND_ERR_67' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_67'],
        'XS_SR_BACKEND_ERR_68' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_68'],
        'XS_SR_BACKEND_ERR_69' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_69'],
        'XS_SR_BACKEND_ERR_70' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_70'],
        'XS_SR_BACKEND_ERR_71' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_71'],
        'XS_SR_BACKEND_ERR_72' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_72'],
        'XS_SR_BACKEND_ERR_73' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_73'],
        'XS_SR_BACKEND_ERR_74' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_74'],
        'XS_SR_BACKEND_ERR_75' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_75'],
        'XS_SR_BACKEND_ERR_76' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_76'],
        'XS_SR_BACKEND_ERR_77' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_77'],
        'XS_SR_BACKEND_ERR_78' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_78'],
        'XS_SR_BACKEND_ERR_79' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_79'],
        'XS_SR_BACKEND_ERR_80' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_80'],
        'XS_SR_BACKEND_ERR_81' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_81'],
        'XS_SR_BACKEND_ERR_82' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_82'],
        'XS_SR_BACKEND_ERR_83' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_83'],
        'XS_SR_BACKEND_ERR_84' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_84'],
        'XS_SR_BACKEND_ERR_85' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_85'],
        'XS_SR_BACKEND_ERR_86' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_86'],
        'XS_SR_BACKEND_ERR_87' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_87'],
        'XS_SR_BACKEND_ERR_88' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_88'],
        'XS_SR_BACKEND_ERR_89' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_89'],
        'XS_SR_BACKEND_ERR_90' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_90'],
        'XS_SR_BACKEND_ERR_91' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_91'],
        'XS_SR_BACKEND_ERR_92' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_92'],
        'XS_SR_BACKEND_ERR_93' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_93'],
        'XS_SR_BACKEND_ERR_94' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_94'],
        'XS_SR_BACKEND_ERR_95' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_95'],
        'XS_SR_BACKEND_ERR_96' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_96'],
        'XS_SR_BACKEND_ERR_97' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_97'],
        'XS_SR_BACKEND_ERR_98' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_98'],
        'XS_SR_BACKEND_ERR_99' => Xphp::$_lang['WEB_ERROR_XS_SR_BACKEND_ERR_99'],

        //FC
        'FC_CONNECT_TO_FC_SERVER_ERROR' => Xphp::$_lang['WEB_ERROR_FC_CONNECT_TO_FC_SERVER_ERROR'],
        'FC_LOGIN_EEROR' => Xphp::$_lang['WEB_ERROR_FC_LOGIN_EEROR'],
        'FC_INCORRECT_USR_OR_PWD_ERROR' => Xphp::$_lang['WEB_ERROR_FC_INCORRECT_USR_OR_PWD_ERROR'],
        'FC_SCAN_VCENTER_ERROR' => Xphp::$_lang['WEB_ERROR_FC_SCAN_VCENTER_ERROR'],
        'FC_BACKUP_VM_TOOLS_NOT_RUNNING_ERROR' => Xphp::$_lang['WEB_ERROR_FC_BACKUP_VM_TOOLS_NOT_RUNNING_ERROR'],
        'FC_NOT_SUPPORT_BACKUP_TEMPLATE_ERROR' => Xphp::$_lang['WEB_ERROR_FC_NOT_SUPPORT_BACKUP_TEMPLATE_ERROR'],
        'FC_GET_BACKUP_PREPARE_INFO_ERROR' => Xphp::$_lang['WEB_ERROR_FC_GET_BACKUP_PREPARE_INFO_ERROR'],
        'FC_GET_SNAPSHOT_TREE_ERROR' => Xphp::$_lang['WEB_ERROR_FC_GET_SNAPSHOT_TREE_ERROR'],
        'FC_GET_SNAPSHOT_INFO_ERROR' => Xphp::$_lang['WEB_ERROR_FC_GET_SNAPSHOT_INFO_ERROR'],
        'FC_INDEPENDENT_DISK_ERROR' => Xphp::$_lang['WEB_ERROR_FC_INDEPENDENT_DISK_ERROR'],
        'FC_CREATE_SNAPSHOT_ERROR' => Xphp::$_lang['WEB_ERROR_FC_CREATE_SNAPSHOT_ERROR'],
        'FC_GET_SNAPSHOT_DISKS_ERROR' => Xphp::$_lang['WEB_ERROR_FC_GET_SNAPSHOT_DISKS_ERROR'],
        'FC_QUERY_CHANGED_DISK_INFO_ERROR' => Xphp::$_lang['WEB_ERROR_FC_QUERY_CHANGED_DISK_INFO_ERROR'],
        'FC_DELETE_SNAPSHOT_ERROR' => Xphp::$_lang['WEB_ERROR_FC_DELETE_SNAPSHOT_ERROR'],
        'FC_DISK_NUM_CHANGED_ERROR' => Xphp::$_lang['WEB_ERROR_FC_DISK_NUM_CHANGED_ERROR'],
        'FC_DISK_CHANGED_ERROR' => Xphp::$_lang['WEB_ERROR_FC_DISK_CHANGED_ERROR'],
        'FC_DISK_IS_NOT_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_FC_DISK_IS_NOT_EXIST_ERROR'],
        'FC_CBT_NOT_ENABLE_ERROR' => Xphp::$_lang['WEB_ERROR_FC_CBT_NOT_ENABLE_ERROR'],
        'FC_GET_VM_INFO_ERROR' => Xphp::$_lang['WEB_ERROR_FC_GET_VM_INFO_ERROR'],
        'FC_GET_VM_CONFIG_ERROR' => Xphp::$_lang['WEB_ERROR_FC_GET_VM_CONFIG_ERROR'],
        'FC_CONNECT_TO_CNA_ERROR' => Xphp::$_lang['WEB_ERROR_FC_CONNECT_TO_CNA_ERROR'],
        'FC_READ_REMOTE_DISK_ERROR' => Xphp::$_lang['WEB_ERROR_FC_READ_REMOTE_DISK_ERROR'],
        'FC_GET_REMOTE_DISK_INFO_ERROR' => Xphp::$_lang['WEB_ERROR_FC_GET_REMOTE_DISK_INFO_ERROR'],
        'FC_CPP_SDK_INIT_ERROR' => Xphp::$_lang['WEB_ERROR_FC_CPP_SDK_INIT_ERROR'],
        'FC_OPEN_REMOTE_DISK_ERROR' => Xphp::$_lang['WEB_ERROR_FC_OPEN_REMOTE_DISK_ERROR'],
        'FC_CLOSE_REMOTE_DISK_ERROR' => Xphp::$_lang['WEB_ERROR_FC_CLOSE_REMOTE_DISK_ERROR'],
        'FC_MODIFY_BACKUP_RESOURCE_ERROR' => Xphp::$_lang['WEB_ERROR_FC_MODIFY_BACKUP_RESOURCE_ERROR'],
        'FC_CPP_SDK_VERIFY_LUN_ERROR' => Xphp::$_lang['WEB_ERROR_FC_CPP_SDK_VERIFY_LUN_ERROR'],
        'FC_INIT_DISK_DRIVER_ERROR' => Xphp::$_lang['WEB_ERROR_FC_INIT_DISK_DRIVER_ERROR'],
        'FC_BACKUP_CONTAINER_FULL_ERROR' => Xphp::$_lang['WEB_ERROR_FC_BACKUP_CONTAINER_FULL_ERROR'],
        'FC_DELETE_BACKUP_RESOURCE_ERROR' => Xphp::$_lang['WEB_ERROR_FC_DELETE_BACKUP_RESOURCE_ERROR'],
        'FC_GET_DATASTORE_INFO_ERROR' => Xphp::$_lang['WEB_ERROR_FC_GET_DATASTORE_INFO_ERROR'],
        'FC_DEPEND_SNAPSHOT_DISK_NOT_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_FC_DEPEND_SNAPSHOT_DISK_NOT_EXIST_ERROR'],
        'FC_POWER_OFF_VM_ERROR' => Xphp::$_lang['WEB_ERROR_FC_POWER_OFF_VM_ERROR'],
        'FC_GET_HOST_DATASTORE_ERROR' => Xphp::$_lang['WEB_ERROR_FC_GET_HOST_DATASTORE_ERROR'],
        'FC_GET_HOST_NETWORK_ERROR' => Xphp::$_lang['WEB_ERROR_FC_GET_HOST_NETWORK_ERROR'],
        'FC_CREATE_VM_ERROR' => Xphp::$_lang['WEB_ERROR_FC_CREATE_VM_ERROR'],
        'FC_NOT_FIND_MATCHED_DATASTORE_ERROR' => Xphp::$_lang['WEB_ERROR_FC_NOT_FIND_MATCHED_DATASTORE_ERROR'],
        'FC_WRITE_REMOTE_DISK_ERROR' => Xphp::$_lang['WEB_ERROR_FC_WRITE_REMOTE_DISK_ERROR'],
        'FC_POWER_ON_VM_ERROR' => Xphp::$_lang['WEB_ERROR_FC_POWER_ON_VM_ERROR'],
        'FC_DELETE_VM_ERROR' => Xphp::$_lang['WEB_ERROR_FC_DELETE_VM_ERROR'],
        'FC_JAVA_SERVER_NOT_INIT_ERROR' => Xphp::$_lang['WEB_ERROR_FC_JAVA_SERVER_NOT_INIT_ERROR'],
        'FC_GET_HOST_INFO_ERROR' => Xphp::$_lang['WEB_ERROR_FC_GET_HOST_INFO_ERROR'],
        'FC_CPP_SDK_ERROR' => Xphp::$_lang['WEB_ERROR_FC_CPP_SDK_ERROR'],
        'FC_CHECK_NAS_EXITENCE_ERROR' => Xphp::$_lang['WEB_ERROR_FC_CHECK_NAS_EXITENCE_ERROR'],
        'FC_CHECK_CBT_STATUS_ERROR' => Xphp::$_lang['WEB_ERROR_FC_CHECK_CBT_STATUS_ERROR'],
        'FC_GET_VERSION_AND_LOGIN_LINK_ERROR' => Xphp::$_lang['WEB_ERROR_FC_GET_VERSION_AND_LOGIN_LINK_ERROR'],
        'FC_CONVERT_STRING_TO_JSON_ERROR' => Xphp::$_lang['WEB_ERROR_FC_CONVERT_STRING_TO_JSON_ERROR'],
        'FC_LOGIN_TO_VRM_BY_RESTFUL_API_ERROR' => Xphp::$_lang['WEB_ERROR_FC_LOGIN_TO_VRM_BY_RESTFUL_API_ERROR'],
        'FC_GET_AUTH_TOKEN_ERROR' => Xphp::$_lang['WEB_ERROR_FC_GET_AUTH_TOKEN_ERROR'],
        'FC_GET_SITE_ID_ERROR' => Xphp::$_lang['WEB_ERROR_FC_GET_SITE_ID_ERROR'],
        'FC_GET_TASK_STATUS_ERROR' => Xphp::$_lang['WEB_ERROR_FC_GET_TASK_STATUS_ERROR'],
        'FC_CREATE_STORAGE_ERROR' => Xphp::$_lang['WEB_ERROR_FC_CREATE_STORAGE_ERROR'],
        'FC_GET_STORAGE_RESOURCE_ERROR' => Xphp::$_lang['WEB_ERROR_FC_GET_STORAGE_RESOURCE_ERROR'],
        'FC_CREATE_NAS_DATASTORE_ERROR' => Xphp::$_lang['WEB_ERROR_FC_CREATE_NAS_DATASTORE_ERROR'],
        'FC_CONNECT_STORAGE_RESOURCE_ERROR' => Xphp::$_lang['WEB_ERROR_FC_CONNECT_STORAGE_RESOURCE_ERROR'],
        'FC_GET_STORAGE_UINIT_ERROR' => Xphp::$_lang['WEB_ERROR_FC_GET_STORAGE_UINIT_ERROR'],
        'FC_SCAN_VMS_OF_SCOPE_ERROR' => Xphp::$_lang['WEB_ERROR_FC_SCAN_VMS_OF_SCOPE_ERROR'],
        'FC_GET_NAS_DATASTORE_DELETE_STATUS_ERROR' => Xphp::$_lang['WEB_ERROR_FC_GET_NAS_DATASTORE_DELETE_STATUS_ERROR'],
        'FC_DISCONNECT_HOST_TO_DATASTORE_ERROR' => Xphp::$_lang['WEB_ERROR_FC_DISCONNECT_HOST_TO_DATASTORE_ERROR'],
        'FC_DELETE_STORAGE_RESOURCE_ERROR' => Xphp::$_lang['WEB_ERROR_FC_DELETE_STORAGE_RESOURCE_ERROR'],
        'FC_CONNECT_TO_FCVINFS_ERROR' => Xphp::$_lang['WEB_ERROR_FC_CONNECT_TO_FCVINFS_ERROR'],
        'FC_DATASTORE_STATUS_ABNORMAL' => Xphp::$_lang['WEB_ERROR_FC_DATASTORE_STATUS_ABNORMAL'],
        'FC_REFRESH_STORAGE_UNIT_ERROR' => Xphp::$_lang['WEB_ERROR_FC_REFRESH_STORAGE_UNIT_ERROR'],
        'FC_CPPSDK_RESOURCE_UNVAILABLE_ERROR' => Xphp::$_lang['WEB_ERROR_FC_CPPSDK_RESOURCE_UNVAILABLE_ERROR'],
        'FC_CPPSDK_SYSTEM_INTERRUPT_ERROR' => Xphp::$_lang['WEB_ERROR_FC_CPPSDK_SYSTEM_INTERRUPT_ERROR'],
        'FC_DISCONNECT_STORAGE_RESOURCE_ERROR' => Xphp::$_lang['WEB_ERROR_FC_DISCONNECT_STORAGE_RESOURCE_ERROR'],
        'FC_CHECK_SUPPORT_QUIESCE_SNAPSHOT_ERROR' => Xphp::$_lang['WEB_ERROR_FC_CHECK_SUPPORT_QUIESCE_SNAPSHOT_ERROR'],
        'FC_NOT_SUPPORT_VERSION' => Xphp::$_lang['WEB_ERROR_FC_NOT_SUPPORT_VERSION'],
        'FC_OPERATE_BY_VRM_RESTFUL_API_ERROR' => Xphp::$_lang['WEB_ERROR_FC_OPERATE_BY_VRM_RESTFUL_API_ERROR'],
        'FC_LOGIN_TO_VRM_BY_RESTFUL_API_TIMEOUT_ERROR' => Xphp::$_lang['WEB_ERROR_FC_LOGIN_TO_VRM_BY_RESTFUL_API_TIMEOUT_ERROR'],
        'FC_CHECK_LUN_ABILITY_ERROR' => Xphp::$_lang['WEB_ERROR_FC_CHECK_LUN_ABILITY_ERROR'],
        'FC_DATASTORE_IN_USE_ERROR' => Xphp::$_lang['WEB_ERROR_FC_DATASTORE_IN_USE_ERROR'],
        'FC_STORAGE_RESOURCE_IN_USE_ERROR' => Xphp::$_lang['WEB_ERROR_FC_STORAGE_RESOURCE_IN_USE_ERROR'],
        'FC_HIBERNATE_VM_ERROR' => Xphp::$_lang['WEB_ERROR_FC_HIBERNATE_VM_ERROR'],
        'FC_RESUME_VM_ERROR' => Xphp::$_lang['WEB_ERROR_FC_RESUME_VM_ERROR'],

        'FC_CPPSDK_INVALID_PARAM_ERROR' => Xphp::$_lang['WEB_ERROR_FC_CPPSDK_INVALID_PARAM_ERROR'],
        'FC_CPPSDK_VERIFY_RESULT_ERROR' => Xphp::$_lang['WEB_ERROR_FC_CPPSDK_VERIFY_RESULT_ERROR'],
        'FC_CPPSDK_CONNECT_REFUSED_ERROR' => Xphp::$_lang['WEB_ERROR_FC_CPPSDK_CONNECT_REFUSED_ERROR'],
        'FC_CPPSDK_LUN_NOT_UNREACHABLE_ERROR' => Xphp::$_lang['WEB_ERROR_FC_CPPSDK_LUN_NOT_UNREACHABLE_ERROR'],
        'FC_CPPSDK_INVALID_LUN' => Xphp::$_lang['WEB_ERROR_FC_CPPSDK_INVALID_LUN'],
        'FC_CPPSDK_LUN_NOT_EXIST' => Xphp::$_lang['WEB_ERROR_FC_CPPSDK_LUN_NOT_EXIST'],
        'FC_CPPSDK_TRANS_MODE_CONFLICT' => Xphp::$_lang['WEB_ERROR_FC_CPPSDK_TRANS_MODE_CONFLICT'],
        'FC_CPPSDK_CNA_NETOWRK_UNREACHABLE' => Xphp::$_lang['WEB_ERROR_FC_CPPSDK_CNA_NETOWRK_UNREACHABLE'],
        'FC_BACKUP_MODE_CHANGED_ERROR' => Xphp::$_lang['WEB_ERROR_FC_BACKUP_MODE_CHANGED_ERROR'],
        'FC_MAC_ALREADY_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_FC_MAC_ALREADY_EXIST_ERROR'],
        'FC_NOT_SUPPORT_DATASTORE_TYPE_EEROR' => Xphp::$_lang['WEB_ERROR_FC_NOT_SUPPORT_DATASTORE_TYPE_EEROR'],
        'FC_OPERATION_IN_PROCESS_ERROR' => Xphp::$_lang['WEB_ERROR_FC_OPERATION_IN_PROCESS_ERROR'],
        'FC_DATASTORE_ALREADY_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_FC_DATASTORE_ALREADY_EXIST_ERROR'],

        /* Openstack LVM new error num */
        'KVM_OPENSTACK_DISK_TYPE_NOT_SUPPORT_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_OPENSTACK_DISK_TYPE_NOT_SUPPORT_ERROR'],
        'KVM_OPENSTACK_CREATE_VOLUME_SNAPSHOT_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_OPENSTACK_CREATE_VOLUME_SNAPSHOT_ERROR'],
        'KVM_OPENSTACK_CREATE_VOLUME_SNAPSHOT_TIME_OUT' => Xphp::$_lang['WEB_ERROR_KVM_OPENSTACK_CREATE_VOLUME_SNAPSHOT_TIME_OUT'],
        'KVM_OPENSTACK_GET_VOLUME_SNAPSHOT_INFO_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_OPENSTACK_GET_VOLUME_SNAPSHOT_INFO_ERROR'],
        'KVM_OPENSTACK_DELETE_VOLUME_SNAPSHOT_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_OPENSTACK_DELETE_VOLUME_SNAPSHOT_ERROR'],
        'KVM_OPENSTACK_DELETE_VM_SNAPHOT_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_OPENSTACK_DELETE_VM_SNAPHOT_ERROR'],
        'KVM_OPENSTACK_GET_VM_DISK_SNAPSHOT_LIST_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_OPENSTACK_GET_VM_DISK_SNAPSHOT_LIST_ERROR'],

        'FC_CBT_STATUS_CHANGED_ERROR' => Xphp::$_lang['WEB_ERROR_FC_CBT_STATUS_CHANGED_ERROR'],
        'FC_STORAGE_RESOURCE_NOT_CONNECTED_ERROR' => Xphp::$_lang['WEB_ERROR_FC_STORAGE_RESOURCE_NOT_CONNECTED_ERROR'],
        'FC_STORAGE_RESOURCE_NOT_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_FC_STORAGE_RESOURCE_NOT_EXIST_ERROR'],

        //2018.8.10 new error_log
        'VSERVER_DISK_SR_TYPE_NOT_ALL_THE_SAME_ERROR' => Xphp::$_lang['WEB_ERROR_VSERVER_DISK_SR_TYPE_NOT_ALL_THE_SAME_ERROR'],
        'VSERVER_DISK_TYPE_NOT_BLOCK_ERROR' => Xphp::$_lang['WEB_ERROR_VSERVER_DISK_TYPE_NOT_BLOCK_ERROR'],
        'VSERVER_NOT_SUPPORT_LANFREE_ERROR' => Xphp::$_lang['WEB_ERROR_VSERVER_NOT_SUPPORT_LANFREE_ERROR'],
        'VSERVER_CREATE_SNAPSHOT_ERROR' => Xphp::$_lang['WEB_ERROR_VSERVER_CREATE_SNAPSHOT_ERROR'],

        'VM_HOST_NOT_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_VM_HOST_NOT_EXIST_ERROR'],
        'FC_GET_VOLUMES_OF_DATASTORE_ERROR' => Xphp::$_lang['WEB_ERROR_FC_GET_VOLUMES_OF_DATASTORE_ERROR'],
        'FC_DATASTORE_NAME_EXIST' => Xphp::$_lang['WEB_ERROR_FC_DATASTORE_NAME_EXIST'],

        'FC_DATASTORE_ALREADY_DETACHED_ERROR' => Xphp::$_lang['WEB_ERROR_FC_DATASTORE_ALREADY_DETACHED_ERROR'],
        'FC_DATASTORE_DETACHING_ERROR' => Xphp::$_lang['WEB_ERROR_FC_DATASTORE_DETACHING_ERROR'],

        'ICS_KVM_API_ERROR' => Xphp::$_lang['WEB_ERROR_ICS_KVM_API_ERROR'],
        'VM_HYPERVISOR_NOT_SUPPORT_OPERATION_ERROR' => Xphp::$_lang['WEB_ERROR_VM_HYPERVISOR_NOT_SUPPORT_OPERATION_ERROR'],
        'VM_DISK_MORE_THAN_ONE_SAME_NAME_ERROR' => Xphp::$_lang['WEB_ERROR_VM_DISK_MORE_THAN_ONE_SAME_NAME_ERROR'],
        'ICS_HOST_AND_EXIST_NFS_SR_NOT_SAME_DATACENTER_ERROR' => Xphp::$_lang['WEB_ERROR_ICS_HOST_AND_EXIST_NFS_SR_NOT_SAME_DATACENTER_ERROR'],

        'FC_STORAGE_RESOURCE_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_FC_STORAGE_RESOURCE_EXIST_ERROR'],
        'FC_STORAGE_RESOURCE_NAME_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_FC_STORAGE_RESOURCE_NAME_EXIST_ERROR'],
        'XS_LARGER_THAN_TWO_TB_VDI_ONLY_SUPPORT_GFS2_SR_ERROR' => Xphp::$_lang['WEB_ERROR_XS_LARGER_THAN_TWO_TB_VDI_ONLY_SUPPORT_GFS2_SR_ERROR'],
        'FC_DATASTORE_NOT_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_FC_DATASTORE_NOT_EXIST_ERROR'],
        'VM_DISK_NOT_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_VM_DISK_NOT_EXIST_ERROR'],

        //CDP
        'VMWARE_OPEN_LOG_METADATA_FILE_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_OPEN_LOG_METADATA_FILE_ERROR'],
        'VMWARE_READ_LOG_METADATA_FILE_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_READ_LOG_METADATA_FILE_ERROR'],
        'VMWARE_WRITE_LOG_METADATA_FILE_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_WRITE_LOG_METADATA_FILE_ERROR'],
        'VMWARE_OPEN_BIT_FILE_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_OPEN_BIT_FILE_ERROR'],
        'VMWARE_READ_BIT_FILE_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_READ_BIT_FILE_ERROR'],
        'VMWARE_WRITE_BIT_FILE_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_WRITE_BIT_FILE_ERROR'],
        'VMWARE_TIMESTAMP_NOT_FOUND_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_TIMESTAMP_NOT_FOUND_ERROR'],
        'VMWARE_PORT_IS_NOT_USING_BY_SYNC_SERVER_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_PORT_IS_NOT_USING_BY_SYNC_SERVER_ERROR'],
        'VMWARE_SYNC_SERVER_AREADY_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_SYNC_SERVER_AREADY_EXIST_ERROR'],
        'VMWARE_START_SYNC_SERVER_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_START_SYNC_SERVER_ERROR'],
        'VMWARE_CONNECT_TO_SYNC_SERVER_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_CONNECT_TO_SYNC_SERVER_ERROR'],
        'VMWARE_HAS_NO_VM_CDP_TASK_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_HAS_NO_VM_CDP_TASK_EXIST_ERROR'],
        'VMWARE_DISK_ALREADY_EXIST_IN_MAP_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_DISK_ALREADY_EXIST_IN_MAP_ERROR'],
        'VMWARE_NOT_FOUND_LOG_CACHE_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_NOT_FOUND_LOG_CACHE_ERROR'],
        'VMWARE_ADD_DISK_TO_VM_LOG_CACHE_MAP_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_ADD_DISK_TO_VM_LOG_CACHE_MAP_ERROR'],
        'VMWARE_LOG_CACHE_DATA_NOT_ENOUGH_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_LOG_CACHE_DATA_NOT_ENOUGH_ERROR'],
        'VMWARE_LOG_CACHE_REMAIN_SPACE_NOT_ENOUGH_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_LOG_CACHE_REMAIN_SPACE_NOT_ENOUGH_ERROR'],
        'VMWARE_PARSE_VM_LOG_CACHE_INFO_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_PARSE_VM_LOG_CACHE_INFO_ERROR'],
        'VMWARE_BUILD_VM_LOG_CACHE_INFO_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_BUILD_VM_LOG_CACHE_INFO_ERROR'],
        'VMWARE_UPDATE_VM_LOG_CACHE_INFO_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_UPDATE_VM_LOG_CACHE_INFO_ERROR'],
        'VMWARE_OPEN_LOG_CACHE_FILE_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_OPEN_LOG_CACHE_FILE_ERROR'],
        'VMWARE_READ_LOG_CACHE_FILE_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_READ_LOG_CACHE_FILE_ERROR'],
        'VMWARE_WRITE_LOG_CACHE_FILE_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_WRITE_LOG_CACHE_FILE_ERROR'],
        'VMWARE_LOG_TASK_ALREADY_EXIST_IN_MAP_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_LOG_TASK_ALREADY_EXIST_IN_MAP_ERROR'],
        'VMWARE_NOT_FOUND_LOG_TASK_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_NOT_FOUND_LOG_TASK_ERROR'],
        'VMWARE_NOT_FOUND_VM_IN_LOG_TASK_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_NOT_FOUND_VM_IN_LOG_TASK_ERROR'],
        'VMWARE_ADD_LOG_TASK_TO_MAP_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_ADD_LOG_TASK_TO_MAP_ERROR'],
        'VMWARE_LOAD_LOG_TASK_INFO_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_LOAD_LOG_TASK_INFO_ERROR'],
        'VMWARE_OPEN_BACKUP_CHAIN_FILE_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_OPEN_BACKUP_CHAIN_FILE_ERROR'],
        'VMWARE_UPDATE_VM_TMP_LOG_INFO_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_UPDATE_VM_TMP_LOG_INFO_ERROR'],
        'VMWARE_ARCHIVE_LOG_BACKUP_TIMEPOINT_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_ARCHIVE_LOG_BACKUP_TIMEPOINT_ERROR'],
        'VMWARE_RELEASE_VM_LOG_TASK_INFO_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_RELEASE_VM_LOG_TASK_INFO_ERROR'],
        'VMWARE_RELOAD_VM_LOG_TASK_INFO_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_RELOAD_VM_LOG_TASK_INFO_ERROR'],
        'VMWARE_CONNECT_TO_VRD_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_CONNECT_TO_VRD_ERROR'],
        'VMWARE_TASK_IS_NOT_RUNNING_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_TASK_IS_NOT_RUNNING_ERROR'],
        'VMWARE_QUERY_IOFILTER_INFO_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_QUERY_IOFILTER_INFO_ERROR'],
        'VMWARE_INSTALL_IOFILTER_INFO_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_INSTALL_IOFILTER_INFO_ERROR'],
        'VMWARE_RECOVERY_TIMESTAMP_NOT_CORRECT_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_RECOVERY_TIMESTAMP_NOT_CORRECT_ERROR'],
        'VMWARE_RECOVERY_TIMEPOINT_NOT_CORRENT_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_RECOVERY_TIMEPOINT_NOT_CORRENT_ERROR'],

        //----------------------------hyper-v error code-------------------------------------------
        //for vm management
        'HYPERV_SCAN_VCENTER_ERROR' => Xphp::$_lang['WEB_ERROR_HYPERV_SCAN_VCENTER_ERROR'],
        'HYPERV_SNAPSHOT_NOT_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_HYPERV_SNAPSHOT_NOT_EXIST_ERROR'],
        'HYPERV_CREATE_SNAPSHOT_ERROR' => Xphp::$_lang['WEB_ERROR_HYPERV_CREATE_SNAPSHOT_ERROR'],
        'HYPERV_DELETE_SNAPSHOT_ERROR' => Xphp::$_lang['WEB_ERROR_HYPERV_DELETE_SNAPSHOT_ERROR'],
        'HYPERV_GET_VM_INFO_ERROR' => Xphp::$_lang['WEB_ERROR_HYPERV_GET_VM_INFO_ERROR'],
        'HYPERV_GET_VM_CONFIGURE_INFORMATION_ERROR' => Xphp::$_lang['WEB_ERROR_HYPERV_GET_VM_CONFIGURE_INFORMATION_ERROR'],
        'HYPERV_CREATE_VM_ERROR' => Xphp::$_lang['WEB_ERROR_HYPERV_CREATE_VM_ERROR'],
        'HYPERV_DELETE_VM_ERROR' => Xphp::$_lang['WEB_ERROR_HYPERV_DELETE_VM_ERROR'],
        'HYPERV_POWER_ON_VM_ERROR' => Xphp::$_lang['WEB_ERROR_HYPERV_POWER_ON_VM_ERROR'],
        'HYPERV_POWER_OFF_VM_ERROR' => Xphp::$_lang['WEB_ERROR_HYPERV_POWER_OFF_VM_ERROR'],
        'HYPERV_SUSPEND_VM_ERROR' => Xphp::$_lang['WEB_ERROR_HYPERV_SUSPEND_VM_ERROR'],
        'HYPERV_RESUME_VM_ERROR' => Xphp::$_lang['WEB_ERROR_HYPERV_RESUME_VM_ERROR'],
        'HYPERV_VM_ALREADY_POWERON_ERROR' => Xphp::$_lang['WEB_ERROR_HYPERV_VM_ALREADY_POWERON_ERROR'],
        'HYPERV_VM_ALREADY_POWEROFF_ERROR' => Xphp::$_lang['WEB_ERROR_HYPERV_VM_ALREADY_POWEROFF_ERROR'],
        'HYPERV_VM_NOT_SUSPEND_ERROR' => Xphp::$_lang['WEB_ERROR_HYPERV_VM_NOT_SUSPEND_ERROR'],
        'HYPERV_VM_NOT_POWERON_ERROR' => Xphp::$_lang['WEB_ERROR_HYPERV_VM_NOT_POWERON_ERROR'],
        'HYPERV_GET_VM_ALL_DISKS_ERROR' => Xphp::$_lang['WEB_ERROR_HYPERV_GET_VM_ALL_DISKS_ERROR'],
        'HYPERV_NOT_FIND_VM_NAME_ERROR' => Xphp::$_lang['WEB_ERROR_HYPERV_NOT_FIND_VM_NAME_ERROR'],

        //for host
        'HYPERV_SCAN_DATASTORE_ERROR' => Xphp::$_lang['WEB_ERROR_HYPERV_SCAN_DATASTORE_ERROR'],
        'HYPERV_DATASTORE_NOT_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_HYPERV_DATASTORE_NOT_EXIST_ERROR'],
        'HYPERV_DATASTORE_SPACE_NOT_ENOUGH_ERROR' => Xphp::$_lang['WEB_ERROR_HYPERV_DATASTORE_SPACE_NOT_ENOUGH_ERROR'],
        'HYPERV_CHECK_AND_CREATE_DIR_ERROR' => Xphp::$_lang['WEB_ERROR_HYPERV_CHECK_AND_CREATE_DIR_ERROR'],
        'HYPERV_SCAN_VIRTUAL_SWITCH_ERROR' => Xphp::$_lang['WEB_ERROR_HYPERV_SCAN_VIRTUAL_SWITCH_ERROR'],

        //for hyper-v disk transport
        'HYPERV_SET_ENCRYPTED_TRANSMISSION_ERROR' => Xphp::$_lang['WEB_ERROR_HYPERV_SET_ENCRYPTED_TRANSMISSION_ERROR'],
        'HYPERV_OPEN_DISK_ERROR' => Xphp::$_lang['WEB_ERROR_HYPERV_OPEN_DISK_ERROR'],
        'HYPERV_CLOSE_DISK_ERROR' => Xphp::$_lang['WEB_ERROR_HYPERV_CLOSE_DISK_ERROR'],
        'HYPERV_GET_DISK_BITMAP_ERROR' => Xphp::$_lang['WEB_ERROR_HYPERV_GET_DISK_BITMAP_ERROR'],
        'HYPERV_READ_MAPPED_DATA_ERROR' => Xphp::$_lang['WEB_ERROR_HYPERV_READ_MAPPED_DATA_ERROR'],
        'HYPERV_READ_BLOCK_DATA_ERROR' => Xphp::$_lang['WEB_ERROR_HYPERV_READ_BLOCK_DATA_ERROR'],
        'HYPERV_WRITE_BLOCK_DATA_ERROR' => Xphp::$_lang['WEB_ERROR_HYPERV_WRITE_BLOCK_DATA_ERROR'],
        'HYPERV_WRITE_METADATA_ERROR' => Xphp::$_lang['WEB_ERROR_HYPERV_WRITE_METADATA_ERROR'],
        'HYPERV_WRITE_VHD_FOOTER_ERROR' => Xphp::$_lang['WEB_ERROR_HYPERV_WRITE_VHD_FOOTER_ERROR'],

        //for hyper-v agent service
        'HYPERV_CONNECT_VM_MANAGEMENT_AGENT_SERVICE_ERROR' => Xphp::$_lang['WEB_ERROR_HYPERV_CONNECT_VM_MANAGEMENT_AGENT_SERVICE_ERROR'],
        'HYPERV_CONNECT_DISK_TRANSPORT_AGENT_SERVICE_ERROR' => Xphp::$_lang['WEB_ERROR_HYPERV_CONNECT_DISK_TRANSPORT_AGENT_SERVICE_ERROR'],

        //for backup and recovery
        'HYPERV_SAVE_SELF_EXPLAN_FILE_ERROR' => Xphp::$_lang['WEB_ERROR_HYPERV_SAVE_SELF_EXPLAN_FILE_ERROR'],
        'HYPERV_BUILD_BACKUP_VM_LIST_ERROR' => Xphp::$_lang['WEB_ERROR_HYPERV_BUILD_BACKUP_VM_LIST_ERROR'],
        'HYPERV_BACKUP_VM_IS_NOT_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_HYPERV_BACKUP_VM_IS_NOT_EXIST_ERROR'],
        'HYPERV_NOT_SUPPORT_BACKUP_TEMPLATE_ERROR' => Xphp::$_lang['WEB_ERROR_HYPERV_NOT_SUPPORT_BACKUP_TEMPLATE_ERROR'],
        'HYPERV_OPEN_BACKUP_FILE_ERROR' => Xphp::$_lang['WEB_ERROR_HYPERV_OPEN_BACKUP_FILE_ERROR'],
        'HYPERV_OPEN_BITMAP_FILE_ERROR' => Xphp::$_lang['WEB_ERROR_HYPERV_OPEN_BITMAP_FILE_ERROR'],
        'HYPERV_COMPRESS_ERROR' => Xphp::$_lang['WEB_ERROR_HYPERV_COMPRESS_ERROR'],
        'HYPERV_WRITE_BACKUP_FILE_ERROR' => Xphp::$_lang['WEB_ERROR_HYPERV_WRITE_BACKUP_FILE_ERROR'],
        'HYPERV_WRITE_BITMAP_FILE_ERROR' => Xphp::$_lang['WEB_ERROR_HYPERV_WRITE_BITMAP_FILE_ERROR'],
        'HYPERV_OPEN_LATEST_BITMAP_FILE_ERROR' => Xphp::$_lang['WEB_ERROR_HYPERV_OPEN_LATEST_BITMAP_FILE_ERROR'],
        'HYPERV_READ_LATEST_BITMAP_FILE_ERROR' => Xphp::$_lang['WEB_ERROR_HYPERV_READ_LATEST_BITMAP_FILE_ERROR'],
        'HYPERV_CREATE_BACKUP_DIR_ERROR' => Xphp::$_lang['WEB_ERROR_HYPERV_CREATE_BACKUP_DIR_ERROR'],
        'HYPERV_LATEST_TIMEPOINT_NOT_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_HYPERV_LATEST_TIMEPOINT_NOT_EXIST_ERROR'],
        'HYPERV_DISK_NUM_CHANGED_ERROR' => Xphp::$_lang['WEB_ERROR_HYPERV_DISK_NUM_CHANGED_ERROR'],
        'HYPERV_DISK_CHANGED_ERROR' => Xphp::$_lang['WEB_ERROR_HYPERV_DISK_CHANGED_ERROR'],
        'HYPERV_DIFF_AND_INC_CAN_NOT_COEXIST_ERROR' => Xphp::$_lang['WEB_ERROR_HYPERV_DIFF_AND_INC_CAN_NOT_COEXIST_ERROR'],
        'HYPERV_BLOCK_SIZE_CHANGED_ERROR' => Xphp::$_lang['WEB_ERROR_HYPERV_BLOCK_SIZE_CHANGED_ERROR'],
        'HYPERV_OPEN_DEEP_VALID_DATA_BITMAP_FILE_ERROR' => Xphp::$_lang['WEB_ERROR_HYPERV_OPEN_DEEP_VALID_DATA_BITMAP_FILE_ERROR'],
        'HYPERV_READ_DEEP_VALIA_DATA_BITMAP_FILE_ERROR' => Xphp::$_lang['WEB_ERROR_HYPERV_READ_DEEP_VALIA_DATA_BITMAP_FILE_ERROR'],
        'HYPERV_WRITE_DEEP_VALIA_DATA_BITMAP_FILE_ERROR' => Xphp::$_lang['WEB_ERROR_HYPERV_WRITE_DEEP_VALIA_DATA_BITMAP_FILE_ERROR'],
        'HYPERV_OPEN_BINARY_BITMAP_FILE_ERROR' => Xphp::$_lang['WEB_ERROR_HYPERV_OPEN_BINARY_BITMAP_FILE_ERROR'],
        'HYPERV_READ_BINARY_BITMAP_FILE_ERROR' => Xphp::$_lang['WEB_ERROR_HYPERV_READ_BINARY_BITMAP_FILE_ERROR'],
        'HYPERV_WRITE_BINARY_BITMAP_FILE_ERROR' => Xphp::$_lang['WEB_ERROR_HYPERV_WRITE_BINARY_BITMAP_FILE_ERROR'],
        'HYPERV_OPEN_RESULT_MERGE_BITMAP_FILE_ERROR' => Xphp::$_lang['WEB_ERROR_HYPERV_OPEN_RESULT_MERGE_BITMAP_FILE_ERROR'],
        'HYPERV_READ_RESULT_MERGE_BITMAP_FILE_ERROR' => Xphp::$_lang['WEB_ERROR_HYPERV_READ_RESULT_MERGE_BITMAP_FILE_ERROR'],
        'HYPERV_WRITE_RESULT_MERGE_BITMAP_FILE_ERROR' => Xphp::$_lang['WEB_ERROR_HYPERV_WRITE_RESULT_MERGE_BITMAP_FILE_ERROR'],
        'HYPERV_BINARY_BITMAP_FILE_SIZE_NOT_EQUAL_TO_DEEP_VALID_BITMAP_FILE_SIZE' => Xphp::$_lang['WEB_ERROR_HYPERV_BINARY_BITMAP_FILE_SIZE_NOT_EQUAL_TO_DEEP_VALID_BITMAP_FILE_SIZE'],

        'HYPERV_BUILD_RECOVERY_VM_LIST_ERROR' => Xphp::$_lang['WEB_ERROR_HYPERV_BUILD_RECOVERY_VM_LIST_ERROR'],
        'HYPERV_GET_RECOVERY_TOTAL_SIZE_ERROR' => Xphp::$_lang['WEB_ERROR_HYPERV_GET_RECOVERY_TOTAL_SIZE_ERROR'],
        'HYPERV_BACKUP_CHAIN_IS_MERGING_ERROR' => Xphp::$_lang['WEB_ERROR_HYPERV_BACKUP_CHAIN_IS_MERGING_ERROR'],
        'HYPERV_BACKUP_CHAIN_IS_USING_ERROR' => Xphp::$_lang['WEB_ERROR_HYPERV_BACKUP_CHAIN_IS_USING_ERROR'],
        'HYPERV_READ_BITMAP_FILE_ERROR' => Xphp::$_lang['WEB_ERROR_HYPERV_READ_BITMAP_FILE_ERROR'],
        'HYPERV_FIND_BACKUP_FILE_ID_ERROR' => Xphp::$_lang['WEB_ERROR_HYPERV_FIND_BACKUP_FILE_ID_ERROR'],
        'HYPERV_READ_BACKUP_FILE_ERROR' => Xphp::$_lang['WEB_ERROR_HYPERV_READ_BACKUP_FILE_ERROR'],
        'HYPERV_DECOMPRESS_ERROR' => Xphp::$_lang['WEB_ERROR_HYPERV_DECOMPRESS_ERROR'],
        'HYPERV_PARSE_VM_CONFIG_ERROR' => Xphp::$_lang['WEB_ERROR_HYPERV_PARSE_VM_CONFIG_ERROR'],
        'HYPERV_GET_RECOVERY_VALID_DATA_SIZE_ERROR' => Xphp::$_lang['WEB_ERROR_HYPERV_GET_RECOVERY_VALID_DATA_SIZE_ERROR'],

        //BACKUP COPY
        'BACKUP_COPY_CLIENT_INIT_ERROR' => Xphp::$_lang['WEB_ERROR_BACKUP_COPY_CLIENT_INIT_ERROR'],
        'BACKUP_COPY_CONNECT_TO_SERVER_ERROR' => Xphp::$_lang['WEB_ERROR_BACKUP_COPY_CONNECT_TO_SERVER_ERROR'],
        'BACKUP_COPY_GET_BACKUP_REPOSITORY_INFO_ERROR' => Xphp::$_lang['WEB_ERROR_HYPERV_BUILD_RECOVERY_VM_LIST_ERROR'],
        'BACKUP_COPY_TASK_ALREADY_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_BACKUP_COPY_TASK_ALREADY_EXIST_ERROR'],
        'BACKUP_COPY_TASK_VM_LIST_EMPTY_ERROR' => Xphp::$_lang['WEB_ERROR_BACKUP_COPY_TASK_VM_LIST_EMPTY_ERROR'],
        'BACKUP_COPY_CHECK_SOURCE_STATUS_ERROR' => Xphp::$_lang['WEB_ERROR_BACKUP_COPY_CHECK_SOURCE_STATUS_ERROR'],
        'BACKUP_COPY_SOURCE_BACKUPS_IS_LOCKED_ERROR' => Xphp::$_lang['WEB_ERROR_BACKUP_COPY_SOURCE_BACKUPS_IS_LOCKED_ERROR'],
        'BACKUP_COPY_GET_SOURCE_TIMEPOINT_ERROR' => Xphp::$_lang['WEB_ERROR_BACKUP_COPY_GET_SOURCE_TIMEPOINT_ERROR'],
        'BACKUP_COPY_SERVER_INIT_ERROR' => Xphp::$_lang['WEB_ERROR_BACKUP_COPY_SERVER_INIT_ERROR'],
        'BACKUP_COPY_GET_REMOTE_STORAGE_STATISTICS_ERROR' => Xphp::$_lang['WEB_ERROR_BACKUP_COPY_GET_REMOTE_STORAGE_STATISTICS_ERROR'],
        'BACKUP_COPY_OPEN_TIMEPOINT_DIRECTORY_ERROR' => Xphp::$_lang['WEB_ERROR_BACKUP_COPY_OPEN_TIMEPOINT_DIRECTORY_ERROR'],
        'BACKUP_COPY_COPY_TIMEPOINT_ERROR' => Xphp::$_lang['WEB_ERROR_BACKUP_COPY_COPY_TIMEPOINT_ERROR'],
        'BACKUP_COPY_GET_STORAGE_FREE_SPACE_ERROR' => Xphp::$_lang['WEB_ERROR_BACKUP_COPY_GET_STORAGE_FREE_SPACE_ERROR'],
        'BACKUP_COPY_NOT_ENOUGH_SPACE_ERROR' => Xphp::$_lang['WEB_ERROR_BACKUP_COPY_NOT_ENOUGH_SPACE_ERROR'],
        'BACKUP_COPY_GET_STORAGE_MOUNT_POINT_ERROR' => Xphp::$_lang['WEB_ERROR_BACKUP_COPY_GET_STORAGE_MOUNT_POINT_ERROR'],
        'BACKUP_COPY_CREATE_DIRECTORY_ERROR' => Xphp::$_lang['WEB_ERROR_BACKUP_COPY_CREATE_DIRECTORY_ERROR'],
        'BACKUP_COPY_INIT_STORAGE_OPERATOR_EEROR' => Xphp::$_lang['WEB_ERROR_BACKUP_COPY_INIT_STORAGE_OPERATOR_EEROR'],
        'BACKUP_COPY_GET_CONTENTS_OF_STORAGE_ERROR' => Xphp::$_lang['WEB_ERROR_BACKUP_COPY_GET_CONTENTS_OF_STORAGE_ERROR'],
        'BACKUP_COPY_GET_STORAGE_INFO_ERROR' => Xphp::$_lang['WEB_ERROR_BACKUP_COPY_GET_STORAGE_INFO_ERROR'],
        'BACKUP_COPY_INIT_OBJECT_OPERATOR_EEROR' => Xphp::$_lang['WEB_ERROR_BACKUP_COPY_INIT_OBJECT_OPERATOR_EEROR'],
        'BACKUP_COPY_OPEN_OBJECT_ERROR' => Xphp::$_lang['WEB_ERROR_BACKUP_COPY_OPEN_OBJECT_ERROR'],
        'BACKUP_COPY_OBJECT_NOT_OPEN_ERROR' => Xphp::$_lang['WEB_ERROR_BACKUP_COPY_OBJECT_NOT_OPEN_ERROR'],
        'BACKUP_COPY_DELETE_TARGET_EROOR' => Xphp::$_lang['WEB_ERROR_BACKUP_COPY_DELETE_TARGET_EROOR'],
        'BACKUP_COPY_GET_OBJECT_SIZE_ERROR' => Xphp::$_lang['WEB_ERROR_BACKUP_COPY_GET_OBJECT_SIZE_ERROR'],
        'BACKUP_COPY_READ_DATA_ERROR' => Xphp::$_lang['WEB_ERROR_BACKUP_COPY_READ_DATA_ERROR'],
        'BACKUP_COPY_WRITE_DATA_ERROR' => Xphp::$_lang['WEB_ERROR_BAKCUP_COPY_WRITE_DATA_ERROR'],
        'BACKUP_COPY_CLOSE_TARGET_ERROR' => Xphp::$_lang['WEB_ERROR_BACKUP_COPY_CLOSE_TARGET_ERROR'],
        'BACKUP_COPY_CREATE_NEW_TIMEPOINT_ERROR' => Xphp::$_lang['WEB_ERROR_BACKUP_COPY_CREATE_NEW_TIMEPOINT_ERROR'],
        'BACKUP_COPY_INCORRECT_USER_AND_PASSWORD' => Xphp::$_lang['WEB_ERROR_BACKUP_COPY_INCORRECT_USER_AND_PASSWORD'],
        'BACKUP_COPY_GET_REMOTE_BACKUP_COPY_TIMEPOINTS_ERROR' => Xphp::$_lang['WEB_ERROR_BACKUP_COPY_GET_REMOTE_BACKUP_COPY_TIMEPOINTS_ERROR'],
        'BACKUP_COPY_IMPORT_COPY_TIMEPOINT_ERROR' => Xphp::$_lang['WEB_ERROR_BACKUP_COPY_IMPORT_COPY_TIMEPOINT_ERROR'],
        'BACKUP_COPY_CREAT_SELF_DESCRIPTION_FILE_ERROR' => Xphp::$_lang['WEB_ERROR_BACKUP_COPY_CREAT_SELF_DESCRIPTION_FILE_ERROR'],
        'BACKUP_COPY_GET_STORAGET_UUID_ERROR' => Xphp::$_lang['WEB_ERROR_BACKUP_COPY_GET_STORAGET_UUID_ERROR'],
        'BACKUP_COPY_GET_TIMEPOINT_INFO_ERROR' => Xphp::$_lang['WEB_ERROR_BACKUP_COPY_GET_TIMEPOINT_INFO_ERROR'],
        'BACKUP_COPY_DELETE_TIMEPOINT_ERROR' => Xphp::$_lang['WEB_ERROR_BACKUP_COPY_DELETE_TIMEPOINT_ERROR'],
        'BACKUP_COPY_NO_NEW_RESTORE_POINT_FOUND' => Xphp::$_lang['WEB_ERROR_BACKUP_COPY_NO_NEW_RESTORE_POINT_FOUND'],
        'BACKUP_COPY_INSERT_BACKUP_TIMEPOINT_INFO_ERROR' => Xphp::$_lang['WEB_ERROR_BACKUP_COPY_INSERT_BACKUP_TIMEPOINT_INFO_ERROR'],
        'BACKUP_COPY_TIMEPOINT_NOT_AVAILABLE_ERROR' => Xphp::$_lang['WEB_ERROR_BACKUP_COPY_TIMEPOINT_NOT_AVAILABLE_ERROR'],
        'BACKUP_COPY_TIMEPOINT_NOT_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_BACKUP_COPY_TIMEPOINT_NOT_EXIST_ERROR'],
        'XENSERVER_VM_SNAPSHOT_IS_MERGING_ERROR' => Xphp::$_lang['WEB_ERROR_XENSERVER_VM_SNAPSHOT_IS_MERGING_ERROR'],
        'BACKUP_COPY_TIMEPOINT_MERGING_ERROR' => Xphp::$_lang['WEB_ERROR_BACKUP_COPY_TIMEPOINT_MERGING_ERROR'],
        'BACKUP_COPY_CREATE_THREAD_FOR_MERGE_ERROR' => Xphp::$_lang['WEB_ERROR_BACKUP_COPY_CREATE_THREAD_FOR_MERGE_ERROR'],
        'BACKUP_COPY_TIMEPOINT_IN_USE_ERROR' => Xphp::$_lang['WEB_ERROR_BACKUP_COPY_TIMEPOINT_IN_USE_ERROR'],

        'BACKUP_COPY_STORAGE_OPERATOR_NOT_INIT_ERROR' => Xphp::$_lang['WEB_ERROR_BACKUP_COPY_STORAGE_OPERATOR_NOT_INIT_ERROR'],
        'BACKUP_COPY_OBJECT_OPERATOR_NOT_INIT_ERROR' => Xphp::$_lang['WEB_ERROR_BACKUP_COPY_OBJECT_OPERATOR_NOT_INIT_ERROR'],
        'BACKUP_COPY_NETWORK_FAULT_ERROR' => Xphp::$_lang['WEB_ERROR_BACKUP_COPY_NETWORK_FAULT_ERROR'],
        'BACKUP_COPY_SET_WRITE_OFFSET_ERROR' => Xphp::$_lang['WEB_ERROR_BACKUP_COPY_SET_WRITE_OFFSET_ERROR'],
        'BACKUP_COPY_UPDATE_BITMAP_ERROR' => Xphp::$_lang['WEB_ERROR_BACKUP_COPY_UPDATE_BITMAP_ERROR'],
        'KVM_SANGFOR_API_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_SANGFOR_API_ERROR'],
        'BACKUP_COPY_UNKOWN_OPCODE_ERROR' => Xphp::$_lang['WEB_ERROR_BACKUP_COPY_UNKOWN_OPCODE_ERROR'],

        'FC_CPPSDK_BROKEN_PIPE_ERROR' => Xphp::$_lang['WEB_ERROR_FC_CPPSDK_BROKEN_PIPE_ERROR'],
        'FC_CPP_FATAL_ERROR' => Xphp::$_lang['WEB_ERROR_FC_CPP_FATAL_ERROR'],
        'BACKUP_COPY_SOURCE_TAKS_NOT_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_BACKUP_COPY_SOURCE_TAKS_NOT_EXIST_ERROR'],
        'KVM_OPENSTACK_GET_AVAILABILITY_ZONE_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_OPENSTACK_GET_AVAILABILITY_ZONE_ERROR'],

        'BACKUP_COPY_LIST_BUCKET_ERROR' => Xphp::$_lang['WEB_ERROR_BACKUP_COPY_LIST_BUCKET_ERROR'],
        'BACKUP_COPY_BUCKET_NOT_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_BACKUP_COPY_BUCKET_NOT_EXIST_ERROR'],

        'VM_DISK_CONFIG_INFO_CHANGE_ERROR' => Xphp::$_lang['WEB_ERROR_VM_DISK_CONFIG_INFO_CHANGE_ERROR'],
        'BACKUP_COPY_LIST_FOLDER_OF_BUCKET_ERROR' => Xphp::$_lang['WEB_ERROR_BACKUP_COPY_LIST_FOLDER_OF_BUCKET_ERROR'],
        'BACKUP_COPY_SUB_FOLDER_NOT_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_BACKUP_COPY_SUB_FOLDER_NOT_EXIST_ERROR'],
        'BACKUP_COPY_OBJECT_NOT_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_BACKUP_COPY_OBJECT_NOT_EXIST_ERROR'],
        'BACKUP_COPY_OBJECT_INCORRECT_URL_ERROR' => Xphp::$_lang['WEB_ERROR_BACKUP_COPY_OBJECT_INCORRECT_URL_ERROR'],

        'BACKUP_COPY_UPLOAD_OBJECT_ERROR' => Xphp::$_lang['WEB_ERROR_BACKUP_COPY_UPLOAD_OBJECT_ERROR'],
        'BACKUP_COPY_DOWNLOAD_OBJECT_ERROR' => Xphp::$_lang['WEB_ERROR_BACKUP_COPY_DOWNLOAD_OBJECT_ERROR'],
        'BACKUP_COPY_CALCULATE_TASK_SIZE_ERROR' => Xphp::$_lang['WEB_ERROR_BACKUP_COPY_CALCULATE_TASK_SIZE_ERROR'],
        'BACKUP_COPY_GET_TIMEPOINT_BLOCK_SIZE_ERROR' => Xphp::$_lang['WEB_ERROR_BACKUP_COPY_GET_TIMEPOINT_BLOCK_SIZE_ERROR'],

        'VM_BACKUP_CHAIN_IS_MERGING_ERROR' => Xphp::$_lang['WEB_ERROR_VM_BACKUP_CHAIN_IS_MERGING_ERROR'],

        /* zstack error code */
        'ZSTACK_API_ERROR' => Xphp::$_lang['WEB_ERROR_ZSTACK_API_ERROR'],
        'ZSTACK_SESSION_INVALID' => Xphp::$_lang['WEB_ERROR_ZSTACK_SESSION_INVALID'],
        'ZSTACK_CLUSTER_NOT_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_ZSTACK_CLUSTER_NOT_EXIST_ERROR'],
        'ZSTACK_ZONE_NOT_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_ZSTACK_ZONE_NOT_EXIST_ERROR'],
        'ZSTACK_L2_NETWORK_NOT_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_ZSTACK_L2_NETWORK_NOT_EXIST_ERROR'],
        'ZSTACK_L3_NETWORK_NOT_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_ZSTACK_L3_NETWORK_NOT_EXIST_ERROR'],
        'ZSTACK_VM_OFFERING_NOT_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_ZSTACK_VM_OFFERING_NOT_EXIST_ERROR'],
        'ZSTACK_DISK_OFFERING_NOT_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_ZSTACK_DISK_OFFERING_NOT_EXIST_ERROR'],
        'ZSTACK_VOLUME_NOT_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_ZSTACK_VOLUME_NOT_EXIST_ERROR'],
        'ZSTACK_CREATE_VOLUME_SNAPSHOT_ERROR' => Xphp::$_lang['WEB_ERROR_ZSTACK_CREATE_VOLUME_SNAPSHOT_ERROR'],

        'ZSTACK_CREATE_NFS_STORAGE_ERROR' => Xphp::$_lang['WEB_ERROR_ZSTACK_CREATE_NFS_STORAGE_ERROR'],
        'ZSTACK_CREATE_DISK_OFFERING_ERROR' => Xphp::$_lang['WEB_ERROR_ZSTACK_CREATE_DISK_OFFERING_ERROR'],
        'ZSTACK_CREATE_VM_OFFERING_ERROR' => Xphp::$_lang['WEB_ERROR_ZSTACK_CREATE_VM_OFFERING_ERROR'],
        'ZSTACK_VOLUME_INSTALL_PATH_INVALID_ERROR' => Xphp::$_lang['WEB_ERROR_ZSTACK_VOLUME_INSTALL_PATH_INVALID_ERROR'],
        'ZSTACK_NOT_SUPPORT_BACKUP_STORAGE_TYPE_ERROR' => Xphp::$_lang['WEB_ERROR_ZSTACK_NOT_SUPPORT_BACKUP_STORAGE_TYPE_ERROR'],

        'ZSTACK_IMAGE_NOT_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_ZSTACK_IMAGE_NOT_EXIST_ERROR'],
        'ZSTACK_BACKUP_STORAGE_NOT_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_ZSTACK_BACKUP_STORAGE_NOT_EXIST_ERROR'],
        'ZSTACK_CANNOT_FIND_ISO_IMAGE_ERROR' => Xphp::$_lang['WEB_ERROR_ZSTACK_CANNOT_FIND_ISO_IMAGE_ERROR'],
        'ZSTACK_CREATE_VM_ERROR' => Xphp::$_lang['WEB_ERROR_ZSTACK_CREATE_VM_ERROR'],
        'ZSTACK_CREATE_VOLUME_ERROR' => Xphp::$_lang['WEB_ERROR_ZSTACK_CREATE_VOLUME_ERROR'],
        'ZSTACK_ATTACH_VOLUME_TO_VM_ERROR' => Xphp::$_lang['WEB_ERROR_ZSTACK_CREATE_VOLUME_ERROR'],
        'ZSTACK_DOWNLOAD_CEPH_CONNECTION_INFO_ERROR' => Xphp::$_lang['WEB_ERROR_ZSTACK_DOWNLOAD_CEPH_CONNECTION_INFO_ERROR'],
        'BACKUP_COPY_GET_ARCHIVE_TIMEPOINT_LIST_ERROR' => Xphp::$_lang['WEB_ERROR_GET_ARCHIVE_TIMEPOINT_LIST_ERROR'],

        /* for new disk driver interface */
        'VM_DISK_CLUSTER_SIZE_UNABLE_DIVIDE_ERROR' => Xphp::$_lang['WEB_ERROR_VM_DISK_CLUSTER_SIZE_UNABLE_DIVIDE_ERROR'],
        'VM_DISK_INVALID_CLUSTER_SIZE_ERROR' => Xphp::$_lang['WEB_ERROR_VM_DISK_INVALID_CLUSTER_SIZE_ERROR'],
        'VM_SNAPSHOT_ALREADY_CREATED_ERROR' => Xphp::$_lang['WEB_ERROR_VM_SNAPSHOT_ALREADY_CREATED_ERROR'],
        'VM_SNAPSHOT_THREAD_JOIN_TIMEOUT_ERROR' => Xphp::$_lang['WEB_ERROR_VM_SNAPSHOT_THREAD_JOIN_TIMEOUT_ERROR'],

        /* for fusioncompute kvm*/
        'FC_KVM_ACCOUNT_LOCKED_ERROR' => Xphp::$_lang['WEB_ERROR_FC_KVM_ACCOUNT_LOCKED_ERROR'],
        'FC_KVM_VERSION_DISMATCHED_ERROR' => Xphp::$_lang['WEB_ERROR_FC_KVM_VERSION_DISMATCHED_ERROR'],
        'FC_KVM_GET_TASK_URI_ERROR' => Xphp::$_lang['WEB_ERROR_FC_KVM_GET_TASK_URI_ERROR'],
        'FC_KVM_TARGET_NOT_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_FC_KVM_TARGET_NOT_EXIST_ERROR'],
        'FC_KVM_JSON_KEY_NOT_EXIST' => Xphp::$_lang['WEB_ERROR_FC_KVM_JSON_KEY_NOT_EXIST'],
        'FC_KVM_SNAPSHOT_IN_USE_ERROR' => Xphp::$_lang['WEB_ERROR_FC_KVM_SNAPSHOT_IN_USE_ERROR'],
        'FC_KVM_INSTANT_MOUNT_POINT_NOT_FOUND_ERROR' => Xphp::$_lang['WEB_ERROR_FC_KVM_INSTANT_MOUNT_POINT_NOT_FOUND_ERROR'],

        'VM_VCENTER_ENV_HAS_CHANGE_ERROR' => Xphp::$_lang['WEB_ERROR_VM_VCENTER_ENV_HAS_CHANGE_ERROR'],

        'FC_KVM_HTTP_OPERATION_ERROR' => Xphp::$_lang['WEB_ERROR_FC_KVM_HTTP_OPERATION_ERROR'],

        'ZSTACK_UPLOAD_DEFAULT_RECOVERY_ISO_ERROR' => Xphp::$_lang['WEB_ERROR_ZSTACK_UPLOAD_DEFAULT_RECOVERY_ISO_ERROR'],
        'ZSTACK_NOT_FOUND_VALID_IMAGE_BACKUP_STORAGE_ERROR' => Xphp::$_lang['WEB_ERROR_ZSTACK_NOT_FOUND_VALID_IMAGE_BACKUP_STORAGE_ERROR'],

        //for object storage
        'BACKUP_COPY_GET_OBJECT_METADATA_ERROR' => Xphp::$_lang['WEB_ERROR_BACKUP_COPY_GET_OBJECT_METADATA_ERROR'],
        'KVM_OPENSTACK_DETACH_VOLUME_TO_VM_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_OPENSTACK_DETACH_VOLUME_TO_VM_ERROR'],

        'VM_REQUEST_CLUSTER_IO_VEC_SIZE_INVALID_ERROR' => Xphp::$_lang['WEB_ERROR_VM_REQUEST_CLUSTER_IO_VEC_SIZE_INVALID_ERROR'], //io vector result size is invalid error"
        'VM_ENCRYPT_CHANGED_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ENCRYPT_CHANGED_ERROR'], //vm encrypt changed error"


        ///// fusion compute kvm version all error code definition, add by sky, date: 2021-03-91
        'VM_FC_KVM_DEFAULT_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_DEFAULT_ERROR'], //FC API default error code"
        'VM_FC_KVM_PUB_10000001_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_PUB_10000001_ERROR'], //invalid request format"
        'VM_FC_KVM_PUB_10000002_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_PUB_10000002_ERROR'], //login session is out of date"
        'VM_FC_KVM_PUB_10000003_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_PUB_10000003_ERROR'], //permission denied with current user"
        'VM_FC_KVM_PUB_10000004_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_PUB_10000004_ERROR'], //failed to op database"
        'VM_FC_KVM_PUB_10000005_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_PUB_10000005_ERROR'], //VRM service is recovering, please try op later"
        'VM_FC_KVM_PUB_10000006_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_PUB_10000006_ERROR'], //system is busy, please try again later"
        'VM_FC_KVM_PUB_10000007_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_PUB_10000007_ERROR'], //there are too much tasks handled by system, please try again later"
        'VM_FC_KVM_PUB_10000008_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_PUB_10000008_ERROR'], //object is not exist"
        'VM_FC_KVM_PUB_10000009_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_PUB_10000009_ERROR'], //object is in invalid status, or operation conflict"
        'VM_FC_KVM_PUB_10000010_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_PUB_10000010_ERROR'], //operation failed"
        'VM_FC_KVM_PUB_10000011_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_PUB_10000011_ERROR'], //string length is invalid, valid range [1, 64]"
        'VM_FC_KVM_PUB_10000012_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_PUB_10000012_ERROR'], //'name' parameter is invalid, please input again"
        'VM_FC_KVM_PUB_10000013_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_PUB_10000013_ERROR'], //'limit' parameter is invalid, please input again"
        'VM_FC_KVM_PUB_10000014_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_PUB_10000014_ERROR'], //'IP' parameter is invalid, please input again"
        'VM_FC_KVM_PUB_10000015_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_PUB_10000015_ERROR'], //'cluster tag' parameter is invalid, please input again"
        'VM_FC_KVM_PUB_10000016_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_PUB_10000016_ERROR'], //'offset' parameter is invalid, please input again"
        'VM_FC_KVM_PUB_10000018_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_PUB_10000018_ERROR'], //'descripion' parameter is invalid, please input again"
        'VM_FC_KVM_PUB_10000020_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_PUB_10000020_ERROR'], //site is not exist"
        'VM_FC_KVM_PUB_10200258_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_PUB_10200258_ERROR'], //failed to communicate with another VRM "
        'VM_FC_KVM_PUB_10000022_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_PUB_10000022_ERROR'], //invalid version "
        'VM_FC_KVM_PUB_10300276_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_PUB_10300276_ERROR'], //DR VM is not support the operation"
        'VM_FC_KVM_PUB_10300275_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_PUB_10300275_ERROR'], //stub VM is not support the operation"
        'VM_FC_KVM_PUB_10540103_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_PUB_10540103_ERROR'], //virtual netcard's portgroup is invalid or not exist"
        'VM_FC_KVM_PUB_10301037_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_PUB_10301037_ERROR'], //the host is in maintenance mode, operation is not allowed"

        // FusionCompute KVM resource(site, cluster, vm) error code
        'VM_FC_KVM_RESOURCE_10300001_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10300001_ERROR'], //IDE bus is not support hotplug feature"
        'VM_FC_KVM_RESOURCE_10300002_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10300002_ERROR'], //VM's name is empty"
        'VM_FC_KVM_RESOURCE_10300003_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10300003_ERROR'], //'cpu core num' of VM is invalid "
        'VM_FC_KVM_RESOURCE_10300004_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10300004_ERROR'], //'cpu quota' of VM is invalid "
        'VM_FC_KVM_RESOURCE_10300005_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10300005_ERROR'], //'cpu limit' of VM is invalid "
        'VM_FC_KVM_RESOURCE_10300006_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10300006_ERROR'], //'memory size' of VM is invalid "
        'VM_FC_KVM_RESOURCE_10300007_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10300007_ERROR'], //'memory quota' of VM is invalid "
        'VM_FC_KVM_RESOURCE_10300008_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10300008_ERROR'], //'disk slot' of VM is invalid "
        'VM_FC_KVM_RESOURCE_10300009_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10300009_ERROR'], //'disk size' of VM is invalid "
        'VM_FC_KVM_RESOURCE_10300010_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10300010_ERROR'], //disk number is over limit"
        'VM_FC_KVM_RESOURCE_10300011_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10300011_ERROR'], //virtual network adapter's portgroup couldn't be null"
        'VM_FC_KVM_RESOURCE_10300012_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10300012_ERROR'], //virtual network adapter number is over limit"
        'VM_FC_KVM_RESOURCE_10300013_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10300013_ERROR'], //'boot mode' of VM is invalid"
        'VM_FC_KVM_RESOURCE_10300014_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10300014_ERROR'], //'Fault handling strategy' of VM is invalid"
        'VM_FC_KVM_RESOURCE_10300015_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10300015_ERROR'], //operation is not allowed for the VM"
        'VM_FC_KVM_RESOURCE_10300016_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10300016_ERROR'], //system resource is insufficient, please retry later"
        'VM_FC_KVM_RESOURCE_10300017_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10300017_ERROR'], //virtual disk is related with different type of datastore, is not allowed to create snapshot"
        'VM_FC_KVM_RESOURCE_10300018_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10300018_ERROR'], //the type of datastore is not support to create virtual disk snapshot"
        'VM_FC_KVM_RESOURCE_10300019_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10300019_ERROR'], //VM 'creation/clone/template provision/boot by network' operation is failed due to lack of resource"
        'VM_FC_KVM_RESOURCE_10300020_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10300020_ERROR'], //VM sleep failed due to lack of storage resource"
        'VM_FC_KVM_RESOURCE_10300021_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10300021_ERROR'], //target VM is template, opreation is not allowed"
        'VM_FC_KVM_RESOURCE_10300022_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10300022_ERROR'], //assigne mac, create vm or add vnic error"
        'VM_FC_KVM_RESOURCE_10300023_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10300023_ERROR'], //IP resource is insufficient, please configure more IP resource"
        'VM_FC_KVM_RESOURCE_10300024_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10300024_ERROR'], //VM couldn't be migrated to the same host"
        'VM_FC_KVM_RESOURCE_10300025_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10300025_ERROR'], //the host is busy, please retry later"
        'VM_FC_KVM_RESOURCE_10300026_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10300026_ERROR'], //guest tool is not running, please retry later"
        'VM_FC_KVM_RESOURCE_10300027_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10300027_ERROR'], //target host is not exist"
        'VM_FC_KVM_RESOURCE_10300028_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10300028_ERROR'], //target cluster is not exist"
        'VM_FC_KVM_RESOURCE_10300029_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10300029_ERROR'], //there is exsit vm in cluster, delete opreation is not allowed"
        'VM_FC_KVM_RESOURCE_10300030_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10300030_ERROR'], //there is exsit host in cluster, delete opreation is not allowed"
        'VM_FC_KVM_RESOURCE_10300031_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10300031_ERROR'], //system is exist more than 32 clusters, failed to add new cluster"
        'VM_FC_KVM_RESOURCE_10300032_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10300032_ERROR'], //some VMs running under the host that does not meet the migration conditions, host can't be empty"
        'VM_FC_KVM_RESOURCE_10300033_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10300033_ERROR'], //cdrom is already loaded on target VM, couldn't be loaded again"
        'VM_FC_KVM_RESOURCE_10300034_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10300034_ERROR'], //cdrom is not loaded on target VM, couldn't do loading operation"
        'VM_FC_KVM_RESOURCE_10300035_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10300035_ERROR'], //'URL' is invalid"
        'VM_FC_KVM_RESOURCE_10300037_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10300037_ERROR'], //volume is not attached to VM, the operation is not allowed"
        'VM_FC_KVM_RESOURCE_10300038_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10300038_ERROR'], //tools is attached to VM, couldn't be mounted again"
        'VM_FC_KVM_RESOURCE_10300039_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10300039_ERROR'], //tools is not attached to VM, the operation is not allowed"
        'VM_FC_KVM_RESOURCE_10300040_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10300040_ERROR'], //invalid parameter of VM's 'memory limit'"
        'VM_FC_KVM_RESOURCE_10300041_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10300041_ERROR'], //invalid 'OS type' of VM"
        'VM_FC_KVM_RESOURCE_10300042_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10300042_ERROR'], //invalid 'OS version' of VM"
        'VM_FC_KVM_RESOURCE_10300043_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10300043_ERROR'], //invalid 'CPU reserve' of VM"
        'VM_FC_KVM_RESOURCE_10300044_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10300044_ERROR'], //invalid 'memory reserve' of VM"
        'VM_FC_KVM_RESOURCE_10300045_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10300045_ERROR'], //mac address is already exist, create vm/vnic failed"
        'VM_FC_KVM_RESOURCE_10300046_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10300046_ERROR'], //failed to free mac address, delete vnic failed"
        'VM_FC_KVM_RESOURCE_10300047_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10300047_ERROR'], //mac resource is insufficient, create VM/add vnic failed, please configure more mac resources"
        'VM_FC_KVM_RESOURCE_10300065_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10300065_ERROR'], //host can't be move in the same cluster"
        'VM_FC_KVM_RESOURCE_10300066_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10300066_ERROR'], //value of 'mac address' is invalid"
        'VM_FC_KVM_RESOURCE_10300067_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10300067_ERROR'], //value of 'location' of VM is invalid"
        'VM_FC_KVM_RESOURCE_10300068_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10300068_ERROR'], //value of 'datastore flag' is invalid"
        'VM_FC_KVM_RESOURCE_10300069_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10300069_ERROR'], //value of 'OS type' is empty"
        'VM_FC_KVM_RESOURCE_10300070_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10300070_ERROR'], //disk slot num is in use, please use another slot num"
        'VM_FC_KVM_RESOURCE_10300071_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10300071_ERROR'], //number of host in cluster is over limit, failed to add new host to target cluster"
        'VM_FC_KVM_RESOURCE_10300072_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10300072_ERROR'], //The target host/cluster cannot meet the storage conditions for the virtual machine to run."
        'VM_FC_KVM_RESOURCE_10300073_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10300073_ERROR'], //The target host/cluster cannot meet the network conditions for the virtual machine to run."
        'VM_FC_KVM_RESOURCE_10300074_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10300074_ERROR'], //failed to attach disk to VM due to the disk is attached to another VM or the status of disk is invalid"
        'VM_FC_KVM_RESOURCE_10300075_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10300075_ERROR'], //failed to attach disk to VM due to the number of attached disk is over limit"
        'VM_FC_KVM_RESOURCE_10300076_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10300076_ERROR'], //the disk is already attached to VM, don't do it again"
        'VM_FC_KVM_RESOURCE_10300077_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10300077_ERROR'], //value length of 'group' is invalid"
        'VM_FC_KVM_RESOURCE_10300078_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10300078_ERROR'], //disk need to be attached is not exist"
        'VM_FC_KVM_RESOURCE_10300079_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10300079_ERROR'], //the disk contains volume snapshot, the operation is not allowed"

        'VM_FC_KVM_RESOURCE_10300083_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10300083_ERROR'], //target location can't find the host whose storage condition meet the VM startup"
        'VM_FC_KVM_RESOURCE_10300084_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10300084_ERROR'], //target location can't find the host whose network condition meet the VM startup"
        'VM_FC_KVM_RESOURCE_10300085_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10300085_ERROR'], //VRM internal error, please contact technical support"

        'VM_FC_KVM_RESOURCE_10300092_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10300092_ERROR'], //the VM is stopped, current operation is not allowed"
        'VM_FC_KVM_RESOURCE_10300093_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10300093_ERROR'], //the VM is suspended, current operation is not allowed"
        'VM_FC_KVM_RESOURCE_10300094_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10300094_ERROR'], //the VM is already running, boot operation is not allowed"

        'VM_FC_KVM_RESOURCE_10300096_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10300096_ERROR'], //current operation is in progress, don't do it again"
        'VM_FC_KVM_RESOURCE_10300097_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10300097_ERROR'], //VM is suspended, don't do stop operation"
        'VM_FC_KVM_RESOURCE_10300098_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10300098_ERROR'], //VM is stopped, don't do suspend operation"

        'VM_FC_KVM_RESOURCE_10300100_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10300100_ERROR'], //exist share disk attached to current VM, operation is no allowed"
        'VM_FC_KVM_RESOURCE_10300101_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10300101_ERROR'], //the VM is busy, please retry operation later"

        'VM_FC_KVM_RESOURCE_10300109_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10300109_ERROR'], //snapshot is not exist, please select other snapshot and retry"
        'VM_FC_KVM_RESOURCE_10300110_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10300110_ERROR'], //operation is not allowed due to current snapshot status"
        'VM_FC_KVM_RESOURCE_10300111_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10300111_ERROR'], //failed to create snapshot due to number of snapshot is over limit"
        'VM_FC_KVM_RESOURCE_10300112_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10300112_ERROR'], //task of creating snapshot is exist, a VM is not allowed create snapshots at the same time"

        'VM_FC_KVM_RESOURCE_10300058_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10300058_ERROR'], //the disk contains other VM's snapshot "

        'VM_FC_KVM_RESOURCE_10300113_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10300113_ERROR'], //failed to create snapshot which contains VM's memory, please retry later"
        'VM_FC_KVM_RESOURCE_10300114_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10300114_ERROR'], //failed to modify the VM to template, because the VM contains snapshot "
        'VM_FC_KVM_RESOURCE_10300115_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10300115_ERROR'], //failed to boot VM on host when the snapshot of VM is recovering"
        'VM_FC_KVM_RESOURCE_10300116_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10300116_ERROR'], //failed to create VM snapshot, because suspended VM is only support create snapshot with memory"
        'VM_FC_KVM_RESOURCE_10300118_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10300118_ERROR'], //link clone VM is not allowed current operation"
        'VM_FC_KVM_RESOURCE_10300119_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10300119_ERROR'], //failed to create VM snapshot, because stopped VM is not support create snapshot with memory"
        'VM_FC_KVM_RESOURCE_10300121_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10300121_ERROR'], //vnic's name is already exist"

        'VM_FC_KVM_RESOURCE_10300138_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10300138_ERROR'], //queried VM list is invalid"

        'VM_FC_KVM_RESOURCE_10300141_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10300141_ERROR'], //template is not support snapshot operation, please convert to VM and retry"

        'VM_FC_KVM_RESOURCE_10300153_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10300153_ERROR'], //Please check whether a snapshot is taken for the disk"

        'VM_FC_KVM_RESOURCE_10900021_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10900021_ERROR'], //CPU in system is over license limit"

        'VM_FC_KVM_RESOURCE_10310034_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10310034_ERROR'], //floppy device is attached to VM, operation is not allowed, please unattached the device"

        'VM_FC_KVM_RESOURCE_10300808_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10300808_ERROR'], //failed to bind the usb device, please shutdown the VM, then reboot the VM and retry"

        'VM_FC_KVM_RESOURCE_11400049_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_11400049_ERROR'], //VM's PCI is passthrough, memory should be 100% reserve"
        'VM_FC_KVM_RESOURCE_11400050_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_11400050_ERROR'], //VM is bound PCI device, memory limit configuration is not allowed"
        'VM_FC_KVM_RESOURCE_11400052_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_11400052_ERROR'], //PCI passthrough VM, must be bound with host"
        'VM_FC_KVM_RESOURCE_11400051_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_11400051_ERROR'], //VM can not be bound with VIRTIO and IDE disks at the same time"

        'VM_FC_KVM_RESOURCE_10300175_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10300175_ERROR'], //VM which be bound with USB device is not support current operation"
        'VM_FC_KVM_RESOURCE_10300843_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10300843_ERROR'], //the VM is not support the mounted device type"

        'VM_FC_KVM_RESOURCE_10300987_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10300987_ERROR'], //the VM whose boot order is not specified is not support configure the boot order options"
        'VM_FC_KVM_RESOURCE_10300988_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10300988_ERROR'], //if VM's boot order is specified, user should configure the boot order of the VM"
        'VM_FC_KVM_RESOURCE_10300828_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10300828_ERROR'], //There is no host whose affinity or anti affinity conditions meet the virtual machine startup in the specified location."
        'VM_FC_KVM_RESOURCE_10300839_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10300839_ERROR'], //the VM is not support change the resource group due to it's status, please retry after VM reboot"

        'VM_FC_KVM_RESOURCE_10300953_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10300953_ERROR'], //normal cluster is not support to run huge page VM"
        'VM_FC_KVM_RESOURCE_10300956_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10300956_ERROR'], //couldn't not configure huge page for the VM, because the VM isn't in cluster "
        'VM_FC_KVM_RESOURCE_10300957_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10300957_ERROR'], //huge page VM is not support to configure memory boot mode"
        'VM_FC_KVM_RESOURCE_10300962_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10300962_ERROR'], //normal cluster is not support to run NUMA VM"
        'VM_FC_KVM_RESOURCE_10300963_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10300963_ERROR'], //could not configure NUMA for the VM, because the VM isn't in cluster "
        'VM_FC_KVM_RESOURCE_10300964_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10300964_ERROR'], //could not change the system volume when the VM is running"
        'VM_FC_KVM_RESOURCE_10300965_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10300965_ERROR'], //should be configured with 100% CPU reserve when the VM is bound with CPU"
        'VM_FC_KVM_RESOURCE_10300966_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10300966_ERROR'], //could not configure CPU bound and CPU range at the same time"

        'VM_FC_KVM_RESOURCE_10300970_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10300970_ERROR'], //bound CPU is not support hotplug feature"
        'VM_FC_KVM_RESOURCE_10300971_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10300971_ERROR'], //normal cluster is not support to run NUMA/realtime VM"

        'VM_FC_KVM_RESOURCE_10300978_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10300978_ERROR'], //vnic is already in task"

        'VM_FC_KVM_RESOURCE_10300980_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10300980_ERROR'], //NUMA resource is insufficient, please check VM 'CPU cores, reserve, limit and mhz'"

        'VM_FC_KVM_RESOURCE_10301107_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10301107_ERROR'], //DPI VM type is invalid"
        'VM_FC_KVM_RESOURCE_10301111_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10301111_ERROR'], //DPI VM is only support normal vswitch"

        'VM_FC_KVM_RESOURCE_10301009_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10301009_ERROR'], //the VM is not bound with host, please cleanup the configuration of CPU bound"
        'VM_FC_KVM_RESOURCE_10301011_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10301011_ERROR'], //the VM is not specified the NUMA bound bitmap"
        'VM_FC_KVM_RESOURCE_10301015_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10301015_ERROR'], //the VM is already bound with CPU, please unbound and retry"
        'VM_FC_KVM_RESOURCE_10301016_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10301016_ERROR'], //the VM's hotplug vcpu is insufficient"

        'VM_FC_KVM_RESOURCE_10301025_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10301025_ERROR'], //could not take snapshot for VM which contains cdrom, please unload and retry"
        'VM_FC_KVM_RESOURCE_10301026_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10301026_ERROR'], //failed to boot VM due to internal reboot or other exception"

        'VM_FC_KVM_RESOURCE_10301042_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10301042_ERROR'], //failed to set auto upgrade for VM tools due to internal error"
        'VM_FC_KVM_RESOURCE_10301043_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10301043_ERROR'], //clone VM or template can not be modify firmware when provisioning"
        'VM_FC_KVM_RESOURCE_10301044_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10301044_ERROR'], //sriov VM is not support memory hotplug feature"
        'VM_FC_KVM_RESOURCE_10300440_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10300440_ERROR'], //libvirt service exception in current host, please check the host"
        'VM_FC_KVM_RESOURCE_10300441_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10300441_ERROR'], //could not modify boot firmware online"
        'VM_FC_KVM_RESOURCE_10300442_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10300442_ERROR'], //failed to define VM"
        'VM_FC_KVM_RESOURCE_10300443_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10300443_ERROR'], //VM internal erorr or unknown exception occured, please retry or contact technical support"
        'VM_FC_KVM_RESOURCE_10300444_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10300444_ERROR'], //SRIOV nic is not support selected port group"
        'VM_FC_KVM_RESOURCE_10300445_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10300445_ERROR'], //failed to operate the device, please check log in guest or contact technical support"
        'VM_FC_KVM_RESOURCE_10300446_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10300446_ERROR'], //Linux VM's IDE system volume, when configured memory "
        'VM_FC_KVM_RESOURCE_10300447_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10300447_ERROR'], //the VM memory hot plug exceeds maximum limit or hot plug size exceeds maximum memory limit"
        'VM_FC_KVM_RESOURCE_12000016_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_12000016_ERROR'], //target host's memory is insufficient, please adjust and retry"
        'VM_FC_KVM_RESOURCE_12000017_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_12000017_ERROR'], //target host's memory multiplex rate is too high, please adjust and retry"
        'VM_FC_KVM_RESOURCE_12000018_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_12000018_ERROR'], //the host's memory multiplex rate is too high, please adjust and retry"
        'VM_FC_KVM_RESOURCE_12000019_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_12000019_ERROR'], //exist host's memory multiplex rate is over 100% in the cluster, please adjust and retry"
        'VM_FC_KVM_RESOURCE_12000020_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_12000020_ERROR'], //number of VM's vcpu is over the limit of host or cluster, please adjust and retry"
        'VM_FC_KVM_RESOURCE_12000022_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_12000022_ERROR'], //cluster HA resource's CPU reserve is insufficient, please adjust and retry"
        'VM_FC_KVM_RESOURCE_12000023_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_12000023_ERROR'], //cluster HA resource's memory reserve is insufficient, please adjust and retry"

        'VM_FC_KVM_RESOURCE_12000027_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_12000027_ERROR'], //exist uncontrolled disk in the VM, please adjust and retry"

        'VM_FC_KVM_RESOURCE_10321005_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10321005_ERROR'], //current guest operating system is not support hotplug disk"
        'VM_FC_KVM_RESOURCE_10321008_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10321008_ERROR'], //current guest operating system is not support hotplug netcard"
        'VM_FC_KVM_RESOURCE_10321009_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10321009_ERROR'], //current guest operating system is not support hotplug SRIOV netcard"
        'VM_FC_KVM_RESOURCE_10321010_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10321010_ERROR'], //current guest operating system is not support unplug netcard online"
        'VM_FC_KVM_RESOURCE_10321016_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10321016_ERROR'], //bound PCI device is not support current operation"

        'VM_FC_KVM_RESOURCE_10321020_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10321020_ERROR'], //linked clone VM's number of disk is over limit"
        'VM_FC_KVM_RESOURCE_10321021_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10321021_ERROR'], //linked clone VM's ID disk's slot in use, failed to do creation"

        'VM_FC_KVM_RESOURCE_10321028_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10321028_ERROR'], //guest operating system is not support snapshot creation"

        'VM_FC_KVM_RESOURCE_10321039_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10321039_ERROR'], //Linux guest is not support VGA video device"

        'VM_FC_KVM_RESOURCE_10321046_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10321046_ERROR'], //VM's cpu cores is over the limit in current topology, please adjust"

        'VM_FC_KVM_RESOURCE_10321050_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10321050_ERROR'], //operation failed due to host exception"
        'VM_FC_KVM_RESOURCE_10321051_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10321051_ERROR'], //operation failed, because memory multiplex ratio is too high"
        'VM_FC_KVM_RESOURCE_10321052_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10321052_ERROR'], //database operation exception, please try again later"
        'VM_FC_KVM_RESOURCE_10321053_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10321053_ERROR'], //failed to send message to host, please try again later"
        'VM_FC_KVM_RESOURCE_10321054_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10321054_ERROR'], //failed to recieve message from host, please try again later"
        'VM_FC_KVM_RESOURCE_10321055_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10321055_ERROR'], //guest tools is not installed or guest internal error"

        'VM_FC_KVM_RESOURCE_10421008_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10421008_ERROR'], //'SATA' virtual disk is not support current operation"
        'VM_FC_KVM_RESOURCE_10321061_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10321061_ERROR'], //cd or tools is mounted to VM, please unmount and retry"
        'VM_FC_KVM_RESOURCE_10321062_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10321062_ERROR'], //VM is not support anti-virus feature"

        'VM_FC_KVM_RESOURCE_10321094_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10321094_ERROR'], //exist different format disks in the VM, opeation is not allowed"

        'VM_FC_KVM_RESOURCE_10321120_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10321120_ERROR'], //'BIOS' boot mode is not allowed in ARM architecture"

        'VM_FC_KVM_RESOURCE_10321123_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10321123_ERROR'], //architecture of VM is incompatible with the compute resource"
        'VM_FC_KVM_RESOURCE_10321124_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10321124_ERROR'], //failed to query the architecture of host, please check the network and retry"

        'VM_FC_KVM_RESOURCE_10321125_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10321125_ERROR'], //ARM VM is not support IDE disk"
        'VM_FC_KVM_RESOURCE_10321127_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_RESOURCE_10321127_ERROR'], //failed to delete snapshot due to VM's status change, please retry after VM become normal"

        // FusionCompute KVM node management error code"
        'VM_FC_KVM_NM_10200101_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_NM_10200101_ERROR'], //input parameter is null"
        'VM_FC_KVM_NM_10200102_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_NM_10200102_ERROR'], //host name is null"
        'VM_FC_KVM_NM_10200103_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_NM_10200103_ERROR'], //host name is null"
        'VM_FC_KVM_NM_10200104_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_NM_10200104_ERROR'], //host name is already exist"
        'VM_FC_KVM_NM_10200105_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_NM_10200105_ERROR'], //length of host name is over limit"
        'VM_FC_KVM_NM_10200106_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_NM_10200106_ERROR'], //host IP is null"
        'VM_FC_KVM_NM_10200107_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_NM_10200107_ERROR'], //host IP is invalid"
        'VM_FC_KVM_NM_10200108_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_NM_10200108_ERROR'], //host IP is already exist "
        'VM_FC_KVM_NM_10200110_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_NM_10200110_ERROR'], //parameter of 'BMC IP' is invlaid"
        'VM_FC_KVM_NM_10200112_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_NM_10200112_ERROR'], //length of BMC user name is over limit"
        'VM_FC_KVM_NM_10200113_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_NM_10200113_ERROR'], //BMC user name is invalid"
        'VM_FC_KVM_NM_10200114_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_NM_10200114_ERROR'], //BMC password is invalid"
        'VM_FC_KVM_NM_10200115_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_NM_10200115_ERROR'], //id of cluster is invalid"
        'VM_FC_KVM_NM_10200116_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_NM_10200116_ERROR'], //cluster is not exist"
        'VM_FC_KVM_NM_10200117_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_NM_10200117_ERROR'], //format of ntpip is invalid"
        'VM_FC_KVM_NM_10200118_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_NM_10200118_ERROR'], //format of logip is invalid"
        'VM_FC_KVM_NM_10200119_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_NM_10200119_ERROR'], //format of kboxip is invalid"
        'VM_FC_KVM_NM_10200120_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_NM_10200120_ERROR'], //the operation target host is not exist"
        'VM_FC_KVM_NM_10200121_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_NM_10200121_ERROR'], //ID of host is invalid"

        'VM_FC_KVM_NM_10200124_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_NM_10200124_ERROR'], //operation failed due to the host status"
        'VM_FC_KVM_NM_10200130_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_NM_10200130_ERROR'], //failed to add new vm, because the host is in maintenance"

        // FusionCompute KVM user/role error code"
        'VM_FC_KVM_NM_10100107_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_NM_10100107_ERROR'], //current user only can modify its password"
        'VM_FC_KVM_NM_10100108_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_NM_10100108_ERROR'], //to ensure the security of account password, please change the password"
        'VM_FC_KVM_NM_10100109_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_NM_10100109_ERROR'], //the password is already out of date, please change the password"
        'VM_FC_KVM_NM_10100112_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_NM_10100112_ERROR'], //login failed, please login again"
        'VM_FC_KVM_NM_10100113_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_NM_10100113_ERROR'], //login failed too more, the account is locked for a few minutes"

        'VM_FC_KVM_NM_10100306_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_NM_10100306_ERROR'], //role of user is not exist"
        'VM_FC_KVM_NM_10100307_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_NM_10100307_ERROR'], //the user is not exist"

        // FusionCompute KVM FM error"
        'VM_FC_KVM_FM_11100011_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_FM_11100011_ERROR'], //query realtime warnning message failed"
        'VM_FC_KVM_FM_11100013_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_FM_11100013_ERROR'], //query history warnning message failed"
        'VM_FC_KVM_FM_11100015_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_FM_11100015_ERROR'], //query task failed"

        // FusionCompute task error"
        'VM_FC_KVM_TASK_10800001_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_TASK_10800001_ERROR'], //this type of task can not be cancel"
        'VM_FC_KVM_TASK_10800002_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_TASK_10800002_ERROR'], //the task is already finished, cann't be cancel"

        'VM_FC_KVM_TASK_BE_CANCELLING_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_TASK_BE_CANCELLING_ERROR'], //the operation task in FusionCompute is cancelled"
        'VM_FC_KVM_TASK_BE_FAILED_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_TASK_BE_FAILED_ERROR'], //the operation task in FusionCompute failed"

        'VM_FC_KVM_FAILED_TO_LOCATE_HOST_IN_SITE_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_FAILED_TO_LOCATE_HOST_IN_SITE_ERROR'], //unable to locate the host in site error"
        'VM_FC_KVM_FAILED_TO_LOCATE_CLUSTER_IN_SITE_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_FAILED_TO_LOCATE_CLUSTER_IN_SITE_ERROR'], //unable to locate the cluster in site error"
        'VM_FC_KVM_SITE_LIST_EMPTY_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_SITE_LIST_EMPTY_ERROR'], //site list is empty, may be due to the"
        'VM_FC_KVM_URN_INVALID_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_URN_INVALID_ERROR'], //urn is invalid"

        'VM_FC_KVM_TASK_ENTITY_URN_NULL_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_TASK_ENTITY_URN_NULL_ERROR'], //FC internal task's entity urn is null"
        'VM_FC_KVM_URI_INVALID_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_URI_INVALID_ERROR'], //uri string is invalid"
        'VM_FC_KVM_INVALID_IR_DATASTORE_INFO' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_INVALID_IR_DATASTORE_INFO'], //"Invalid instant recovery datastore info"
        'VM_FC_KVM_INVALID_CBT_BITMAP_BLOCK_LEN' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_INVALID_CBT_BITMAP_BLOCK_LEN'], //invalid FC cbt bitmap block length"
        'VM_FC_KVM_INVALID_CBT_BITMAP_LENGTH' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_INVALID_CBT_BITMAP_LENGTH'], //invlaid FC cbt bitmap string length"
        'VM_FC_KVM_PREPARE_RESOURCE_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_PREPARE_RESOURCE_ERROR'], //prepare backup or recovery resource error"
        'VM_FC_KVM_DELETE_BACKUP_RESOURCE_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_DELETE_BACKUP_RESOURCE_ERROR'], //delete backup or recovery resource error"
        'VM_FC_KVM_BITMAP_OFFSET_IS_NOT_ALIGN' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_BITMAP_OFFSET_IS_NOT_ALIGN'], //request bitmap offset is not align erorr"

        'VM_FC_KVM_NBD_INVALID_OP_CODE_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_NBD_INVALID_OP_CODE_ERROR'], //invalid request op code"
        'VM_FC_KVM_NBD_FILE_NOT_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_NBD_FILE_NOT_EXIST_ERROR'], //remote disk file is not exist error"
        'VM_FC_KVM_NBD_INVALID_TOKEN_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_NBD_INVALID_TOKEN_ERROR'], //invalid request token"
        'VM_FC_KVM_NBD_INVALID_OPEN_FLAGS_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_NBD_INVALID_OPEN_FLAGS_ERROR'], //invalid request open flag"
        'VM_FC_KVM_NBD_FILE_HANDLE_NOT_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_NBD_FILE_HANDLE_NOT_EXIST_ERROR'], //remote file handle is not exist error"
        'VM_FC_KVM_NBD_START_BLOCK_OUT_OF_RANGE_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_NBD_START_BLOCK_OUT_OF_RANGE_ERROR'], //request start block is invalid"
        'VM_FC_KVM_NBD_READ_SIZE_OUT_OF_RANGE' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_NBD_READ_SIZE_OUT_OF_RANGE'], //request read size is invalid"
        'VM_FC_KVM_NBD_READ_FAILED_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_NBD_READ_FAILED_ERROR'], //request to read file error"
        'VM_FC_KVM_NBD_WRITE_SIZE_OUT_OF_RANGE' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_NBD_WRITE_SIZE_OUT_OF_RANGE'], //request write size is invalid"
        'VM_FC_KVM_NBD_WRITE_FAILED_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_NBD_WRITE_FAILED_ERROR'], //request to write file error"
        'VM_FC_KVM_NBD_UNKNWON_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_NBD_UNKNWON_ERROR'], //unknown remote fc server error"
        'VM_FC_KVM_INC_MODE_AND_TRANSPORT_MODE_NOT_MATCH_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_INC_MODE_AND_TRANSPORT_MODE_NOT_MATCH_ERROR'], //incremental mode and transport mode is not match error, API LAN transport mode only support CBT incremantal mode"
        'VM_FC_KVM_GET_BACKUP_SERVER_IP_LIST_IS_EMPTY' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_GET_BACKUP_SERVER_IP_LIST_IS_EMPTY'], //backup server ip list is empty"
        'VM_DISK_LIST_IS_EMPTY_ERROR' => Xphp::$_lang['WEB_ERROR_VM_DISK_LIST_IS_EMPTY_ERROR'], //disk list of task is empty, exclude all disk or vm is not include disk is not support the"

        'VM_FC_KVM_IS_ROOT_SNAPSHOT_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_IS_ROOT_SNAPSHOT_ERROR'], //snapshot is root error

        'VM_OVIRT_CBT_BACKUP_ID_IS_NULL_ERROR' => Xphp::$_lang['WEB_ERROR_VM_OVIRT_CBT_BACKUP_ID_IS_NULL_ERROR'], //create cbt backup success, but response message is invalid
        'VM_OVIRT_CBT_BACKUP_CREATE_FAILED' => Xphp::$_lang['WEB_ERROR_VM_OVIRT_CBT_BACKUP_CREATE_FAILED'], //failed to create cbt backup
        'VM_OVIRT_CBT_BACKUP_CREATE_TIMEOUT' => Xphp::$_lang['WEB_ERROR_VM_OVIRT_CBT_BACKUP_CREATE_TIMEOUT'], //create cbt backup timeout
        'VM_OVIRT_GET_BACKUP_INFO_ERROR' => Xphp::$_lang['WEB_ERROR_VM_OVIRT_GET_BACKUP_INFO_ERROR'], //failed to get cbt backup info
        'VM_OVIRT_TRANSFER_ID_IS_NULL_ERROR' => Xphp::$_lang['WEB_ERROR_VM_OVIRT_TRANSFER_ID_IS_NULL_ERROR'], //create imagetransfer success, but response transfer id is invalid
        'VM_OVIRT_TRANSFER_URL_IS_NULL_ERROR' => Xphp::$_lang['WEB_ERROR_VM_OVIRT_TRANSFER_URL_IS_NULL_ERROR'], //create imagetransfer success, but response transfer url is invalid

        'VM_FC_MACHINE_HAS_INDEP_DISK_ERORR' => Xphp::$_lang['WEB_ERROR_VM_FC_MACHINE_HAS_INDEP_DISK_ERORR'], //backup vm which contains independent disk is not support
        'VM_FC_MACHINE_HAS_SHARABLE_DISK_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_MACHINE_HAS_SHARABLE_DISK_ERROR'], //backup vm which contains sharable disk is not support

        'BACKUP_COPY_NODE_LOCATION_ERROR' => Xphp::$_lang['WEB_ERROR_BACKUP_COPY_NODE_LOCATION_ERROR'],
        'VM_FC_KVM_STORAGE_10410003_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_STORAGE_10410003_ERROR'],
        'VM_OVIRT_ENGINE_VERSION_IS_NOT_SUPPORT_CBT' => Xphp::$_lang['WEB_ERROR_VM_OVIRT_ENGINE_VERSION_IS_NOT_SUPPORT_CBT'],
        'VM_FC_KVM_VM_10300797_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_VM_10300797_ERROR'],								//This operation is not allowed because the VM has disks whose bus type is IDE

        'VM_OVIRT_CREATE_TRANSFER_ERROR' => Xphp::$_lang['WEB_ERROR_VM_OVIRT_CREATE_TRANSFER_ERROR'],                           //create imagetransfer error, detect invalid phase(黄总跟你说中文翻译)
        'VM_ICS_KVM_INVALID_CBT_BITMAP_BLOCK_LEN' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_INVALID_CBT_BITMAP_BLOCK_LEN'],					//invalid ICS KVM cbt bitmap block length（无效的cbt位图块长度）
        'VM_ICS_KVM_BITMAP_OFFSET_IS_NOT_ALIGN' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_BITMAP_OFFSET_IS_NOT_ALIGN'],					//request bitmap offset is not align erorr （请求的bitmap偏移没有对齐）
        'VM_ICS_KVM_CBT_DIFF_BACKUP_MODEL_CREATE_SNAPSHOT_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_CBT_DIFF_BACKUP_MODEL_CREATE_SNAPSHOT_ERROR'],     //ICS KVM not support cbt diff backup（cbt不支持差异备份）
        'VM_ICS_KVM_V2_QCOW_DISK_NOT_SUPPORT_HIGH_SPEED_MODEL' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_V2_QCOW_DISK_NOT_SUPPORT_HIGH_SPEED_MODEL'],  //v2 qcow disk not support high speed model(V2 QCOW磁盘虚拟机不支持增量高速模式)
        'VM_ICS_KVM_SYNC_CACHE_TO_PHYSICAL_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_SYNC_CACHE_TO_PHYSICAL_ERROR'],	// sync cache to physical error 刷新磁盘缓存失败
        'VM_CONVERT_LIC_NUM_EXHUAST' => Xphp::$_lang['WEB_ERROR_VM_CONVERT_LIC_NUM_EXHUAST'],                        //convert license is exhaust

        'VM_ICS_KVM_TASK_20027_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_TASK_20027_ERROR'],
        'VM_ICS_KVM_TASK_102002_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_TASK_102002_ERROR'],
        'VM_ICS_KVM_TASK_102033_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_TASK_102033_ERROR'],
        'VM_ICS_KVM_TASK_201014_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_TASK_201014_ERROR'],
        'VM_ICS_KVM_TASK_201040_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_TASK_201040_ERROR'],
        'VM_ICS_KVM_TASK_201049_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_TASK_201049_ERROR'],
        'VM_ICS_KVM_TASK_201077_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_TASK_201077_ERROR'],
        'VM_ICS_KVM_TASK_201096_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_TASK_201096_ERROR'],
        'VM_ICS_KVM_TASK_210033_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_TASK_210033_ERROR'],
        'VM_ICS_KVM_TASK_210508_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_TASK_210508_ERROR'],
        'VM_ICS_KVM_TASK_210520_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_TASK_210520_ERROR'],
        'VM_ICS_KVM_TASK_210559_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_TASK_210559_ERROR'],
        'VM_ICS_KVM_TASK_220021_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_TASK_220021_ERROR'],
        'VM_ICS_KVM_TASK_220048_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_TASK_220048_ERROR'],
        'VM_ICS_KVM_TASK_220084_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_TASK_220084_ERROR'],
        'VM_ICS_KVM_TASK_220266_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_TASK_220266_ERROR'],
        'VM_ICS_KVM_TASK_220271_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_TASK_220271_ERROR'],
        'VM_ICS_KVM_TASK_300301_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_TASK_300301_ERROR'],
        'VM_ICS_KVM_TASK_300306_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_TASK_300306_ERROR'],

        'VM_ICS_KVM_PUB_220008_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_PUB_220008_ERROR'],
        'VM_ICS_KVM_PUB_220009_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_PUB_220009_ERROR'],
        'VM_ICS_KVM_PUB_220010_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_PUB_220010_ERROR'],
        'VM_ICS_KVM_PUB_220012_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_PUB_220012_ERROR'],
        'VM_ICS_KVM_PUB_220039_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_PUB_220039_ERROR'],
        'VM_ICS_KVM_PUB_220044_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_PUB_220044_ERROR'],
        'VM_ICS_KVM_PUB_220047_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_PUB_220047_ERROR'],
        'VM_ICS_KVM_PUB_220049_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_PUB_220049_ERROR'],
        'VM_ICS_KVM_PUB_220055_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_PUB_220055_ERROR'],
        'VM_ICS_KVM_PUB_220122_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_PUB_220122_ERROR'],
        'VM_ICS_KVM_PUB_220158_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_PUB_220158_ERROR'],
        'VM_ICS_KVM_PUB_220228_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_PUB_220228_ERROR'],
        'VM_ICS_KVM_RESOURCE_220001_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_RESOURCE_220001_ERROR'],
        'VM_ICS_KVM_RESOURCE_220002_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_RESOURCE_220002_ERROR'],
        'VM_ICS_KVM_RESOURCE_220003_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_RESOURCE_220003_ERROR'],
        'VM_ICS_KVM_RESOURCE_220004_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_RESOURCE_220004_ERROR'],
        'VM_ICS_KVM_RESOURCE_220006_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_RESOURCE_220006_ERROR'],
        'VM_ICS_KVM_RESOURCE_220011_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_RESOURCE_220011_ERROR'],
        'VM_ICS_KVM_RESOURCE_220013_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_RESOURCE_220013_ERROR'],
        'VM_ICS_KVM_RESOURCE_220017_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_RESOURCE_220017_ERROR'],
        'VM_ICS_KVM_RESOURCE_220030_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_RESOURCE_220030_ERROR'],
        'VM_ICS_KVM_RESOURCE_220032_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_RESOURCE_220032_ERROR'],
        'VM_ICS_KVM_RESOURCE_220033_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_RESOURCE_220033_ERROR'],
        'VM_ICS_KVM_RESOURCE_220034_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_RESOURCE_220034_ERROR'],
        'VM_ICS_KVM_RESOURCE_220035_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_RESOURCE_220035_ERROR'],
        'VM_ICS_KVM_RESOURCE_220045_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_RESOURCE_220045_ERROR'],
        'VM_ICS_KVM_RESOURCE_220046_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_RESOURCE_220046_ERROR'],
        'VM_ICS_KVM_RESOURCE_220048_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_RESOURCE_220048_ERROR'],
        'VM_ICS_KVM_RESOURCE_220051_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_RESOURCE_220051_ERROR'],
        'VM_ICS_KVM_RESOURCE_220053_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_RESOURCE_220053_ERROR'],
        'VM_ICS_KVM_RESOURCE_220056_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_RESOURCE_220056_ERROR'],
        'VM_ICS_KVM_RESOURCE_220057_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_RESOURCE_220057_ERROR'],
        'VM_ICS_KVM_RESOURCE_220059_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_RESOURCE_220059_ERROR'],
        'VM_ICS_KVM_RESOURCE_220062_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_RESOURCE_220062_ERROR'],
        'VM_ICS_KVM_RESOURCE_220063_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_RESOURCE_220063_ERROR'],
        'VM_ICS_KVM_RESOURCE_220065_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_RESOURCE_220065_ERROR'],
        'VM_ICS_KVM_RESOURCE_220067_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_RESOURCE_220067_ERROR'],
        'VM_ICS_KVM_RESOURCE_220069_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_RESOURCE_220069_ERROR'],
        'VM_ICS_KVM_RESOURCE_220073_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_RESOURCE_220073_ERROR'],
        'VM_ICS_KVM_RESOURCE_220074_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_RESOURCE_220074_ERROR'],
        'VM_ICS_KVM_RESOURCE_220077_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_RESOURCE_220077_ERROR'],
        'VM_ICS_KVM_RESOURCE_220078_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_RESOURCE_220078_ERROR'],
        'VM_ICS_KVM_RESOURCE_220080_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_RESOURCE_220080_ERROR'],
        'VM_ICS_KVM_RESOURCE_220082_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_RESOURCE_220082_ERROR'],
        'VM_ICS_KVM_RESOURCE_220084_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_RESOURCE_220084_ERROR'],
        'VM_ICS_KVM_RESOURCE_220087_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_RESOURCE_220087_ERROR'],
        'VM_ICS_KVM_RESOURCE_220089_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_RESOURCE_220089_ERROR'],
        'VM_ICS_KVM_RESOURCE_220090_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_RESOURCE_220090_ERROR'],
        'VM_ICS_KVM_RESOURCE_220091_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_RESOURCE_220091_ERROR'],
        'VM_ICS_KVM_RESOURCE_220095_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_RESOURCE_220095_ERROR'],
        'VM_ICS_KVM_RESOURCE_220096_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_RESOURCE_220096_ERROR'],
        'VM_ICS_KVM_RESOURCE_220098_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_RESOURCE_220098_ERROR'],
        'VM_ICS_KVM_RESOURCE_220100_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_RESOURCE_220100_ERROR'],
        'VM_ICS_KVM_RESOURCE_220104_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_RESOURCE_220104_ERROR'],
        'VM_ICS_KVM_RESOURCE_220106_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_RESOURCE_220106_ERROR'],
        'VM_ICS_KVM_RESOURCE_220107_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_RESOURCE_220107_ERROR'],
        'VM_ICS_KVM_RESOURCE_220108_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_RESOURCE_220108_ERROR'],
        'VM_ICS_KVM_RESOURCE_220109_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_RESOURCE_220109_ERROR'],
        'VM_ICS_KVM_RESOURCE_220110_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_RESOURCE_220110_ERROR'],
        'VM_ICS_KVM_RESOURCE_220111_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_RESOURCE_220111_ERROR'],
        'VM_ICS_KVM_RESOURCE_220112_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_RESOURCE_220112_ERROR'],
        'VM_ICS_KVM_RESOURCE_220113_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_RESOURCE_220113_ERROR'],
        'VM_ICS_KVM_RESOURCE_220117_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_RESOURCE_220117_ERROR'],
        'VM_ICS_KVM_RESOURCE_220118_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_RESOURCE_220118_ERROR'],
        'VM_ICS_KVM_RESOURCE_220123_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_RESOURCE_220123_ERROR'],
        'VM_ICS_KVM_RESOURCE_220124_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_RESOURCE_220124_ERROR'],
        'VM_ICS_KVM_RESOURCE_220125_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_RESOURCE_220125_ERROR'],
        'VM_ICS_KVM_RESOURCE_220127_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_RESOURCE_220127_ERROR'],
        'VM_ICS_KVM_RESOURCE_220128_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_RESOURCE_220128_ERROR'],
        'VM_ICS_KVM_RESOURCE_220130_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_RESOURCE_220130_ERROR'],
        'VM_ICS_KVM_RESOURCE_220133_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_RESOURCE_220133_ERROR'],
        'VM_ICS_KVM_RESOURCE_220134_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_RESOURCE_220134_ERROR'],
        'VM_ICS_KVM_RESOURCE_220135_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_RESOURCE_220135_ERROR'],
        'VM_ICS_KVM_RESOURCE_220136_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_RESOURCE_220136_ERROR'],
        'VM_ICS_KVM_RESOURCE_220139_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_RESOURCE_220139_ERROR'],
        'VM_ICS_KVM_RESOURCE_220141_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_RESOURCE_220141_ERROR'],
        'VM_ICS_KVM_RESOURCE_220142_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_RESOURCE_220142_ERROR'],
        'VM_ICS_KVM_RESOURCE_220143_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_RESOURCE_220143_ERROR'],
        'VM_ICS_KVM_RESOURCE_220144_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_RESOURCE_220144_ERROR'],
        'VM_ICS_KVM_RESOURCE_220145_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_RESOURCE_220145_ERROR'],
        'VM_ICS_KVM_RESOURCE_220146_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_RESOURCE_220146_ERROR'],
        'VM_ICS_KVM_RESOURCE_220151_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_RESOURCE_220151_ERROR'],
        'VM_ICS_KVM_RESOURCE_220152_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_RESOURCE_220152_ERROR'],
        'VM_ICS_KVM_RESOURCE_220159_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_RESOURCE_220159_ERROR'],
        'VM_ICS_KVM_RESOURCE_220160_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_RESOURCE_220160_ERROR'],
        'VM_ICS_KVM_RESOURCE_220161_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_RESOURCE_220161_ERROR'],
        'VM_ICS_KVM_RESOURCE_220162_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_RESOURCE_220162_ERROR'],
        'VM_ICS_KVM_RESOURCE_220163_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_RESOURCE_220163_ERROR'],
        'VM_ICS_KVM_RESOURCE_220164_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_RESOURCE_220164_ERROR'],
        'VM_ICS_KVM_RESOURCE_220165_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_RESOURCE_220165_ERROR'],
        'VM_ICS_KVM_RESOURCE_220166_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_RESOURCE_220166_ERROR'],
        'VM_ICS_KVM_RESOURCE_220167_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_RESOURCE_220167_ERROR'],
        'VM_ICS_KVM_RESOURCE_220168_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_RESOURCE_220168_ERROR'],
        'VM_ICS_KVM_RESOURCE_220175_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_RESOURCE_220175_ERROR'],
        'VM_ICS_KVM_RESOURCE_220190_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_RESOURCE_220190_ERROR'],
        'VM_ICS_KVM_RESOURCE_220191_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_RESOURCE_220191_ERROR'],
        'VM_ICS_KVM_RESOURCE_220195_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_RESOURCE_220195_ERROR'],
        'VM_ICS_KVM_RESOURCE_220197_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_RESOURCE_220197_ERROR'],
        'VM_ICS_KVM_RESOURCE_220198_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_RESOURCE_220198_ERROR'],
        'VM_ICS_KVM_RESOURCE_220199_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_RESOURCE_220199_ERROR'],
        'VM_ICS_KVM_RESOURCE_220200_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_RESOURCE_220200_ERROR'],
        'VM_ICS_KVM_RESOURCE_220201_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_RESOURCE_220201_ERROR'],
        'VM_ICS_KVM_RESOURCE_220202_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_RESOURCE_220202_ERROR'],
        'VM_ICS_KVM_RESOURCE_220203_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_RESOURCE_220203_ERROR'],
        'VM_ICS_KVM_RESOURCE_220205_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_RESOURCE_220205_ERROR'],
        'VM_ICS_KVM_RESOURCE_220206_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_RESOURCE_220206_ERROR'],
        'VM_ICS_KVM_RESOURCE_220207_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_RESOURCE_220207_ERROR'],
        'VM_ICS_KVM_RESOURCE_220208_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_RESOURCE_220208_ERROR'],
        'VM_ICS_KVM_RESOURCE_220209_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_RESOURCE_220209_ERROR'],
        'VM_ICS_KVM_RESOURCE_220210_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_RESOURCE_220210_ERROR'],
        'VM_ICS_KVM_RESOURCE_220211_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_RESOURCE_220211_ERROR'],
        'VM_ICS_KVM_RESOURCE_220216_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_RESOURCE_220216_ERROR'],
        'VM_ICS_KVM_RESOURCE_220221_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_RESOURCE_220221_ERROR'],
        'VM_ICS_KVM_RESOURCE_220222_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_RESOURCE_220222_ERROR'],
        'VM_ICS_KVM_RESOURCE_220227_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_RESOURCE_220227_ERROR'],
        'VM_ICS_KVM_RESOURCE_220229_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_RESOURCE_220229_ERROR'],
        'VM_ICS_KVM_RESOURCE_220233_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_RESOURCE_220233_ERROR'],
        'VM_ICS_KVM_RESOURCE_220235_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_RESOURCE_220235_ERROR'],
        'VM_ICS_KVM_NM_220005_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_NM_220005_ERROR'],
        'VM_ICS_KVM_NM_220007_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_NM_220007_ERROR'],
        'VM_ICS_KVM_NM_220015_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_NM_220015_ERROR'],
        'VM_ICS_KVM_NM_220016_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_NM_220016_ERROR'],
        'VM_ICS_KVM_NM_220018_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_NM_220018_ERROR'],
        'VM_ICS_KVM_NM_220019_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_NM_220019_ERROR'],
        'VM_ICS_KVM_NM_220021_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_NM_220021_ERROR'],
        'VM_ICS_KVM_NM_220024_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_NM_220024_ERROR'],
        'VM_ICS_KVM_NM_220025_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_NM_220025_ERROR'],
        'VM_ICS_KVM_NM_220026_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_NM_220026_ERROR'],
        'VM_ICS_KVM_NM_220029_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_NM_220029_ERROR'],
        'VM_ICS_KVM_NM_220031_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_NM_220031_ERROR'],
        'VM_ICS_KVM_NM_299999_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_NM_299999_ERROR'],
        'VM_ICS_KVM_NM_220036_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_NM_220036_ERROR'],
        'VM_ICS_KVM_NM_220037_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_NM_220037_ERROR'],
        'VM_ICS_KVM_NM_220038_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_NM_220038_ERROR'],
        'VM_ICS_KVM_NM_220040_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_NM_220040_ERROR'],
        'VM_ICS_KVM_NM_220041_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_NM_220041_ERROR'],
        'VM_ICS_KVM_NM_220042_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_NM_220042_ERROR'],
        'VM_ICS_KVM_NM_220043_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_NM_220043_ERROR'],
        'VM_ICS_KVM_NM_220050_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_NM_220050_ERROR'],
        'VM_ICS_KVM_NM_220052_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_NM_220052_ERROR'],
        'VM_ICS_KVM_NM_220054_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_NM_220054_ERROR'],
        'VM_ICS_KVM_NM_220058_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_NM_220058_ERROR'],
        'VM_ICS_KVM_NM_220060_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_NM_220060_ERROR'],
        'VM_ICS_KVM_NM_220061_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_NM_220061_ERROR'],
        'VM_ICS_KVM_NM_220066_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_NM_220066_ERROR'],
        'VM_ICS_KVM_NM_220068_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_NM_220068_ERROR'],
        'VM_ICS_KVM_NM_220070_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_NM_220070_ERROR'],
        'VM_ICS_KVM_NM_220071_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_NM_220071_ERROR'],
        'VM_ICS_KVM_NM_220072_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_NM_220072_ERROR'],
        'VM_ICS_KVM_NM_220075_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_NM_220075_ERROR'],
        'VM_ICS_KVM_NM_220076_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_NM_220076_ERROR'],
        'VM_ICS_KVM_NM_220079_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_NM_220079_ERROR'],
        'VM_ICS_KVM_NM_220083_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_NM_220083_ERROR'],
        'VM_ICS_KVM_NM_220085_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_NM_220085_ERROR'],
        'VM_ICS_KVM_NM_220092_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_NM_220092_ERROR'],
        'VM_ICS_KVM_NM_220093_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_NM_220093_ERROR'],
        'VM_ICS_KVM_NM_220097_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_NM_220097_ERROR'],
        'VM_ICS_KVM_NM_220101_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_NM_220101_ERROR'],
        'VM_ICS_KVM_NM_220102_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_NM_220102_ERROR'],
        'VM_ICS_KVM_NM_220103_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_NM_220103_ERROR'],
        'VM_ICS_KVM_NM_220105_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_NM_220105_ERROR'],
        'VM_ICS_KVM_NM_220114_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_NM_220114_ERROR'],
        'VM_ICS_KVM_NM_220115_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_NM_220115_ERROR'],
        'VM_ICS_KVM_NM_220116_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_NM_220116_ERROR'],
        'VM_ICS_KVM_NM_220120_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_NM_220120_ERROR'],
        'VM_ICS_KVM_NM_220121_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_NM_220121_ERROR'],
        'VM_ICS_KVM_NM_220126_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_NM_220126_ERROR'],
        'VM_ICS_KVM_NM_220129_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_NM_220129_ERROR'],
        'VM_ICS_KVM_NM_220132_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_NM_220132_ERROR'],
        'VM_ICS_KVM_NM_220137_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_NM_220137_ERROR'],
        'VM_ICS_KVM_NM_220140_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_NM_220140_ERROR'],
        'VM_ICS_KVM_NM_220147_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_NM_220147_ERROR'],
        'VM_ICS_KVM_NM_220148_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_NM_220148_ERROR'],
        'VM_ICS_KVM_NM_220149_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_NM_220149_ERROR'],
        'VM_ICS_KVM_NM_220150_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_NM_220150_ERROR'],
        'VM_ICS_KVM_NM_220153_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_NM_220153_ERROR'],
        'VM_ICS_KVM_NM_220154_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_NM_220154_ERROR'],
        'VM_ICS_KVM_NM_220155_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_NM_220155_ERROR'],
        'VM_ICS_KVM_NM_220156_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_NM_220156_ERROR'],
        'VM_ICS_KVM_NM_220157_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_NM_220157_ERROR'],
        'VM_ICS_KVM_NM_220169_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_NM_220169_ERROR'],
        'VM_ICS_KVM_NM_220170_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_NM_220170_ERROR'],
        'VM_ICS_KVM_NM_220171_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_NM_220171_ERROR'],
        'VM_ICS_KVM_NM_220172_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_NM_220172_ERROR'],
        'VM_ICS_KVM_NM_220173_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_NM_220173_ERROR'],
        'VM_ICS_KVM_NM_220174_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_NM_220174_ERROR'],
        'VM_ICS_KVM_NM_220176_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_NM_220176_ERROR'],
        'VM_ICS_KVM_NM_220177_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_NM_220177_ERROR'],
        'VM_ICS_KVM_NM_220178_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_NM_220178_ERROR'],
        'VM_ICS_KVM_NM_220179_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_NM_220179_ERROR'],
        'VM_ICS_KVM_NM_220180_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_NM_220180_ERROR'],
        'VM_ICS_KVM_NM_220181_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_NM_220181_ERROR'],
        'VM_ICS_KVM_NM_220182_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_NM_220182_ERROR'],
        'VM_ICS_KVM_NM_220183_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_NM_220183_ERROR'],
        'VM_ICS_KVM_NM_220184_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_NM_220184_ERROR'],
        'VM_ICS_KVM_NM_220185_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_NM_220185_ERROR'],
        'VM_ICS_KVM_NM_220186_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_NM_220186_ERROR'],
        'VM_ICS_KVM_NM_220188_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_NM_220188_ERROR'],
        'VM_ICS_KVM_NM_220189_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_NM_220189_ERROR'],
        'VM_ICS_KVM_NM_220192_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_NM_220192_ERROR'],
        'VM_ICS_KVM_NM_220193_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_NM_220193_ERROR'],
        'VM_ICS_KVM_NM_220194_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_NM_220194_ERROR'],
        'VM_ICS_KVM_NM_220204_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_NM_220204_ERROR'],
        'VM_ICS_KVM_NM_220212_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_NM_220212_ERROR'],
        'VM_ICS_KVM_NM_220213_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_NM_220213_ERROR'],
        'VM_ICS_KVM_NM_220214_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_NM_220214_ERROR'],
        'VM_ICS_KVM_NM_220215_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_NM_220215_ERROR'],
        'VM_ICS_KVM_NM_220217_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_NM_220217_ERROR'],
        'VM_ICS_KVM_NM_220218_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_NM_220218_ERROR'],
        'VM_ICS_KVM_NM_220219_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_NM_220219_ERROR'],
        'VM_ICS_KVM_NM_220220_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_NM_220220_ERROR'],
        'VM_ICS_KVM_NM_220226_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_NM_220226_ERROR'],
        'VM_ICS_KVM_NM_220230_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_NM_220230_ERROR'],
        'VM_ICS_KVM_NM_220231_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_NM_220231_ERROR'],
        'VM_ICS_KVM_TOOL_220014_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_TOOL_220014_ERROR'],
        'VM_ICS_KVM_TOOL_220020_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_TOOL_220020_ERROR'],
        'VM_ICS_KVM_TOOL_220022_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_TOOL_220022_ERROR'],
        'VM_ICS_KVM_TOOL_220023_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_TOOL_220023_ERROR'],
        'VM_ICS_KVM_TOOL_220232_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_TOOL_220232_ERROR'],
        'VM_ICS_KVM_SNAP_220027_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_SNAP_220027_ERROR'],
        'VM_ICS_KVM_SNAP_220028_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_SNAP_220028_ERROR'],
        'VM_ICS_KVM_SNAP_220064_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_SNAP_220064_ERROR'],
        'VM_ICS_KVM_SNAP_220081_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_SNAP_220081_ERROR'],
        'VM_ICS_KVM_SNAP_220086_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_SNAP_220086_ERROR'],
        'VM_ICS_KVM_SNAP_220088_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_SNAP_220088_ERROR'],
        'VM_ICS_KVM_SNAP_220094_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_SNAP_220094_ERROR'],
        'VM_ICS_KVM_SNAP_220119_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_SNAP_220119_ERROR'],
        'VM_ICS_KVM_SNAP_220131_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_SNAP_220131_ERROR'],
        'VM_ICS_KVM_SNAP_220138_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_SNAP_220138_ERROR'],
        'VM_ICS_KVM_SNAP_220187_ERROR' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_SNAP_220187_ERROR'],

        'KVM_OPENSTACK_SET_VOLUME_IMAGE_METADATA_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_OPENSTACK_SET_VOLUME_IMAGE_METADATA_ERROR'],
        'KVM_OPENSTACK_GET_VOLUME_QOS_INFO_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_OPENSTACK_GET_VOLUME_QOS_INFO_ERROR'],
        'KVM_OPENSTACK_SET_VOLUME_QOS_INFO_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_OPENSTACK_SET_VOLUME_QOS_INFO_ERROR'],

        'VM_ICS_KVM_QCOW_DISK_NOT_SUPPORT_CBT_MODEL' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_QCOW_DISK_NOT_SUPPORT_CBT_MODEL'],
        'VM_DETECTED_INDEPENDENT_PERSISTENT_DISK_ERROR' => Xphp::$_lang['WEB_ERROR_VM_DETECTED_INDEPENDENT_PERSISTENT_DISK_ERROR'],

        // Ceph s3 cloud storage error code
        'CEPH_S3_CLOUD_STORAGE_BUCKET_NAME_TOO_LONG_ERROR' => Xphp::$_lang['WEB_ERROR_CEPH_S3_CLOUD_STORAGE_BUCKET_NAME_TOO_LONG_ERROR'],
        'CEPH_S3_CLOUD_STORAGE_BUCKET_NAME_TOO_SHORT_ERROR' => Xphp::$_lang['WEB_ERROR_CEPH_S3_CLOUD_STORAGE_BUCKET_NAME_TOO_SHORT_ERROR'],
        'CEPH_S3_CLOUD_STORAGE_NAME_LOOKUP_ERROR' => Xphp::$_lang['WEB_ERROR_CEPH_S3_CLOUD_STORAGE_NAME_LOOKUP_ERROR'],
        'CEPH_S3_CLOUD_STORAGE_CONNECT_ERROR' => Xphp::$_lang['WEB_ERROR_CEPH_S3_CLOUD_STORAGE_CONNECT_ERROR'],
        'CEPH_S3_CLOUD_STORAGE_ACCESS_DENIED_ERROR' => Xphp::$_lang['WEB_ERROR_CEPH_S3_CLOUD_STORAGE_ACCESS_DENIED_ERROR'],
        'CEPH_S3_CLOUD_STORAGE_ACCOUNT_PROBLEM_ERROR' => Xphp::$_lang['WEB_ERROR_CEPH_S3_CLOUD_STORAGE_ACCOUNT_PROBLEM_ERROR'],
        'CEPH_S3_CLOUD_STORAGE_BUCKET_ALREADY_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_CEPH_S3_CLOUD_STORAGE_BUCKET_ALREADY_EXIST_ERROR'],
        'CEPH_S3_CLOUD_STORAGE_BUCKET_NOT_EMPTY_ERROR' => Xphp::$_lang['WEB_ERROR_CEPH_S3_CLOUD_STORAGE_BUCKET_NOT_EMPTY_ERROR'],
        'CEPH_S3_CLOUD_STORAGE_ENTITY_TOO_SMALL_ERROR' => Xphp::$_lang['WEB_ERROR_CEPH_S3_CLOUD_STORAGE_ENTITY_TOO_SMALL_ERROR'],
        'CEPH_S3_CLOUD_STORAGE_ENTITY_TOO_LARGE_ERROR' => Xphp::$_lang['WEB_ERROR_CEPH_S3_CLOUD_STORAGE_ENTITY_TOO_LARGE_ERROR'],
        'CEPH_S3_CLOUD_STORAGE_INVALID_ACCESS_KEY_ID_ERROR' => Xphp::$_lang['WEB_ERROR_CEPH_S3_CLOUD_STORAGE_INVALID_ACCESS_KEY_ID_ERROR'],
        'CEPH_S3_CLOUD_STORAGE_INVALID_BUCKET_NAME_ERROR' => Xphp::$_lang['WEB_ERROR_CEPH_S3_CLOUD_STORAGE_INVALID_BUCKET_NAME_ERROR'],
        'CEPH_S3_CLOUD_STORAGE_INVALID_BUCKET_STATE_ERROR' => Xphp::$_lang['WEB_ERROR_CEPH_S3_CLOUD_STORAGE_INVALID_BUCKET_STATE_ERROR'],
        'CEPH_S3_CLOUD_STORAGE_INVALID_LOCATION_CONSTRAINT_ERROR' => Xphp::$_lang['WEB_ERROR_CEPH_S3_CLOUD_STORAGE_INVALID_LOCATION_CONSTRAINT_ERROR'],
        'CEPH_S3_CLOUD_STORAGE_INVALID_OBJECT_STATE_ERROR' => Xphp::$_lang['WEB_ERROR_CEPH_S3_CLOUD_STORAGE_INVALID_OBJECT_STATE_ERROR'],
        'CEPH_S3_CLOUD_STORAGE_KEY_TOO_LONG_ERROR' => Xphp::$_lang['WEB_ERROR_CEPH_S3_CLOUD_STORAGE_KEY_TOO_LONG_ERROR'],
        'CEPH_S3_CLOUD_STORAGE_NO_SUCH_BUCKET_ERROR' => Xphp::$_lang['WEB_ERROR_CEPH_S3_CLOUD_STORAGE_NO_SUCH_BUCKET_ERROR'],
        'CEPH_S3_CLOUD_STORAGE_NO_SUCH_KEY_ERROR' => Xphp::$_lang['WEB_ERROR_CEPH_S3_CLOUD_STORAGE_NO_SUCH_KEY_ERROR'],
        'CEPH_S3_CLOUD_STORAGE_NO_SUCH_BUCKET_POLICY_ERROR' => Xphp::$_lang['WEB_ERROR_CEPH_S3_CLOUD_STORAGE_NO_SUCH_BUCKET_POLICY_ERROR'],
        'CEPH_S3_CLOUD_STORAGE_SERVICE_UNAVAILABLE_ERROR' => Xphp::$_lang['WEB_ERROR_CEPH_S3_CLOUD_STORAGE_SERVICE_UNAVAILABLE_ERROR'],
        'CEPH_S3_CLOUD_STORAGE_QUOTA_EXCEEDED_ERROR' => Xphp::$_lang['WEB_ERROR_CEPH_S3_CLOUD_STORAGE_QUOTA_EXCEEDED_ERROR'],
        'CEPH_S3_CLOUD_STORAGE_REQUEST_TIMEOUT_ERROR' => Xphp::$_lang['WEB_ERROR_CEPH_S3_CLOUD_STORAGE_REQUEST_TIMEOUT_ERROR'],
        'CEPH_S3_CLOUD_STORAGE_S3_INIT_ERROR' => Xphp::$_lang['WEB_ERROR_CEPH_S3_CLOUD_STORAGE_S3_INIT_ERROR'],

        // Winhong kvm error code
        'KVM_WINHONG_HOST_POOL_LIST_EMPTY_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_WINHONG_HOST_POOL_LIST_EMPTY_ERROR'],
        'KVM_WINHONG_SWITCH_NOT_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_WINHONG_SWITCH_NOT_EXIST_ERROR'],
        'KVM_WINHONG_POWEROFF_VM_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_WINHONG_POWEROFF_VM_ERROR'],
        'KVM_WINHONG_POWERON_VM_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_WINHONG_POWERON_VM_ERROR'],
        'KVM_WINHONG_CREATE_VM_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_WINHONG_CREATE_VM_ERROR'],
        'KVM_WINHONG_PORT_GROUPS_NOT_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_WINHONG_PORT_GROUPS_NOT_EXIST_ERROR'],
        'KVM_WINHONG_VM_NOT_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_WINHONG_VM_NOT_EXIST_ERROR'],
        'KVM_WINHONG_DELETE_VM_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_WINHONG_DELETE_VM_ERROR'],
        'KVM_WINHONG_DELETE_STORAGE_POOL_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_WINHONG_DELETE_STORAGE_POOL_ERROR'],
        'KVM_WINHONG_REFRESH_STORAGE_POOL_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_WINHONG_REFRESH_STORAGE_POOL_ERROR'],
        'KVM_WINHONG_RESOURCE_NOT_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_WINHONG_RESOURCE_NOT_EXIST_ERROR'],
        'KVM_WINHONG_CREATE_NAS_STORAGE_POOL_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_WINHONG_CREATE_NAS_STORAGE_POOL_ERROR'],
        'KVM_WINHONG_DELETE_VOLUME_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_WINHONG_DELETE_VOLUME_ERROR'],
        'KVM_WINHONG_CREATE_NAS_STORAGE_STORE_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_WINHONG_CREATE_NAS_STORAGE_STORE_ERROR'],
        'KVM_WINHONG_CREATE_SNAPSHOT_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_WINHONG_CREATE_SNAPSHOT_ERROR'],
        'KVM_WINHONG_DELETE_SNAPSHOT_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_WINHONG_DELETE_SNAPSHOT_ERROR'],
        'KVM_WINHONG_CEPH_CONFIG_NOT_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_WINHONG_CEPH_CONFIG_NOT_EXIST_ERROR'],
        'KVM_WINHONG_CONNECT_VIRT_AGENT_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_WINHONG_CONNECT_VIRT_AGENT_ERROR'],
        'KVM_WINHONG_STORAGE_POOL_NOT_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_WINHONG_STORAGE_POOL_NOT_EXIST_ERROR'],
        'KVM_WINHONG_STORAGE_VOLUME_NOT_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_WINHONG_STORAGE_VOLUME_NOT_EXIST_ERROR'],
        'KVM_WINHONG_SCAN_STORE_RESOURCE_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_WINHONG_SCAN_STORE_RESOURCE_ERROR'],
        'KVM_WINHONG_STOP_STORAGE_POOL_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_WINHONG_STOP_STORAGE_POOL_ERROR'],
        'KVM_WINHONG_START_STORAGE_POOL_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_WINHONG_START_STORAGE_POOL_ERROR'],
        'KVM_WINHONG_API_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_WINHONG_API_ERROR'],
        'KVM_WINHONG_MODIFY_VM_BOOT_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_WINHONG_MODIFY_VM_BOOT_ERROR'],
        'KVM_WINHONG_VM_INSTALL_TOOLS_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_WINHONG_VM_INSTALL_TOOLS_ERROR'],					//  vm install tools error
        'KVM_WINHONG_MODIFY_VM_CONSOLE_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_WINHONG_MODIFY_VM_CONSOLE_ERROR'],					// modify vm console error
        'KVM_WINHONG_MODIFY_INTERFACE_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_WINHONG_MODIFY_INTERFACE_ERROR'],					// modify interface error
        'KVM_WINHONG_VM_ADD_VIDEO_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_WINHONG_VM_ADD_VIDEO_ERROR'],						// vm add video error
        'KVM_WINHONG_MODIFY_VM_VIDEO_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_WINHONG_MODIFY_VM_VIDEO_ERROR'],					// modify vm video error

        'KVM_OPENSTACK_GET_ALL_FLAVOR_TYPES_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_OPENSTACK_GET_ALL_FLAVOR_TYPES_ERROR'],					// get all flavor types error
        'KVM_OPENSTACK_GET_FLAVOR_TYPE_DETAIL_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_OPENSTACK_GET_FLAVOR_TYPE_DETAIL_ERROR'],				// get flavor type detail error
        'KVM_OPENSTACK_V2V_NOT_SUPPORT_IMAGE_START' => Xphp::$_lang['WEB_ERROR_KVM_OPENSTACK_V2V_NOT_SUPPORT_IMAGE_START'],				// openstack v2v not support image start
        'KVM_OPENSTACK_APPLIANCE_RECOVERY_NOT_SUPPORT_IMAGE_START' => Xphp::$_lang['WEB_ERROR_KVM_OPENSTACK_APPLIANCE_RECOVERY_NOT_SUPPORT_IMAGE_START'],

        'VM_V2V_APPLIANCE_CHOOSE_ERROR' => Xphp::$_lang['WEB_ERROR_VM_V2V_APPLIANCE_CHOOSE_ERROR'],							// VM Error: appliance choose error

        'KVM_WINHONG_GET_HOST_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_WINHONG_GET_HOST_ERROR'],							// winhong kvm: get host error（获取主机信息失败）
        'KVM_WINHONG_GET_HOST_RESOURCE_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_WINHONG_GET_HOST_RESOURCE_ERROR'],					// winhong kvm: get host resource error（获取主机资源失败）
        'KVM_WINHONG_GET_HOST_POOL_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_WINHONG_GET_HOST_POOL_ERROR'],						// winhong kvm: get host pool error（获取主机池失败）
        'KVM_WINHONG_GET_CLUSTER_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_WINHONG_GET_CLUSTER_ERROR'],						// winhong kvm: get cluster error（获取集群信息失败）
        'KVM_WINHONG_GET_VM_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_WINHONG_GET_VM_ERROR'],								// winhong kvm: get vm error（获取虚拟机信息失败）
        'KVM_WINHONG_GET_VM_DISK_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_WINHONG_GET_VM_DISK_ERROR'],						// winhong kvm: get vm disk error（获取虚拟机磁盘失败）
        'KVM_WINHONG_GET_VM_CONSOLE_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_WINHONG_GET_VM_CONSOLE_ERROR'],						// winhong kvm: get vm console error（获取虚拟机控制台信息失败）
        'KVM_WINHONG_GET_STORAGE_POOL_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_WINHONG_GET_STORAGE_POOL_ERROR'],					// winhong kvm: get storage pool error（获取存储池失败）
        'KVM_WINHONG_GET_STORAGE_VOLUME_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_WINHONG_GET_STORAGE_VOLUME_ERROR'],					// winhong kvm: get storage volume error（获取存储卷失败）
        'KVM_WINHONG_GET_STORAGE_STORE_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_WINHONG_GET_STORAGE_STORE_ERROR'],					// winhong kvm: get storage store error（获取存储设备失败）
        'KVM_WINHONG_GET_STORE_RESOURCE_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_WINHONG_GET_STORE_RESOURCE_ERROR'],					// winhong kvm: get store resource error（获取存储设备资源失败）
        'KVM_WINHONG_GET_SNAPSHOT_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_WINHONG_GET_SNAPSHOT_ERROR'],						// winhong kvm: get snapshot error（获取快照失败）
        'KVM_WINHONG_GET_PORT_GROUP_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_WINHONG_GET_PORT_GROUP_ERROR'],						// winhong kvm: get port group error（获取端口组信息失败）
        'KVM_WINHONG_GET_PRODUCT_VERSION_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_WINHONG_GET_PRODUCT_VERSION_ERROR'],				// winhong kvm: get product version error（获取版本失败）
        'KVM_WINHONG_GET_VSWITCH_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_WINHONG_GET_VSWITCH_ERROR'],						// winhong kvm: get virual switch error（获取虚拟交换机失败）
        'KVM_WINHONG_GET_TASK_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_WINHONG_GET_TASK_ERROR'],							// winhong kvm: get task error（获取任务信息失败）
        'KVM_WINHONG_LOGIN_NODE_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_WINHONG_LOGIN_NODE_ERROR'],							// winhong kvm: login to winhong node error（登录失败）

        /*********sure backup************/
        /*******add by BrinePineapple****/
        //virtual lab
        'SR_DEPLOY_SURE_BACKUP_NETWORK_ERROR' => Xphp::$_lang['WEB_ERROR_SR_DEPLOY_SURE_BACKUP_NETWORK_ERROR'],				//vmware: deploy sure backup network error
        'SR_ANALYSIS_IP_ADDRESS_ERROR' => Xphp::$_lang['WEB_ERROR_SR_ANALYSIS_IP_ADDRESS_ERROR'],						//vmware: analysis ip address error
        'SR_ANALYSIS_NETMASK_ERROR' => Xphp::$_lang['WEB_ERROR_SR_ANALYSIS_NETMASK_ERROR'],							//vmware: analysis netmask error
        'SR_NETMASK_ILLEGAL' => Xphp::$_lang['WEB_ERROR_SR_NETMASK_ILLEGAL'],									//vmware: netmask is illegal
        'SR_VIRTUAL_LAB_ALL_THE_ISOLATED_SEGMENT_USED' => Xphp::$_lang['WEB_ERROR_SR_VIRTUAL_LAB_ALL_THE_ISOLATED_SEGMENT_USED'],		//vmware: isolated segment is used
        'SR_VIRTUAL_LAB_NETWORK_MAP_LIST_ERROR' => Xphp::$_lang['WEB_ERROR_SR_VIRTUAL_LAB_NETWORK_MAP_LIST_ERROR'],				//vmware: network map list error
        'SR_VIRTUAL_LAB_ISOLATED_NETWORK_NOT_ENOUGH' => Xphp::$_lang['WEB_ERROR_SR_VIRTUAL_LAB_ISOLATED_NETWORK_NOT_ENOUGH'],			//vmware: sure backup of virtual lab is not enought
        'SR_VIRTUAL_ANALYSIS_PROXY_NETWORK_ERROR' => Xphp::$_lang['WEB_ERROR_SR_VIRTUAL_ANALYSIS_PROXY_NETWORK_ERROR'],			//vmware: analysis virtual lab proxy error
        'SR_GET_VIRTUAL_LAB_PROXY_INFO_ERROR' => Xphp::$_lang['WEB_ERROR_SR_GET_VIRTUAL_LAB_PROXY_INFO_ERROR'],				//vmware: virtual lab proxy info error
        'SR_DELETE_VIRTUAL_LAB_ERROR' => Xphp::$_lang['WEB_ERROR_SR_DELETE_VIRTUAL_LAB_ERROR'],						//vmware: delete virtual lab error
        'SR_GET_RESOURCE_POOL_ERROR' => Xphp::$_lang['WEB_ERROR_SR_GET_RESOURCE_POOL_ERROR'],							//vmware: get resource pool error
        'SR_ADD_RESOURCE_POOL_ERROR' => Xphp::$_lang['WEB_ERROR_SR_ADD_RESOURCE_POOL_ERROR'],							//vmware: add resource pool error
        'SR_GET_HOST_CLUSTER_ERROR' => Xphp::$_lang['WEB_ERROR_SR_GET_HOST_CLUSTER_ERROR'],							//vmware: get host cluster error
        'SR_ADD_FOLDER_ERROR' => Xphp::$_lang['WEB_ERROR_SR_ADD_FOLDER_ERROR'],								//vmware: add folder error
        'SR_ADD_NETCARD_FOR_VM_ERROR' => Xphp::$_lang['WEB_ERROR_SR_ADD_NETCARD_FOR_VM_ERROR'],						//vmware: add netcard for vm error
        'SR_NETWORK_MAP_LIST_ERROR' => Xphp::$_lang['WEB_ERROR_SR_NETWORK_MAP_LIST_ERROR'],							//vmware: network map list error
        'SR_DELETE_NETWORK_MAP_ERROR' => Xphp::$_lang['WEB_ERROR_SR_DELETE_NETWORK_MAP_ERROR'],						//vmware: delete network map error
        'SR_REMOVE_RESOURCE_POOL_ERROR' => Xphp::$_lang['WEB_ERROR_SR_REMOVE_RESOURCE_POOL_ERROR'],						//vmware: remove resource pool error
        'SR_REMOVE_FOLDER_ERROR' => Xphp::$_lang['WEB_ERROR_SR_REMOVE_FOLDER_ERROR'],								//vmware: remove folder error
        'VM_INSTANT_RECOVERY_NFS_SERVER_IP_NOT_MATCH_ERROR' => Xphp::$_lang['WEB_ERROR_VM_INSTANT_RECOVERY_NFS_SERVER_IP_NOT_MATCH_ERROR'],		//vmware: NFS server is not match
        'SR_GET_HOST_CONFIG_MANAGER_ERROR' => Xphp::$_lang['WEB_ERROR_SR_GET_HOST_CONFIG_MANAGER_ERROR'],					//vmware: get host config manager error
        'SR_GET_VM_IP_ADDRESS_ERROR' => Xphp::$_lang['WEB_ERROR_SR_GET_VM_IP_ADDRESS_ERROR'],							//vmware: get vm ip address error
        //sure backup recovery task
        'SR_WITHOUT_ISOLATED_NETWORK' => Xphp::$_lang['WEB_ERROR_SR_WITHOUT_ISOLATED_NETWORK'],						//vmware: without isolated network
        'SR_BUILD_SURE_BACKUP_VM_LIST_ERROR' => Xphp::$_lang['WEB_ERROR_SR_BUILD_SURE_BACKUP_VM_LIST_ERROR'],					//vmware: build sure backup vm list error

        //verifited way
        'SR_SCREEN_SHOT_VM_ERROR' => Xphp::$_lang['WEB_ERROR_SR_SCREEN_SHOT_VM_ERROR'],							//vmware: screen shot error
        'SR_PING_TEST_VM_ERROR' => Xphp::$_lang['WEB_ERROR_SR_PING_TEST_VM_ERROR'],								//vmware: ping test error
        'SR_HEARTBEAT_TEST_VM_ERROR' => Xphp::$_lang['WEB_ERROR_SR_HEARTBEAT_TEST_VM_ERROR'],							//vmware: heartbeat test vm error
        'SR_DOWNLOAD_SCREEN_SHOT_ERROR' => Xphp::$_lang['WEB_ERROR_SR_DOWNLOAD_SCREEN_SHOT_ERROR'],						//vmware: download screen shot error

        //add for backup and recovery UEFI boot file
        'VMWARE_UPLOAD_FILE_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_UPLOAD_FILE_ERROR'],								//vmware: upload file error
        'VMWARE_DOWNLOAD_FILE_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_DOWNLOAD_FILE_ERROR'],								//vmware: download file error
        'VMWARE_RECOVERY_BOOT_FILE_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_RECOVERY_BOOT_FILE_ERROR'],						//vmware: recovery boot file error
        'VMWARE_BACKUP_BOOT_FILE_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_BACKUP_BOOT_FILE_ERROR'],							//vmware: backup boot file error

        'KVM_SMARTX_DATA_CENTER_GET_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_SMARTX_DATA_CENTER_GET_ERROR'],					// smartx kvm: get data center error（获取数据中心失败）
        'KVM_SMARTX_ORGANIZATION_GET_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_SMARTX_ORGANIZATION_GET_ERROR'],				// smartx kvm: get organization error（获取组织失败）
        'KVM_SMARTX_ORGANIZATION_NOT_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_SMARTX_ORGANIZATION_NOT_EXIST_ERROR'],				// smartx kvm: the organization does exist error（组织不存在）
        'KVM_SMARTX_HOST_NOT_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_SMARTX_HOST_NOT_EXIST_ERROR'],					// smartx kvm: the host does not exist error（主机不存在）
        'KVM_SMARTX_HOST_GET_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_SMARTX_HOST_GET_ERROR'],					// smartx kvm: get host info error（获取主机信息失败）
        'KVM_SMARTX_CLUSTER_GET_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_SMARTX_CLUSTER_GET_ERROR'],					// smartx kvm: get cluster info error（获取集群信息失败）
        'KVM_SMARTX_VLAN_NOT_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_SMARTX_VLAN_NOT_EXIST_ERROR'],					// smartx kvm: the vlan does not exist error（vlan不存在）
        'KVM_SMARTX_VLAN_GET_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_SMARTX_VLAN_GET_ERROR'],					// smartx kvm: get vlan info error（获取vlan信息失败）
        'KVM_SMARTX_SWITCH_GET_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_SMARTX_SWITCH_GET_ERROR'],					// smartx kvm: get virtual switch info error（获取虚拟交换机失败）
        'KVM_SMARTX_SNAPSHOT_CREATE_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_SMARTX_SNAPSHOT_CREATE_ERROR'],				// smartx kvm: the vm creates snapshot error（创建快照失败）
        'KVM_SMARTX_SNAPSHOT_NOT_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_SMARTX_SNAPSHOT_NOT_EXIST_ERROR'],				// smartx kvm: the snapshot does not exist error（快照不存在）
        'KVM_SMARTX_SNAPSHOT_DELETE_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_SMARTX_SNAPSHOT_DELETE_ERROR'],				// smartx kvm: the vm deletes snapshot error（删除快照失败）
        'KVM_SMARTX_SNAPSHOT_GET_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_SMARTX_SNAPSHOT_GET_ERROR'],					// smartx kvm: get snapshot info error（获取快照信息失败）
        'KVM_SMARTX_LUN_SNAPSHOT_GET_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_SMARTX_LUN_SNAPSHOT_GET_ERROR'],				// smartx kvm: get lun snapshot info error（获取lun快照信息失败）
        'KVM_SMARTX_ISCSI_LUN_GET_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_SMARTX_ISCSI_LUN_GET_ERROR'],					// smartx kvm: get iscsi lun error（获取iscsi lun信息失败）
        'KVM_SMARTX_VM_NOT_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_SMARTX_VM_NOT_EXIST_ERROR'],					// smartx kvm: the vm does not exist error（虚拟机不存在）
        'KVM_SMARTX_VM_CREATE_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_SMARTX_VM_CREATE_ERROR'],					// smartx kvm: the vm creates error（创建虚拟机失败）
        'KVM_SMARTX_VM_DELETE_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_SMARTX_VM_DELETE_ERROR'],					// smartx kvm: the vm deletes error（删除虚拟机失败）
        'KVM_SMARTX_VM_GET_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_SMARTX_VM_GET_ERROR'],					// smartx kvm: get vm info error（获取虚拟机信息失败）
        'KVM_SMARTX_VM_UPDATE_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_SMARTX_VM_UPDATE_ERROR'],					// smartx kvm: update vm info error（更新虚拟机失败）
        'KVM_SMARTX_VM_POWEROFF_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_SMARTX_VM_POWEROFF_ERROR'],					// smartx kvm: the vm powers off error（关闭虚拟机失败）
        'KVM_SMARTX_VM_POWERON_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_SMARTX_VM_POWERON_ERROR'],					// smartx kvm: the vm powers on error（打开虚拟机失败）
        'KVM_SMARTX_VM_DISK_GET_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_SMARTX_VM_DISK_GET_ERROR'],					// smartx kvm: get vm disk info error（获取虚拟机磁盘信息失败）
        'KVM_SMARTX_VM_VOLUME_GET_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_SMARTX_VM_VOLUME_GET_ERROR'],					// smartx kvm: get vm volume info error（获取虚拟卷失败）
        'KVM_SMARTX_TASK_GET_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_SMARTX_TASK_GET_ERROR'],					// smartx kvm: get task info error（获取任务信息失败）
        'KVM_SMARTX_LOGIN_NODE_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_SMARTX_LOGIN_NODE_ERROR'],					// smartx kvm: login to smartx node error（登录到smartx节点失败）
        'KVM_SMARTX_ZBS_READ_VOLUME_DATA_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_SMARTX_ZBS_READ_VOLUME_DATA_ERROR'],				// smartx kvm: read volume data error（读取虚拟卷数据失败）
        'KVM_SMARTX_ZBS_LIST_VOLUME_EXTENT_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_SMARTX_ZBS_LIST_VOLUME_EXTENT_ERROR'],				// smartx kvm: list volume extents error（列出虚拟卷区段失败）
        'KVM_SMARTX_ZBS_CREATE_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_SMARTX_ZBS_CREATE_ERROR'],					// smartx kvm: create zbs object error（创建zbs对象失败）
        'KVM_SMARTX_ZBS_WRITE_DATA_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_SMARTX_ZBS_WRITE_DATA_ERROR'],					// smartx kvm: zbs writes data error（虚拟卷写入数据失败）
        'KVM_SMARTX_ZBS_CONFIG_PARAM_GET_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_SMARTX_ZBS_CONFIG_PARAM_GET_ERROR'],				// smartx kvm: get zbs config file param error（获取配置文件参数失败）
        'KVM_XSKY_CEPH_LOGIN_GET_TOKENID_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_XSKY_CEPH_LOGIN_GET_TOKENID_ERROR'],		// Kvm Xsky Ceph Error: get xsky ceph token id from json value error（获取ceph token错误）
        'KVM_XSKY_CEPH_CLONE_SNAP_TO_VOLUME_TIME_OUT' => Xphp::$_lang['WEB_ERROR_KVM_XSKY_CEPH_CLONE_SNAP_TO_VOLUME_TIME_OUT'],	// Kvm Xsky Ceph Error: clone snap to volume time out（克隆快照到卷超时）
        'KVM_XSKY_CEPH_CLONE_SNAP_TO_VOLUME_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_XSKY_CEPH_CLONE_SNAP_TO_VOLUME_ERROR'],	// Kvm Xsky Ceph Error: clone snap to volume error（克隆快照到卷错误）
        'KVM_XSKY_CEPH_DELETE_VOLUME_TIME_OUT' => Xphp::$_lang['WEB_ERROR_KVM_XSKY_CEPH_DELETE_VOLUME_TIME_OUT'],		// Kvm Xsky Ceph Error: delete volume time out（删除卷超时）
        'KVM_XSKY_CEPH_CREATE_ACCESS_PATH_TIME_OUT' => Xphp::$_lang['WEB_ERROR_KVM_XSKY_CEPH_CREATE_ACCESS_PATH_TIME_OUT'],	// Kvm Xsky Ceph Error: create access path time out（创建访问路径超时）
        'KVM_XSKY_CEPH_CREATE_ACCESS_PATH_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_XSKY_CEPH_CREATE_ACCESS_PATH_ERROR'],		// Kvm Xsky Ceph Error: create access path error（创建访问路径错误）
        'KVM_XSKY_CEPH_DELETE_ACCESS_PATH_TIME_OUT' => Xphp::$_lang['WEB_ERROR_KVM_XSKY_CEPH_DELETE_ACCESS_PATH_TIME_OUT'],	// Kvm Xsky Ceph Error: delete access path time out（删除访问路径超时）
        'VM_ICS_KVM_NFS_NOT_SUPPORT_CBT_SNAPSHOTS' => Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_NFS_NOT_SUPPORT_CBT_SNAPSHOTS'],  //ics api error:nfs storage does not support cbt snapshots
        'VMWARE_GET_HOST_MOUNT_INFO_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_GET_HOST_MOUNT_INFO_ERROR'], 					// vmware get host mount info error
        'VMWARE_RENAME_OBJECT_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_RENAME_OBJECT_ERROR'], 				//Vmware rename object error
        'VMWARE_MOVE_VM_TO_NEW_LOCATION_ERROR' => Xphp::$_lang['WEB_ERROR_VMWARE_MOVE_VM_TO_NEW_LOCATION_ERROR'], 	//Vmware move vm to new location error
        'KVM_SMARTX_STORAGE_POLICY_GET_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_SMARTX_STORAGE_POLICY_GET_ERROR'],					// smartx kvm: get storage policy error（获取存储策略失败）
        'KVM_SMARTX_VM_FOLDER_GET_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_SMARTX_VM_FOLDER_GET_ERROR'],						// smartx kvm: get vm folder error（获取虚拟机放置组失败）
        'KVM_SMARTX_ISCSI_TARGET_GET_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_SMARTX_ISCSI_TARGET_GET_ERROR'],						// smartx kvm: get iscsi target error（获取iscsi target失败）
        'KVM_SMARTX_VDS_GET_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_SMARTX_VDS_GET_ERROR'],							// smartx kvm: get vds error（获取vds失败）
        'KVM_SMARTX_API_VERSION_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_SMARTX_API_VERSION_ERROR'],							// smartx kvm: get api version error（获取API版本失败）

        // sangfor kvm
        'KVM_SANGFOR_SNAPSHOT_COUNT_EXCEED_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_SANGFOR_SNAPSHOT_COUNT_EXCEED_ERROR'],				// sangfor kvm: excessive number of snapshots（快照数量过多）

        //openstack kvm
        'KVM_OPENSTACK_GET_CINDER_API_VERSION_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_OPENSTACK_GET_CINDER_API_VERSION_ERROR'],						// KvmOpenStack Error: openstack get cinder api version error(获取cinder api版本错误)
        'KVM_OPENSTACK_GET_GROUP_TYPES_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_OPENSTACK_GET_GROUP_TYPES_ERROR'],										// KvmOpenStack Error: openstack get group types error(获取组类型错误)
        'KVM_OPENSTACK_GET_VOLUME_GROUPS_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_OPENSTACK_GET_VOLUME_GROUPS_ERROR'],									// KvmOpenStack Error: openstack get volume groups error(获取卷组信息错误)
        'KVM_OPENSTACK_CREATE_VOLUME_GROUP_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_OPENSTACK_CREATE_VOLUME_GROUP_ERROR'],								// KvmOpenStack Error: openstack create volume group error(创建卷组错误)
        'KVM_OPENSTACK_CREATE_VOLUME_GROUP_TIMEOUT' => Xphp::$_lang['WEB_ERROR_KVM_OPENSTACK_CREATE_VOLUME_GROUP_TIMEOUT'],							// KvmOpenStack Error: openstack create volume group time out(创建卷组超时)
        'KVM_OPENSTACK_DELETE_VOLUME_GROUP_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_OPENSTACK_DELETE_VOLUME_GROUP_ERROR'],								// KvmOpenStack Error: openstack delete volume group error(删除卷组错误)
        'KVM_OPENSTACK_UPDATE_VOLUME_GROUP_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_OPENSTACK_UPDATE_VOLUME_GROUP_ERROR'],								// KvmOpenStack Error: openstack update volume group error(更新卷组错误)
        'KVM_OPENSTACK_GET_GROUP_SNAPS_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_OPENSTACK_GET_GROUP_SNAPS_ERROR'],										// KvmOpenStack Error: openstack get group snaps error(获取组快照错误)
        'KVM_OPENSTACK_CREATE_GROUP_SNAP_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_OPENSTACK_CREATE_GROUP_SNAP_ERROR'],									// KvmOpenStack Error: openstack create group snap error(创建组快照错误)
        'KVM_OPENSTACK_DELETE_GROUP_SNAP_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_OPENSTACK_DELETE_GROUP_SNAP_ERROR'],									// KvmOpenStack Error: openstack delete group snap error(删除组快照错误)
        'KVM_OPENSTACK_CREATE_GROUP_SNAP_TIMEOUT' => Xphp::$_lang['WEB_ERROR_KVM_OPENSTACK_CREATE_GROUP_SNAP_TIMEOUT'],								// KvmOpenStack Error: openstack create group snap time out(创建组快照超时)
        'KVM_OPENSTACK_DELETE_GROUP_SNAP_TIMEOUT' => Xphp::$_lang['WEB_ERROR_KVM_OPENSTACK_DELETE_GROUP_SNAP_TIMEOUT'],								// KvmOpenStack Error: openstack delete group snap time out(删除组快照超时)
        'KVM_OPENSTACK_UPDATE_VOLUMES_IN_VOLUME_GROUP_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_OPENSTACK_UPDATE_VOLUMES_IN_VOLUME_GROUP_ERROR'],		// KvmOpenStack Error: openstack update volumes in volume group(更新卷组超时)

        //ics vvdk
        'KVM_ICS_VVDK_INIT_HANDLE_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_ICS_VVDK_INIT_HANDLE_ERROR'],
        'KVM_ICS_VVDK_CREATE_CONNECTION_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_ICS_VVDK_CREATE_CONNECTION_ERROR'],
        'KVM_ICS_VVDK_OPEN_VIRTUAL_DISK_FILE_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_ICS_VVDK_OPEN_VIRTUAL_DISK_FILE_ERROR'],
        'KVM_ICS_VVDK_CLOSE_VIRTUAL_DISK_FILE_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_ICS_VVDK_CLOSE_VIRTUAL_DISK_FILE_ERROR'],

        //proxmox
        'KVM_PROXMOX_API_VERSION_GET_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_PROXMOX_API_VERSION_GET_ERROR'],
        'KVM_PROXMOX_RESOURCE_GET_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_PROXMOX_RESOURCE_GET_ERROR'],
        'KVM_PROXMOX_RESOURCE_POOL_GET_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_PROXMOX_RESOURCE_POOL_GET_ERROR'],
        'KVM_PROXMOX_CLUSTER_GET_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_PROXMOX_CLUSTER_GET_ERROR'],
        'KVM_PROXMOX_CLUSTER_STATUS_GET_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_PROXMOX_CLUSTER_STATUS_GET_ERROR'],
        'KVM_PROXMOX_NODE_GET_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_PROXMOX_NODE_GET_ERROR'],
        'KVM_PROXMOX_NODE_STATUS_GET_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_PROXMOX_NODE_STATUS_GET_ERROR'],
        'KVM_PROXMOX_BRIDGE_GET_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_PROXMOX_BRIDGE_GET_ERROR'],
        'KVM_PROXMOX_VM_GET_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_PROXMOX_VM_GET_ERROR'],
        'KVM_PROXMOX_VM_CREATE_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_PROXMOX_VM_CREATE_ERROR'],
        'KVM_PROXMOX_VM_DELETE_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_PROXMOX_VM_DELETE_ERROR'],
        'KVM_PROXMOX_VM_UPDATE_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_PROXMOX_VM_UPDATE_ERROR'],
        'KVM_PROXMOX_VM_POWEROFF_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_PROXMOX_VM_POWEROFF_ERROR'],
        'KVM_PROXMOX_VM_POWERON_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_PROXMOX_VM_POWERON_ERROR'],
        'KVM_PROXMOX_VM_CUR_STATUS_GET_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_PROXMOX_VM_CUR_STATUS_GET_ERROR'],
        'KVM_PROXMOX_VM_NEXT_ID_GET_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_PROXMOX_VM_NEXT_ID_GET_ERROR'],
        'KVM_PROXMOX_SNAPSHOT_CREATE_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_PROXMOX_SNAPSHOT_CREATE_ERROR'],
        'KVM_PROXMOX_SNAPSHOT_DELETE_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_PROXMOX_SNAPSHOT_DELETE_ERROR'],
        'KVM_PROXMOX_SNAPSHOT_GET_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_PROXMOX_SNAPSHOT_GET_ERROR'],
        'KVM_PROXMOX_STORAGE_GET_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_PROXMOX_STORAGE_GET_ERROR'],
        'KVM_PROXMOX_STORAGE_SCAN_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_PROXMOX_STORAGE_SCAN_ERROR'],
        'KVM_PROXMOX_STORAGE_CREATE_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_PROXMOX_STORAGE_CREATE_ERROR'],
        'KVM_PROXMOX_STORAGE_DELETE_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_PROXMOX_STORAGE_DELETE_ERROR'],
        'KVM_PROXMOX_STORAGE_VOLUME_GET_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_PROXMOX_STORAGE_VOLUME_GET_ERROR'],
        'KVM_PROXMOX_STORAGE_VOLUME_CREATE_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_PROXMOX_STORAGE_VOLUME_CREATE_ERROR'],
        'KVM_PROXMOX_STORAGE_VOLUME_DELETE_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_PROXMOX_STORAGE_VOLUME_DELETE_ERROR'],
        'KVM_PROXMOX_STORAGE_CONTENT_GET_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_PROXMOX_STORAGE_CONTENT_GET_ERROR'],
        'KVM_PROXMOX_CEPH_CONFIG_NOT_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_PROXMOX_CEPH_CONFIG_NOT_EXIST_ERROR'],
        'KVM_PROXMOX_VIRT_AGENT_CONNECT_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_PROXMOX_VIRT_AGENT_CONNECT_ERROR'],
        'KVM_PROXMOX_TASK_GET_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_PROXMOX_TASK_GET_ERROR'],
        'KVM_PROXMOX_LOGIN_NODE_ERROR' => Xphp::$_lang['WEB_ERROR_KVM_PROXMOX_LOGIN_NODE_ERROR'],
        'HYPERV_HAS_DIFF_TIMEPOINT_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_HYPERV_HAS_DIFF_TIMEPOINT_EXIST_ERROR'],
        'VM_FC_KVM_STORAGE_CONFIG_PARAM_GET_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_STORAGE_CONFIG_PARAM_GET_ERROR'],
        'VM_FC_KVM_STORAGE_RESOURCE_CLEAN_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_STORAGE_RESOURCE_CLEAN_ERROR'],
        'VM_FC_KVM_STORAGE_VOLUME_GET_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_STORAGE_VOLUME_GET_ERROR'],
        'VM_FC_KVM_STORAGE_VOLUME_CREATE_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_STORAGE_VOLUME_CREATE_ERROR'],
        'VM_FC_KVM_STORAGE_VOLUME_DELETE_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_STORAGE_VOLUME_DELETE_ERROR'],
        'VM_FC_KVM_STORAGE_VOLUME_MOUNT_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_STORAGE_VOLUME_MOUNT_ERROR'],
        'VM_FC_KVM_STORAGE_VOLUME_UNMOUNT_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_STORAGE_VOLUME_UNMOUNT_ERROR'],
        'VM_FC_KVM_STORAGE_VOLUME_MOUNT_INFO_GET_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_STORAGE_VOLUME_MOUNT_INFO_GET_ERROR'],
        'VM_FC_KVM_CONNECT_VIRT_AGENT_ERROR' => Xphp::$_lang['WEB_ERROR_VM_FC_KVM_CONNECT_VIRT_AGENT_ERROR'],

        //文件
        'FS_TARGET_NOT_FOUND_ERROR' => Xphp::$_lang['WEB_ERROR_FS_TARGET_NOT_FOUND_ERROR'],
        'FS_TASK_BACKUP_LIST_EMPTY_ERROR' => Xphp::$_lang['WEB_ERROR_FS_TASK_BACKUP_LIST_EMPTY_ERROR'],
        'FS_BACKUP_CONTAINER_FULL_ERROR' => Xphp::$_lang['WEB_ERROR_FS_BACKUP_CONTAINER_FULL_ERROR'],
        'FS_QUERY_TIMEPOINT_BACKUP_LIST_ERROR' => Xphp::$_lang['WEB_ERROR_FS_QUERY_TIMEPOINT_BACKUP_LIST_ERROR'],
        'FS_LOAD_MD5_INDEX_FILE_ERROR' => Xphp::$_lang['WEB_ERROR_FS_LOAD_MD5_INDEX_FILE_ERROR'],
        'FS_OPEN_BACKUP_INDEX_FILE_ERROR' => Xphp::$_lang['WEB_ERROR_FS_OPEN_BACKUP_INDEX_FILE_ERROR'],
        'FS_CONVERT_BACKUP_PATH_MD5_ERROR' => Xphp::$_lang['WEB_ERROR_FS_CONVERT_BACKUP_PATH_MD5_ERROR'],
        'FS_NAS_NOT_MOUNT_ERROR' => Xphp::$_lang['WEB_ERROR_FS_NAS_NOT_MOUNT_ERROR'],
        'FS_CHECK_FILE_FAILED' => Xphp::$_lang['WEB_ERROR_FS_CHECK_FILE_FAILED'],
        'FS_CHECK_FILE_STOP' => Xphp::$_lang['WEB_ERROR_FS_CHECK_FILE_STOP'],
        'FS_CREATE_LINUX_SNAPSHOT_ERROR' => Xphp::$_lang['WEB_ERROR_FS_CREATE_LINUX_SNAPSHOT_ERROR'],
        'FS_DELETE_LINUX_SNAPSHOT_ERROR' => Xphp::$_lang['WEB_ERROR_FS_DELETE_LINUX_SNAPSHOT_ERROR'],
        'FS_FILE_CHANGED' => Xphp::$_lang['WEB_ERROR_FS_FILE_CHANGED'],
        'FS_FILE_BUFFER_CHECK_ERROR' => Xphp::$_lang['WEB_ERROR_FS_FILE_BUFFER_CHECK_ERROR'],
        'FS_DIR_NOT_EMPTY_ERROR' => Xphp::$_lang['WEB_ERROR_FS_DIR_NOT_EMPTY_ERROR'],
        'FS_ACL_GET_ERROR' => Xphp::$_lang['WEB_ERROR_FS_ACL_GET_ERROR'],
        'FS_ALC_TO_STRING_ERROR' => Xphp::$_lang['WEB_ERROR_FS_ALC_TO_STRING_ERROR'],
        'FS_GET_FILE_LINK_ERROR' => Xphp::$_lang['WEB_ERROR_FS_GET_FILE_LINK_ERROR'],
        'FS_SHORTCUT_CREATE_ERROR' => Xphp::$_lang['WEB_ERROR_FS_SHORTCUT_CREATE_ERROR'],
        'FS_ACL_RECOVERY_ERROR' => Xphp::$_lang['WEB_ERROR_FS_ACL_RECOVERY_ERROR'],
        'FS_FILE_TIME_SET_ERROR' => Xphp::$_lang['WEB_ERROR_FS_FILE_TIME_SET_ERROR'],
        'FS_FILE_MODE_SET_ERROR' => Xphp::$_lang['WEB_ERROR_FS_FILE_MODE_SET_ERROR'],
        'FS_USER_AND_GROUP_SET_ERROR' => Xphp::$_lang['WEB_ERROR_FS_USER_AND_GROUP_SET_ERROR'],
        'FS_USER_OR_GROUP_NOT_EXIST' => Xphp::$_lang['WEB_ERROR_FS_USER_OR_GROUP_NOT_EXIST'],
        'FS_USER_OR_GROUP_STRING_SAVE_ERROR' => Xphp::$_lang['WEB_ERROR_FS_USER_OR_GROUP_STRING_SAVE_ERROR'],
        'FS_SYMBOL_LINK_FILE_CREATE_ERROR' => Xphp::$_lang['WEB_ERROR_FS_SYMBOL_LINK_FILE_CREATE_ERROR'],
        'FS_HARD_LINK_FILE_CREATE_ERROR' => Xphp::$_lang['WEB_ERROR_FS_HARD_LINK_FILE_CREATE_ERROR'],
        'FS_READ_PACKET_NOT_USEFUL_ERROR' => Xphp::$_lang['WEB_ERROR_FS_READ_PACKET_NOT_USEFUL_ERROR'],
        'FS_READ_USER_OLD_ID_ERROR' => Xphp::$_lang['WEB_ERROR_FS_READ_USER_OLD_ID_ERROR'],
        'FS_SET_FILE_PERMISSION_ERROR' => Xphp::$_lang['WEB_ERROR_FS_SET_FILE_PERMISSION_ERROR'],
        'FS_PERMISSION_CANNOT_SET' => Xphp::$_lang['WEB_ERROR_FS_PERMISSION_CANNOT_SET'],
        'FS_ACL_CANNOT_SET' => Xphp::$_lang['WEB_ERROR_FS_ACL_CANNOT_SET'],
        'FS_SCAN_DIR_ERROR' => Xphp::$_lang['WEB_ERROR_FS_SCAN_DIR_ERROR'],
        'FS_GET_FILE_MODE_ERROR' => Xphp::$_lang['WEB_ERROR_FS_GET_FILE_MODE_ERROR'],
        'FS_ERROR_TASK_NOT_FINISH' => Xphp::$_lang['WEB_ERROR_FS_ERROR_TASK_NOT_FINISH'],
        'FS_ERROR_METABASE_WAS_LOCKED' => Xphp::$_lang['WEB_ERROR_FS_ERROR_METABASE_WAS_LOCKED'],
        'FS_ERROR_CONTAINER_WAS_LOCKED' => Xphp::$_lang['WEB_ERROR_FS_ERROR_CONTAINER_WAS_LOCKED'],

        'FS_SERVER_OBS_INVAILD_APPID' => Xphp::$_lang['WEB_ERROR_FS_SERVER_OBS_INVAILD_APPID'],
        'FS_SERVER_OBS_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_FS_SERVER_OBS_EXIST_ERROR'],
        'FS_SERVER_OBS_NOT_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_FS_SERVER_OBS_NOT_EXIST_ERROR'],
        'FS_SERVER_OBS_IS_BUSY_ERROR' => Xphp::$_lang['WEB_ERROR_FS_SERVER_OBS_IS_BUSY_ERROR'],
        'FS_SERVER_OBS_CONNECT_ERROR' => Xphp::$_lang['WEB_ERROR_OBS_CONNECT_ERROR'],
        'FS_SERVER_OBS_EMPTY_PREFIX_ERROR' => Xphp::$_lang['WEB_ERROR_OBS_EMPTY_PREFIX_ERROR'],

        // 对象存储
        'OBS_INVAILD_APPID' => Xphp::$_lang['WEB_ERROR_FS_SERVER_OBS_INVAILD_APPID'],
        'OBS_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_FS_SERVER_OBS_EXIST_ERROR'],
        'OBS_NOT_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_FS_SERVER_OBS_NOT_EXIST_ERROR'],
        'OBS_IS_BUSY_ERROR' => Xphp::$_lang['WEB_ERROR_FS_SERVER_OBS_IS_BUSY_ERROR'],
        'OBS_CONNECT_ERROR' => Xphp::$_lang['WEB_ERROR_OBS_CONNECT_ERROR'],
        'OBS_EMPTY_PREFIX_ERROR' => Xphp::$_lang['WEB_ERROR_OBS_EMPTY_PREFIX_ERROR'],
        'OBS_IS_NOT_AUTH' => Xphp::$_lang['WEB_ERROR_OBS_IS_NOT_AUTH'],
        'OBS_OBJECT_HAS_BEEN_MODIFIED' => Xphp::$_lang['WEB_ERROR_OBS_OBJECT_HAS_BEEN_MODIFIED'],
        'OBS_S3_NOT_INITIALIZED' => Xphp::$_lang['WEB_ERROR_OBS_S3_NOT_INITIALIZED'],
        'OBS_S3_ALREADY_INITIALIZED' => Xphp::$_lang['WEB_ERROR_OBS_S3_ALREADY_INITIALIZED'],
        'OBS_S3_HAS_BEEN_DESTROYED' => Xphp::$_lang['WEB_ERROR_OBS_S3_HAS_BEEN_DESTROYED'],

        'HADOOP_CURL_GETINFO_RESPONSE_CODE_ERROR' => Xphp::$_lang['WEB_ERROR_HADOOP_CURL_GETINFO_RESPONSE_CODE_ERROR'],
        'HADOOP_CURL_GETINFO_REDIRECT_URL_ERROR' => Xphp::$_lang['WEB_ERROR_HADOOP_CURL_GETINFO_REDIRECT_URL_ERROR'],
        'HADOOP_CURL_PERFORM_HTTP_PUT_ERROR' => Xphp::$_lang['WEB_ERROR_HADOOP_CURL_PERFORM_HTTP_PUT_ERROR'],
        'HADOOP_CURL_PERFORM_HTTP_DELETE_ERROR' => Xphp::$_lang['WEB_ERROR_HADOOP_CURL_PERFORM_HTTP_DELETE_ERROR'],
        'HADOOP_CURL_PERFORM_HTTP_HEAD_ERROR' => Xphp::$_lang['WEB_ERROR_HADOOP_CURL_PERFORM_HTTP_HEAD_ERROR'],
        'HADOOP_HADOOP_USER_SNAPSHOT_PATH_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_HADOOP_HADOOP_USER_SNAPSHOT_PATH_EXIST_ERROR'],
        'HADOOP_HADOOP_GET_SNAPSHOT_DIFF_REPORT_ERROR' => Xphp::$_lang['WEB_ERROR_HADOOP_HADOOP_GET_SNAPSHOT_DIFF_REPORT_ERROR'],
        'HADOOP_CLUSTER_UUID_NOT_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_HADOOP_CLUSTER_UUID_NOT_EXIST_ERROR'],
        'HADOOP_NAMENODE_NOT_MATCH_CLUSTER_ERROR' => Xphp::$_lang['WEB_ERROR_HADOOP_NAMENODE_NOT_MATCH_CLUSTER_ERROR'],
        'HADOOP_NAMENODE_CONNECT_HDFS_ERROR' => Xphp::$_lang['WEB_ERROR_HADOOP_NAMENODE_CONNECT_HDFS_ERROR'],
        'HADOOP_NAMENODE_IP_NOT_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_HADOOP_NAMENODE_IP_NOT_EXIST_ERROR'],
        'HADOOP_NAMENODE_IP_INVALID_ERROR' => Xphp::$_lang['WEB_ERROR_HADOOP_NAMENODE_IP_INVALID_ERROR'],
        'HADOOP_NAMENODE_NOT_AVAILABLE_ERROR' => Xphp::$_lang['WEB_ERROR_HADOOP_NAMENODE_NOT_AVAILABLE_ERROR'],
        'HADOOP_HDFS_DOWNLOAD_FILE_ERROR' => Xphp::$_lang['WEB_ERROR_HADOOP_HDFS_DOWNLOAD_FILE_ERROR'],
        'HADOOP_HDFS_UPLOAD_FILE_ERROR' => Xphp::$_lang['WEB_ERROR_HADOOP_HDFS_UPLOAD_FILE_ERROR'],
        'HADOOP_HDFS_CREATE_FILE_ERROR' => Xphp::$_lang['WEB_ERROR_HADOOP_HDFS_CREATE_FILE_ERROR'],
        'HADOOP_HDFS_CREATE_DIRECTORY_ERROR' => Xphp::$_lang['WEB_ERROR_HADOOP_HDFS_CREATE_DIRECTORY_ERROR'],
        'HADOOP_HDFS_SET_PERMISSION_ERROR' => Xphp::$_lang['WEB_ERROR_HADOOP_HDFS_SET_PERMISSION_ERROR'],
        'HADOOP_HDFS_GET_FILE_DIR_STATUS_ERROR' => Xphp::$_lang['WEB_ERROR_HADOOP_HDFS_GET_FILE_DIR_STATUS_ERROR'],
        'HADOOP_HDFS_GET_FILE_ACL_STATUS_ERROR' => Xphp::$_lang['WEB_ERROR_HADOOP_HDFS_GET_FILE_ACL_STATUS_ERROR'],
        'HADOOP_HDFS_LIST_FILE_DIR_STATUS_ERROR' => Xphp::$_lang['WEB_ERROR_HADOOP_HDFS_LIST_FILE_DIR_STATUS_ERROR'],
        'HADOOP_HDFS_GET_DISPLAY_DIR_LIST_ERROR' => Xphp::$_lang['WEB_ERROR_HADOOP_HDFS_GET_DISPLAY_DIR_LIST_ERROR'],
        'HADOOP_HDFS_ALLOW_SNAPSHOT_ERROR' => Xphp::$_lang['WEB_ERROR_HADOOP_HDFS_ALLOW_SNAPSHOT_ERROR'],
        'HADOOP_HDFS_DISALLOW_SNAPSHOT_ERROR' => Xphp::$_lang['WEB_ERROR_HADOOP_HDFS_DISALLOW_SNAPSHOT_ERROR'],
        'HADOOP_HDFS_CREATE_SNAPSHOT_ERROR' => Xphp::$_lang['WEB_ERROR_HADOOP_HDFS_CREATE_SNAPSHOT_ERROR'],
        'HADOOP_HDFS_DELETE_SNAPSHOT_ERROR' => Xphp::$_lang['WEB_ERROR_HADOOP_HDFS_DELETE_SNAPSHOT_ERROR'],
        'HADOOP_HDFS_RENAME_SNAPSHOT_ERROR' => Xphp::$_lang['WEB_ERROR_HADOOP_HDFS_RENAME_SNAPSHOT_ERROR'],
        'HADOOP_HDFS_SNAPSHOT_NOT_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_HADOOP_HDFS_SNAPSHOT_NOT_EXIST_ERROR'],
        'HADOOP_HDFS_SNAPSHOT_ALREADY_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_HADOOP_HDFS_SNAPSHOT_ALREADY_EXIST_ERROR'],
        'HADOOP_HDFS_LIST_SNAPSHOT_NAME_ERROR' => Xphp::$_lang['WEB_ERROR_HADOOP_HDFS_LIST_SNAPSHOT_NAME_ERROR'],
        'HADOOP_HDFS_GET_SNAPSHOT_DIR_LIST_ERROR' => Xphp::$_lang['WEB_ERROR_HADOOP_HDFS_GET_SNAPSHOT_DIR_LIST_ERROR'],
        'HADOOP_HDFS_SCAN_DIR_CHILD_ITEM_ERROR' => Xphp::$_lang['WEB_ERROR_HADOOP_HDFS_SCAN_DIR_CHILD_ITEM_ERROR'],
        'HADOOP_HDFS_GET_CLUSTER_ID_ERROR' => Xphp::$_lang['WEB_ERROR_HADOOP_HDFS_GET_CLUSTER_ID_ERROR'],
        'HADOOP_TASK_CLUSTER_BUSY_ERROR' => Xphp::$_lang['WEB_ERROR_HADOOP_TASK_CLUSTER_BUSY_ERROR'],
        'HADOOP_TASK_NAMENODE_BUSY_ERROR' => Xphp::$_lang['WEB_ERROR_HADOOP_TASK_NAMENODE_BUSY_ERROR'],
        'HADOOP_TASK_QUERY_FS_INDEX_ERROR' => Xphp::$_lang['WEB_ERROR_HADOOP_TASK_QUERY_FS_INDEX_ERROR'],
        'HADOOP_SET_RECORD_TO_HOSTS_ERROR' => Xphp::$_lang['WEB_ERROR_HADOOP_SET_RECORD_TO_HOSTS_ERROR'],
        'HADOOP_SET_RECORD_TO_KERBEROS_ERROR' => Xphp::$_lang['WEB_ERROR_HADOOP_SET_RECORD_TO_KERBEROS_ERROR'],
        'HADOOP_DELETE_RECORD_FROM_HOSTS_ERROR' => Xphp::$_lang['WEB_ERROR_HADOOP_DELETE_RECORD_FROM_HOSTS_ERROR'],
        'HADOOP_DELETE_RECORD_FROM_KERBEROS_ERROR' => Xphp::$_lang['WEB_ERROR_HADOOP_DELETE_RECORD_FROM_KERBEROS_ERROR'],
        'HADOOP_EXECUTE_CMD_ERROR' => Xphp::$_lang['WEB_ERROR_HADOOP_EXECUTE_CMD_ERROR'],
        'HADOOP_KERBEROS_KINIT_ERROR' => Xphp::$_lang['WEB_ERROR_HADOOP_KERBEROS_KINIT_ERROR'],
        'HADOOP_NOT_FIND_AIM_SNAPSHOT_ERROR' => Xphp::$_lang['WEB_ERROR_HADOOP_NOT_FIND_AIM_SNAPSHOT_ERROR'],
        'HADOOP_SET_FILE_TIME_ERROR' => Xphp::$_lang['WEB_ERROR_HADOOP_SET_FILE_TIME_ERROR'],
        'HADOOP_UNKNOWN_INC_FILE_TYPE' => Xphp::$_lang['WEB_ERROR_HADOOP_UNKNOWN_INC_FILE_TYPE'],
        'HADOOP_ERROR_MSG_EMPTY_ERROR' => Xphp::$_lang['WEB_ERROR_HADOOP_ERROR_MSG_EMPTY_ERROR'],
        'HADOOP_DATANODE_ERROR' => Xphp::$_lang['WEB_ERROR_HADOOP_DATANODE_ERROR'],

        'FS_FAST_INC_HEAD_HASH_MAP_ERROR' => Xphp::$_lang['WEB_ERROR_FS_FAST_INC_HEAD_HASH_MAP_ERROR'],
        'FS_FAST_INC_HASH_DIR_EMPTY_POINT_ERROR' => Xphp::$_lang['WEB_ERROR_FS_FAST_INC_HASH_DIR_EMPTY_POINT_ERROR'],
        'FS_FAST_INC_ERROR_NO_FATHER_NAME_CAN_CUT' => Xphp::$_lang['WEB_ERROR_FS_FAST_INC_ERROR_NO_FATHER_NAME_CAN_CUT'],
        'FS_FAST_INC_ERROR_ILLEGAL_DIFF_TYPE' => Xphp::$_lang['WEB_ERROR_FS_FAST_INC_ERROR_ILLEGAL_DIFF_TYPE'],
        'FS_FAST_INC_NOT_FIND_DEPEND_SNAPSHOT' => Xphp::$_lang['WEB_ERROR_FS_FAST_INC_NOT_FIND_DEPEND_SNAPSHOT'],
        'FS_FAST_INC_HEAD_DIR_NOT_PUT_INTO_MAP_FIRST' => Xphp::$_lang['WEB_ERROR_FS_FAST_INC_HEAD_DIR_NOT_PUT_INTO_MAP_FIRST'],
        'FS_FAST_INC_TAKE_DIFF_RESULT_ABNORMAL' => Xphp::$_lang['WEB_ERROR_FS_FAST_INC_TAKE_DIFF_RESULT_ABNORMAL'],
        'FS_FAST_INC_DIFF_WILDCARD' => Xphp::$_lang['WEB_ERROR_FS_FAST_INC_DIFF_WILDCARD'],
        'FS_FAST_INC_DIFF_BACKUP_OBJECT' => Xphp::$_lang['WEB_ERROR_FS_FAST_INC_DIFF_BACKUP_OBJECT'],
        'FS_FAST_INC_NAS_UNSUPPORT_SNAPSHOT' => Xphp::$_lang['WEB_ERROR_FS_FAST_INC_NAS_UNSUPPORT_SNAPSHOT'],
        'FS_FAST_INC_NAS_SHARE_INFO_NOT_EXIST' => Xphp::$_lang['WEB_ERROR_FS_FAST_INC_NAS_SHARE_INFO_NOT_EXIST'],
        'FS_FAST_INC_SNAPDIFF_NOT_OPEN' => Xphp::$_lang['WEB_ERROR_FS_FAST_INC_SNAPDIFF_NOT_OPEN'],
        'FS_FAST_INC_OCEANSTOR_UNDEFINE_ERROR_CODE' => Xphp::$_lang['WEB_ERROR_FS_FAST_INC_OCEANSTOR_UNDEFINE_ERROR_CODE'],

        'FS_HADOOP_CLUSTER_TASK_BUSY_ERROR' => Xphp::$_lang['WEB_ERROR_FS_HADOOP_CLUSTER_TASK_BUSY_ERROR'],
        'FS_HADOOP_CLUSTER_ALREADY_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_FS_HADOOP_CLUSTER_ALREADY_EXIST_ERROR'],
        'FS_HADOOP_NAMENODES_OFFLINE_ERROR' => Xphp::$_lang['WEB_ERROR_FS_HADOOP_NAMENODES_OFFLINE_ERROR'],
        'FS_HADOOP_NAMENODES_ALREADY_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_FS_HADOOP_NAMENODES_ALREADY_EXIST_ERROR'],
        'FS_HADOOP_NAMENODE_CONNECT_HDFS_ERROR' => Xphp::$_lang['WEB_ERROR_FS_HADOOP_NAMENODE_CONNECT_HDFS_ERROR'],
        'FS_HADOOP_NAMENODE_NOT_MATCH_CLUSTER_ERROR' => Xphp::$_lang['WEB_ERROR_FS_HADOOP_NAMENODE_NOT_MATCH_CLUSTER_ERROR'],
        'FS_HADOOP_NAMENODE_USERNAME_INVALID_ERROR' => Xphp::$_lang['WEB_ERROR_FS_HADOOP_NAMENODE_USERNAME_INVALID_ERROR'],
        'FS_HADOOP_HDFS_STORAGE_SPACE_NOT_ENOUGH_ERROR' => Xphp::$_lang['WEB_ERROR_FS_HADOOP_HDFS_STORAGE_SPACE_NOT_ENOUGH_ERROR'],
        'FS_HADOOP_NEED_ONE_ONLINE_NAMENODE_ERROR' => Xphp::$_lang['WEB_ERROR_FS_HADOOP_NEED_ONE_ONLINE_NAMENODE_ERROR'],
        //******文件复制错误定义******//
        'SYNC_ERROR_UNKNOWN' => Xphp::$_lang['WEB_ERROR_SYNC_ERROR_UNKNOWN'],
        'SYNC_ERROR_LOAD_SCAN_FILE_PATHNAME_ERROR' => Xphp::$_lang['WEB_ERROR_SYNC_ERROR_LOAD_SCAN_FILE_PATHNAME_ERROR'],
        'SYNC_ERROR_CHECK_SCAN_FILE_ERROR' => Xphp::$_lang['WEB_ERROR_SYNC_ERROR_CHECK_SCAN_FILE_ERROR'],
        'SYNC_ERROR_SEND_THREAD_START_ERROR' => Xphp::$_lang['WEB_ERROR_SYNC_ERROR_SEND_THREAD_START_ERROR'],
        'SYNC_ERROR_TRANSRPOT_READ_PACEKT_ERROR' => Xphp::$_lang['WEB_ERROR_SYNC_ERROR_TRANSRPOT_READ_PACEKT_ERROR'],
        'SYNC_ERROR_GET_FILE_SIZE_ERROR' => Xphp::$_lang['WEB_ERROR_SYNC_ERROR_GET_FILE_SIZE_ERROR'],
        'SYNC_ERROR_PACKET_RECACHE_ERROR' => Xphp::$_lang['WEB_ERROR_SYNC_ERROR_PACKET_RECACHE_ERROR'],
        'SYNC_ERROR_ITEM_NOT_FOUND' => Xphp::$_lang['WEB_ERROR_SYNC_ERROR_ITEM_NOT_FOUND'],
        'SYNC_ERROR_BUFFER_FULL_ERROR' => Xphp::$_lang['WEB_ERROR_SYNC_ERROR_BUFFER_FULL_ERROR'],
        'SYNC_ERROR_MD5_OBJECT_CHANGE' => Xphp::$_lang['WEB_ERROR_SYNC_ERROR_MD5_OBJECT_CHANGE'],
        'SYNC_ERROR_MD5_EMPTY_RESULT' => Xphp::$_lang['WEB_ERROR_SYNC_ERROR_MD5_EMPTY_RESULT'],
        'SYNC_ERROR_UPDATE_STRUCT_ERROR' => Xphp::$_lang['WEB_ERROR_SYNC_ERROR_UPDATE_STRUCT_ERROR'],
        'SYNC_ERROR_TASK_MODE_ERROR' => Xphp::$_lang['WEB_ERROR_SYNC_ERROR_TASK_MODE_ERROR'],
        'SYNC_ERROR_SCAN_NOT_FINISH' => Xphp::$_lang['WEB_ERROR_SYNC_ERROR_SCAN_NOT_FINISH'],
        'SYNC_ERROR_META_SAVE_NOT_INIT_ERROR' => Xphp::$_lang['WEB_ERROR_SYNC_ERROR_META_SAVE_NOT_INIT_ERROR'],
        'SYNC_ERROR_COMPARE_SAVE_DIR_PATH_INVAILD' => Xphp::$_lang['WEB_ERROR_SYNC_ERROR_COMPARE_SAVE_DIR_PATH_INVAILD'],
        'SYNC_ERROR_THREAD_NOT_RUNNING' => Xphp::$_lang['WEB_ERROR_SYNC_ERROR_THREAD_NOT_RUNNING'],
        'SYNC_ERROR_TASK_NOT_FINISH' => Xphp::$_lang['WEB_ERROR_SYNC_ERROR_TASK_NOT_FINISH'],
        'SYNC_ERROR_META_FILE_UNCOMPLETE' => Xphp::$_lang['WEB_ERROR_SYNC_ERROR_META_FILE_UNCOMPLETE'],
        'SYNC_ERROR_OFFSET_OVER_FILE_SIZE' => Xphp::$_lang['WEB_ERROR_SYNC_ERROR_OFFSET_OVER_FILE_SIZE'],
        'SYNC_ERROR_FILE_NOT_SAME_TPYE' => Xphp::$_lang['WEB_ERROR_SYNC_ERROR_FILE_NOT_SAME_TPYE'],
        'SYNC_ERROR_FILE_NOT_SAME_SIZE' => Xphp::$_lang['WEB_ERROR_SYNC_ERROR_FILE_NOT_SAME_SIZE'],
        'SYNC_ERROR_BIG_FILE_HANDLE_BE_DELETE' => Xphp::$_lang['WEB_ERROR_SYNC_ERROR_BIG_FILE_HANDLE_BE_DELETE'],
        'SYNC_ERROR_PACKET_PUT_INTO_NET_LIST_ERROR' => Xphp::$_lang['WEB_ERROR_SYNC_ERROR_PACKET_PUT_INTO_NET_LIST_ERROR'],
        'SYNC_ERROR_FILE_RENAME_ERROR' => Xphp::$_lang['WEB_ERROR_SYNC_ERROR_FILE_RENAME_ERROR'],
        'SYNC_ERROR_SURE_HEAD_NOT_BE_FOUND' => Xphp::$_lang['WEB_ERROR_SYNC_ERROR_SURE_HEAD_NOT_BE_FOUND'],
        'SYNC_ERROR_SURE_HEAD_NOT_USEFUL' => Xphp::$_lang['WEB_ERROR_SYNC_ERROR_SURE_HEAD_NOT_USEFUL'],
        'SYNC_ERROR_PACKET_NOT_BELONG_TO_SELF' => Xphp::$_lang['WEB_ERROR_SYNC_ERROR_PACKET_NOT_BELONG_TO_SELF'],
        'SYNC_ERROR_NO_CACHE_PLACE' => Xphp::$_lang['WEB_ERROR_SYNC_ERROR_NO_CACHE_PLACE'],
        'SYNC_ERROR_DIR_META_NOT_USEFUL' => Xphp::$_lang['WEB_ERROR_SYNC_ERROR_DIR_META_NOT_USEFUL'],
        'SYNC_ERROR_PACKET_NOT_PUSH' => Xphp::$_lang['WEB_ERROR_SYNC_ERROR_PACKET_NOT_PUSH'],
        'SYNC_ERRPR_EMPTY_POINT' => Xphp::$_lang['WEB_ERROR_SYNC_ERRPR_EMPTY_POINT'],
        'SYNC_ERROR_INVALIED_QUEUE_ERROR' => Xphp::$_lang['WEB_ERROR_SYNC_ERROR_INVALIED_QUEUE_ERROR'],
        'SYNC_ERROR_INVALID_FILE_TYPE' => Xphp::$_lang['WEB_ERROR_SYNC_ERROR_INVALID_FILE_TYPE'],
        'SYNC_ERROR_INIT_WINDOWS_CREATE_INSTANCE' => Xphp::$_lang['WEB_ERROR_SYNC_ERROR_INIT_WINDOWS_CREATE_INSTANCE'],
        'SYNC_ERROR_INIT_WINDOWS_SHELL_QUERY_INTER_FACE' => Xphp::$_lang['WEB_ERROR_SYNC_ERROR_INIT_WINDOWS_SHELL_QUERY_INTER_FACE'],
        'SYNC_ERROR_NOT_SUPPORT_CREATE_SNAPSHOT' => Xphp::$_lang['WEB_ERROR_SYNC_ERROR_NOT_SUPPORT_CREATE_SNAPSHOT'],
        'SYNC_ERROR_SPEED_OPERATOR_NOT_EXIST' => Xphp::$_lang['WEB_ERROR_SYNC_ERROR_SPEED_OPERATOR_NOT_EXIST'],
        'SYNC_ERROR_COMPARE_RESULT_UNAVAILABLE' => Xphp::$_lang['WEB_ERROR_SYNC_ERROR_COMPARE_RESULT_UNAVAILABLE'],
        //******数据库模块错误定义******//
        'DATABASE_UNKNOWN_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_UNKNOWN_ERROR'], 					// database unknown error
        'DATABASE_DB_INSTANCE_NOT_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_DB_INSTANCE_NOT_EXIST_ERROR'],			// the db instance is not exist error
        'DATABASE_DB_LIST_NOT_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_DB_LIST_NOT_EXIST_ERROR'],				// the db list is not exist error
        'DATABASE_FIND_BACKUP_FILE_ID_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_FIND_BACKUP_FILE_ID_ERROR'],				// find backup file id error
        'DATABASE_NOT_SUPPORT_DB_TYPE_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_NOT_SUPPORT_DB_TYPE_ERROR'],				// not support database type
        'DATABASE_CREATE_BACKUP_DIR_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_CREATE_BACKUP_DIR_ERROR'],				// create backup directory error
        'DATABASE_BUILD_BACKUP_DB_LIST_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_BUILD_BACKUP_DB_LIST_ERROR'],			// build backup db list error
        'DATABASE_BUILD_RECOVERY_DB_LIST_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_BUILD_RECOVERY_DB_LIST_ERROR'],			// build recovery db list error
        'DATABASE_LATEST_TIMEPOINT_NOT_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_LATEST_TIMEPOINT_NOT_EXIST_ERROR'],		// the latest timepoint is not exist error
        'DATABASE_BACKUP_CHAIN_IS_USING_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_BACKUP_CHAIN_IS_USING_ERROR'],			// the backup chain is using error
        'DATABASE_SAVE_SELF_EXPLAN_FILE_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_SAVE_SELF_EXPLAN_FILE_ERROR'],			// save self explan file error
        'DATABASE_OPEN_BACKUP_FILE_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_OPEN_BACKUP_FILE_ERROR'],				// open backup file error
        'DATABASE_OPEN_BITMAP_FILE_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_OPEN_BITMAP_FILE_ERROR'],				// open bitmap file error
        'DATABASE_WRITE_BACKUP_FILE_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_WRITE_BACKUP_FILE_ERROR'],				// write backup file error
        'DATABASE_WRITE_BITMAP_FILE_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_WRITE_BITMAP_FILE_ERROR'],				// write bitmap file error
        'DATABASE_READ_BACKUP_FILE_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_READ_BACKUP_FILE_ERROR'],				// read backup file error
        'DATABASE_READ_BITMAP_FILE_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_READ_BITMAP_FILE_ERROR'],				// read bitmap file error
        'DATABASE_COMPRESS_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_COMPRESS_ERROR'],						// compress error
        'DATABASE_DECOMPRESS_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_DECOMPRESS_ERROR'],						// decompress error
        'DATABASE_CONTAINER_FULL_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_CONTAINER_FULL_ERROR'],					// the container is full error
        'DATABASE_GET_RECOVERY_TOTAL_SIZE_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_GET_RECOVERY_TOTAL_SIZE_ERROR'],			// database get recovery total size error


        'DATABASE_SCAN_INSTANCE_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_SCAN_INSTANCE_ERROR'],					// scan instance error
        'DATABASE_CONNECT_DB_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_CONNECT_DB_ERROR'],						// connect to database error
        'DATABASE_GET_DB_VERSION_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_GET_DB_VERSION_ERROR'],					// get db version error
        'DATABASE_GET_DBID_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_GET_DBID_ERROR'],						// get dbid error
        'DATABASE_DB_NOT_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_DB_NOT_EXIST_ERROR'],					// the database not exist error
        'DATABASE_SCAN_DB_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_SCAN_DB_ERROR'],							// scan db error
        'DATABASE_SCAN_TABLE_SPACE_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_SCAN_TABLE_SPACE_ERROR'],				// scan table space error
        'DATABASE_SCAN_PDB_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_SCAN_PDB_ERROR'],						// scan pdb error
        'DATABASE_CHECK_DB_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_CHECK_DB_ERROR'],						// check db error
        'DATABASE_GET_RECOVERY_MODE_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_GET_RECOVERY_MODE_ERROR'],				// get recovery mode error
        'DATABASE_SET_RECOVERY_MODE_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_SET_RECOVERY_MODE_ERROR'],				// set recovery mode error
        'DATABASE_CHECK_DB_NAME_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_CHECK_DB_NAME_EXIST_ERROR'],				// check db name exist error
        'DATABASE_CREATE_DB_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_CREATE_DB_ERROR'],						// create db error
        'DATABASE_DELETE_DB_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_DELETE_DB_ERROR'],						// delete db error
        'DATABASE_SET_SINGLE_USER_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_SET_SINGLE_USER_ERROR'],					// set single user error
        'DATABASE_SET_MULTI_USER_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_SET_MULTI_USER_ERROR'],					// set multi user error
        'DATABASE_CHECK_ARCHIVE_LOG_OPEN_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_CHECK_ARCHIVE_LOG_OPEN_ERROR'],			// check archive log open error
        'DATABASE_CHECK_ARCHIVE_LOG_IN_SHARE_STORAGE_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_CHECK_ARCHIVE_LOG_IN_SHARE_STORAGE_ERROR'],	//check archive log in share storage error
        'DATABASE_CHECK_BLOCK_CHANGE_TRACKING_ENABLE_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_CHECK_BLOCK_CHANGE_TRACKING_ENABLE_ERROR'],	//check blcok change tracking enable error
        'DATABASE_OPEN_BLOCK_CHANGE_TRACKING_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_OPEN_BLOCK_CHANGE_TRACKING_ERROR'],		//open block change tracking error
        'DATABASE_GET_CURRENT_INCARNATION_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_GET_CURRENT_INCARNATION_ERROR'],			//get current incarnation error
        'DATABASE_GET_CURRENT_SCN_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_GET_CURRENT_SCN_ERROR'],					//get current scn error
        'DATABASE_GET_ARCHIVE_LOG_NEWEST_SEQUENCE_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_GET_ARCHIVE_LOG_NEWEST_SEQUENCE_ERROR'], //get archive log newest sequence error
        'DATABASE_SQL_ALLOCATE_HANDLE_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_SQL_ALLOCATE_HANDLE_ERROR'],			    // sql allocate handle error
        'DATABASE_SQL_SET_ENV_ATTR_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_SQL_SET_ENV_ATTR_ERROR'],				// sql set env attr error
        'DATABASE_OCI_ENV_CREATE_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_OCI_ENV_CREATE_ERROR'],					// oci env create error
        'DATABASE_OCI_HANDLE_ALLOC_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_OCI_HANDLE_ALLOC_ERROR'],				// oci handle allocate error
        'DATABASE_MASTER_DATABASE_NOT_SUPPORT_DIFF_OR_LOG_BACKUP' => Xphp::$_lang['WEB_ERROR_DATABASE_MASTER_DATABASE_NOT_SUPPORT_DIFF_OR_LOG_BACKUP'],		// master database not support diff or log backup
        'DATABASE_SIMPLE_MODE_NOT_SUPPORT_LOG_BACKUP' => Xphp::$_lang['WEB_ERROR_DATABASE_SIMPLE_MODE_NOT_SUPPORT_LOG_BACKUP'],					// simple mode not support log backup
        'DATABASE_INCLUDE_UNKONWN_BACKUPSET_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_INCLUDE_UNKONWN_BACKUPSET_ERROR'],						// include other unknown backupset error
        'DATABASE_GET_DB_BACKUPSET_INFO_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_GET_DB_BACKUPSET_INFO_ERROR'],							// get database 's backupset info error
        'DATABASE_GET_DB_BACKUPSET_BACKUP_START_DATE_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_GET_DB_BACKUPSET_BACKUP_START_DATE_ERROR'],			    // get db backupset backup start date error
        'DATABASE_VDI_NOT_INIT_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_VDI_NOT_INIT_ERROR'],									// vdi not init error
        'DATABASE_NOT_OPEN_ARCHIVE_LOG_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_NOT_OPEN_ARCHIVE_LOG_ERROR'],							// not open archive log error
        'DATABASE_GET_RMAN_BACKUP_JOB_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_GET_RMAN_BACKUP_JOB_ERROR'],								// get rman backup job information error
        'DATABASE_INCLUDE_UNKONWN_RMAN_BACKUP_JOB_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_INCLUDE_UNKONWN_RMAN_BACKUP_JOB_ERROR'],					// include unknown rman backup job error

        'DATABASE_GET_RMAN_BACKUP_JOB_START_TIME_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_GET_RMAN_BACKUP_JOB_START_TIME_ERROR'],					// get rman backup job start time error
        'DATABASE_GET_SQL_DB_CONFIG_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_GET_SQL_DB_CONFIG_ERROR'],												// get sql db config error


        //mysql error code
        'DATABASE_CONNECT_SERVER_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_CONNECT_SERVER_ERROR'],						//connect to mysql server failed
        'DATABASE_DO_QUERY_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_DO_QUERY_ERROR'],							//mysql do query error
        'DATABASE_INFO_NOT_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_INFO_NOT_EXIST_ERROR'],						//xtrabackup_info file not exists
        'DATABASE_ITEM_NOT_FOUND_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_ITEM_NOT_FOUND_ERROR'],						//not found item in file
        'DATABASE_LAST_TIMEPOINT_NOT_EXIST' => Xphp::$_lang['WEB_ERROR_DATABASE_LAST_TIMEPOINT_NOT_EXIST'],					//last timepoint not exist
        'DATABASE_BIN_LOG_NOT_OPEN' => Xphp::$_lang['WEB_ERROR_DATABASE_BIN_LOG_NOT_OPEN'],							//bin log is off
        'DATABASE_UNKNOWN_BACKUP_MODE' => Xphp::$_lang['WEB_ERROR_DATABASE_UNKNOWN_BACKUP_MODE'],						//unknown backup mode
        'DATABASE_XTRABACKUP_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_XTRABACKUP_ERROR'],							//xtrabackup backup error
        'DATABASE_GET_RECOVERY_INFO_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_GET_RECOVERY_INFO_ERROR'],					//get db recovery info from db
        'DATABASE_DIR_NOT_EMPTY_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_DIR_NOT_EMPTY_ERROR'],						//dir not empty error
        'DATABASE_PREPARE_BACKUP_DATA_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_PREPARE_BACKUP_DATA_ERROR'],					//xtrabackup prepare backup data error
        'DATABASE_GET_BACKUP_INFO_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_GET_BACKUP_INFO_ERROR'],						//get info which mysql backup need error

        //add for dm
        'DATABASE_INSTANCE_IS_RUNNING_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_INSTANCE_IS_RUNNING_ERROR'],					//instance is running (实例正在运行)
        'DATABASE_GET_SQL_RESULT_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_GET_SQL_RESULT_ERROR'],						//get sql result error (获取数据库执行语句结果错误)
        'DATABASE_INVALID_INSTALL_DB_USER' => Xphp::$_lang['WEB_ERROR_DATABASE_INVALID_INSTALL_DB_USER'],					//invalid install database user (无效的安装数据库用户名)
        'DATABASE_FIND_SBT_PATH_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_FIND_SBT_PATH_ERROR'],						//find sbt path error (获取sbt 的路径错误)
        'DATABASE_INVALID_USERNAME_PASSWD_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_INVALID_USERNAME_PASSWD_ERROR'],				//invalid user name or password error (无效的用户名/密码)
        'DATABASE_HAVE_NO_INSTANCE_WERE_SCANED' => Xphp::$_lang['WEB_ERROR_DATABASE_HAVE_NO_INSTANCE_WERE_SCANED'],				//have no instance were scaned (没有获取到实例)
        'DATABASE_NOT_RUNNING_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_NOT_RUNNING_ERROR'],							//database not running (数据库没有运行)

        //add for psql
        'DATABASE_OPEN_PIPE_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_OPEN_PIPE_ERROR'],							//open pipe error (打开管道文件失败)
        'DATABASE_INIT_OR_ALLOC_DCI_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_INIT_OR_ALLOC_DCI_ERROR'],					//init or alloc dci error (初始化/分配DCI句柄失败)
        'DATABASE_SET_SAFELY_CONFIG_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_SET_SAFELY_CONFIG_ERROR'],					//set safely config error (设置安全配置失败)
        'DATABASE_BEGIN_CONNECT_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_BEGIN_CONNECT_ERROR'],						//begin connect error (开始连接数据库失败)
        'DATABASE_EXEC_CMD_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_EXEC_CMD_ERROR'],							//exec cmd error (执行数据库语句失败)
        'DATABASE_OPEN_SYS_SERVICE_CONF_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_OPEN_SYS_SERVICE_CONF_ERROR'],				//open service config (打开服务配置文件失败)
        'DATABASE_GET_USER_OF_CLUSTER_PATH_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_GET_USER_OF_CLUSTER_PATH_ERROR'],			//get user of cluster path error (获取实例集簇路径的用户名失败)
        'DATABASE_GET_GROUP_OF_CLUSTER_PATH_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_GET_GROUP_OF_CLUSTER_PATH_ERROR'],			//get group of cluster path error (获取实例集簇路径的归属组失败)
        'DATABASE_ARCHIVE_COMMAND_IS_DISABLE_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_ARCHIVE_COMMAND_IS_DISABLE_ERROR'],			//archive command is disable (归档命令没有开启)
        'DATABASE_WAL_LEVEL_TOO_LOW_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_WAL_LEVEL_TOO_LOW_ERROR'],					//wal level too low error (wal 级别太低)
        'DATABASE_CREATE_PIPE_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_CREATE_PIPE_ERROR'],							//create pipe error (创建管道失败)
        'DATABASE_TAR_DATABASE_CLUSTER_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_TAR_DATABASE_CLUSTER_ERROR'],				//tar datbase cluster error (打包实例集簇文件失败)
        'DATABASE_DELETE_PIPE_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_DELETE_PIPE_ERROR'],							//delete pipe error (删除管道失败)
        'DATABASE_FIND_ARCHIVE_PATH_BY_CLUSTER_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_FIND_ARCHIVE_PATH_BY_CLUSTER_ERROR'],		//find archive path by cluster error (通过集簇路径获取归档路径失败)
        'DATABASE_ANALYSIS_ARCHIVE_PATH_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_ANALYSIS_ARCHIVE_PATH_ERROR'],				//analysis archive path error (解析归档路径失败)
        'DATABASE_CHECK_OR_CREATE_RESTORE_DESTINE_PATH_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_CHECK_OR_CREATE_RESTORE_DESTINE_PATH_ERROR'],//check or create restore destin path error (检测/创建指定恢复路径失败)
        'DATABASE_PORT_IS_OCCUPIED' => Xphp::$_lang['WEB_ERROR_DATABASE_PORT_IS_OCCUPIED'],							//port is occupied (端口被占用)
        'DATABASE_CHECK_LATESE_WAL_LOG_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_CHECK_LATESE_WAL_LOG_ERROR'],				//check latest wal log error (检测最近的归档日志错误)

        'DATABASE_DESTIN_FOLDER_IS_NOT_EMPTY_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_DESTIN_FOLDER_IS_NOT_EMPTY_ERROR'],			//destin path is not empty error (指定恢复的文件夹不为空)
        'DATABASE_CHECK_CURRENT_WAL_LOG_STAT_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_CHECK_CURRENT_WAL_LOG_STAT_ERROR'],			//check current wal log stat error (检测当前的wal 日志状态错误)
        'DATABASE_DESTIN_ARCHIVE_PATH_IS_NOT_EMPTY_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_DESTIN_ARCHIVE_PATH_IS_NOT_EMPTY_ERROR'],	//destin archive path is not empty (指定的归档路径不为空)
        'DATABASE_PARTITION_INSUFFICIENT_MARGIN' => Xphp::$_lang['WEB_ERROR_DATABASE_PARTITION_INSUFFICIENT_MARGIN'],				//partion insufficient margin (分区余量不足)
        'DATABASE_GET_STORE_ALARM_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_GET_STORE_ALARM_ERROR'],						//get store alarm error (获取存储告警提示失败)
        'DATABASE_CHECK_INSTALL_DB_PATH_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_CHECK_INSTALL_DB_PATH_ERROR'],			//check install db path error (检测数据库安装目录失败)

        'DATABASE_VERSION_NOT_SUPPORT_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_VERSION_NOT_SUPPORT_ERROR'],     	//database version not support(不支持的数据库版本)
        'DATABASE_LISTEN_IP_INVALID_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_LISTEN_IP_INVALID_ERROR'],     	//database listen ip invalid error
        'DATABASE_GET_LISTEN_PORT_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_GET_LISTEN_PORT_ERROR'],      		//get database listen port error
        'DATABASE_GET_INSTALL_DB_USERNAME_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_GET_INSTALL_DB_USERNAME_ERROR'],   //get install db username error
        'DATABASE_IS_ALREADY_RECOVERD_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_IS_ALREADY_RECOVERD_ERROR'],     //database is already recoverd error
        'DATABASE_SPECIFIED_TIME_IS_AFTER_THE_VALID_LOG' => Xphp::$_lang['WEB_ERROR_DATABASE_SPECIFIED_TIME_IS_AFTER_THE_VALID_LOG'],    //the specified time is after the valid log(指定恢复时间在有效日志之后)
        'DATABASE_START_INSTANCE_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_START_INSTANCE_ERROR'],
        'DATABASE_BINLOG_FILE_NOT_EXIST' => Xphp::$_lang['WEB_ERROR_DATABASE_BINLOG_FILE_NOT_EXIST'],

        'DATABASE_WITHOUT_SYSDBA_OR_SYSBACKUP_USER_PERMISION_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_WITHOUT_SYSDBA_OR_SYSBACKUP_USER_PERMISION_ERROR'],
        'DATABASE_WITHOUT_DBA_USER_PERMISION_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_WITHOUT_DBA_USER_PERMISION_ERROR'],
        'DATABASE_ROLE_NOT_BELONG_CLUSTER' => Xphp::$_lang['WEB_ERROR_DATABASE_ROLE_NOT_BELONG_CLUSTER'],
        'DATABASE_WITHOUT_SYSADMIN_USER_PERMISION_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_WITHOUT_SYSADMIN_USER_PERMISION_ERROR'],
        'DATABASE_NODES_LESS_THAN_CLUSTER' => Xphp::$_lang['WEB_ERROR_DATABASE_NODES_LESS_THAN_CLUSTER'],
        'DATABASE_RECOVERY_PATH_HAVE_NO_PERMISSION' => Xphp::$_lang['WEB_ERROR_DATABASE_RECOVERY_PATH_HAVE_NO_PERMISSION'],
        'DATABASE_NEED_RERUN_RECOVERY' => Xphp::$_lang['WEB_ERROR_DATABASE_NEED_RERUN_RECOVERY'],

        'DATABASE_INSTANCE_SERVICE_NOT_RUNNING_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_INSTANCE_SERVICE_NOT_RUNNING_ERROR'],
        'DATABASE_DB_NOT_RUNNING_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_DB_NOT_RUNNING_ERROR'],
        'DATABASE_CONFIGURE_DB_USING_BACKINT_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_CONFIGURE_DB_USING_BACKINT_ERROR'],
        'DATABASE_CONFIGURE_DB_BACKINT_CHANNELS_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_CONFIGURE_DB_BACKINT_CHANNELS_ERROR'],
        'DATABASE_CONFIGURE_DB_AUTO_LOG_BACKUP_INTERVAL_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_CONFIGURE_DB_AUTO_LOG_BACKUP_INTERVAL_ERROR'],
        'DATABASE_NOT_FIND_MASTER_NODE_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_NOT_FIND_MASTER_NODE_ERROR'],
        'DATABASE_SNAPSHOT_CONTROLFILE_NOT_IN_SHARE_STORAGE' => Xphp::$_lang['WEB_ERROR_DATABASE_SNAPSHOT_CONTROLFILE_NOT_IN_SHARE_STORAGE'],
        'DATABASE_ARCHIVE_LOG_LOST' => Xphp::$_lang['WEB_ERROR_DATABASE_ARCHIVE_LOG_LOST'],
        'DATABASE_DATAPATH_HAS_CHANGED_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_DATAPATH_HAS_CHANGED_ERROR'],
        'DATABASE_CLUSTER_SERVER_NOT_RUNNING' => Xphp::$_lang['WEB_ERROR_DATABASE_CLUSTER_SERVER_NOT_RUNNING'],
        'DATABASE_INCARNATION_CHNAGED_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_INCARNATION_CHNAGED_ERROR'],
        'DATABASE_LAST_BACKUP_WAS_NON_BACKINT' => Xphp::$_lang['WEB_ERROR_DATABASE_LAST_BACKUP_WAS_NON_BACKINT'],
        'DATABASE_LOG_CANNOT_BE_DOWNGRADED_TO_COMPLETE' => Xphp::$_lang['WEB_ERROR_DATABASE_LOG_CANNOT_BE_DOWNGRADED_TO_COMPLETE'],
        'DATABASE_TIKV_SERVER_NOT_WORKING_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_TIKV_SERVER_NOT_WORKING_ERROR'],
        'DATABASE_GET_BACKUP_TIMESTAMP_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_GET_BACKUP_TIMESTAMP_ERROR'],
        'DATABASE_BR_TASK_FAILED_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_BR_TASK_FAILED_ERROR'],
        'DATABASE_RESULT_OF_QUERY_IS_EMPTY_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_RESULT_OF_QUERY_IS_EMPTY_ERROR'],
        'DATABASE_INITIALIZE_CLIENT_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_INITIALIZE_CLIENT_ERROR'],
        'DATABASE_QUERRY_MESSAGE_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_QUERRY_MESSAGE_ERROR'],
        'DATABASE_INSTANCE_DEPENDENT_PORT_NOT_EXIST' => Xphp::$_lang['WEB_ERROR_DATABASE_INSTANCE_DEPENDENT_PORT_NOT_EXIST'],
        'DATABASE_NOT_SUPPORT_TO_BACKUP_LOG' => Xphp::$_lang['WEB_ERROR_DATABASE_NOT_SUPPORT_TO_BACKUP_LOG'],
        'DATABASE_RUNNING_INSTANCE_HAS_CHANGED' => Xphp::$_lang['WEB_ERROR_DATABASE_RUNNING_INSTANCE_HAS_CHANGED'],
        'DATABASE_JOURNAL_IS_NOT_ENABLED' => Xphp::$_lang['WEB_ERROR_DATABASE_JOURNAL_IS_NOT_ENABLED'],
        'DATABASE_BACKUP_PARTITION_HAS_CHANGED' => Xphp::$_lang['WEB_ERROR_DATABASE_BACKUP_PARTITION_HAS_CHANGED'],
        'DATABASE_STOP_BALANCER_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_STOP_BALANCER_ERROR'],
        'DATABASE_START_BALANCER_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_START_BALANCER_ERROR'],
        'DATABASE_FIND_KEY_IN_JSON_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_FIND_KEY_IN_JSON_ERROR'],
        'DATABASE_SPLIT_STRING_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_SPLIT_STRING_ERROR'],
        'DATABASE_JOURNAL_PATH_IS_NOT_EXIST' => Xphp::$_lang['WEB_ERROR_DATABASE_JOURNAL_PATH_IS_NOT_EXIST'],
        'DATABASE_TIMESTAMP_IS_NOT_EXIST' => Xphp::$_lang['WEB_ERROR_DATABASE_TIMESTAMP_IS_NOT_EXIST'],
        'DATABASE_SPECIFIED_PATH_IS_NOT_EXIST' => Xphp::$_lang['WEB_ERROR_DATABASE_SPECIFIED_PATH_IS_NOT_EXIST'],
        'DATABASE_GET_FILE_SYSTEM_TYPE_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_GET_FILE_SYSTEM_TYPE_ERROR'],
        'DATABASE_FIND_RECOVERY_OPLOG_FILE_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_FIND_RECOVERY_OPLOG_FILE_ERROR'],
        'DATABASE_NOT_FIND_THE_AVAILABLE_COMPLETE_INSTANCE' => Xphp::$_lang['WEB_ERROR_DATABASE_NOT_FIND_THE_AVAILABLE_COMPLETE_INSTANCE'],
        'DATABASE_BACKUP_CHAIN_IS_MERGING_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_BACKUP_CHAIN_IS_MERGING_ERROR'],
        'DATABASE_COPY_BITMAP_FILE_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_COPY_BITMAP_FILE_ERROR'],
        'DATABASE_MERGE_TIMEPOINT_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_MERGE_TIMEPOINT_ERROR'],
        'DATABASE_DEDUPE_BLOCK_SIZE_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_DEDUPE_BLOCK_SIZE_ERROR'],
        'DATABASE_UPDATE_BACKUP_TOTAL_SIZE_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_UPDATE_BACKUP_TOTAL_SIZE_ERROR'],
        'DATABASE_INC_AND_DIFF_BACKUP_POINT_IN_BACKUP_CHAIN' => Xphp::$_lang['WEB_ERROR_DATABASE_INC_AND_DIFF_BACKUP_POINT_IN_BACKUP_CHAIN'],
        'DATABASE_BACKUP_CLUSTER_NODE_CHANGED' => Xphp::$_lang['WEB_ERROR_DATABASE_BACKUP_CLUSTER_NODE_CHANGED'],
        'DATABASE_HANA_DESTROY_TRANSFER_CHANNEL_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_HANA_DESTROY_TRANSFER_CHANNEL_ERROR'],
        'DATABASE_LOAD_LIBRARY_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_LOAD_LIBRARY_ERROR'],
        'DATABASE_CURRENT_TIMEPOINT_HAS_NO_DEPEND_TIMEPOINT_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_CURRENT_TIMEPOINT_HAS_NO_DEPEND_TIMEPOINT_ERROR'],
        'DATABASE_TARGET_PATH_IS_NOT_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_TARGET_PATH_IS_NOT_EXIST_ERROR'],
        'DATABASE_TARGET_PATH_IS_EMPTY_ERROR' => Xphp::$_lang['WEB_ERROR_DATABASE_TARGET_PATH_IS_EMPTY_ERROR'],
        //主机操作系统模块
        'OS_SERVER_GET_BACKUP_INFO_ERROR' => Xphp::$_lang['WEB_OS_SERVER_GET_BACKUP_INFO_ERROR'],								//get backup info for backup error
        'OS_SERVER_GET_RECOVERY_INFO_ERROR' => Xphp::$_lang['WEB_OS_SERVER_GET_RECOVERY_INFO_ERROR'],										//get recovery info for recovery error
        'OS_SERVER_OS_NOT_EXIST_ERROR' => Xphp::$_lang['WEB_OS_SERVER_OS_NOT_EXIST_ERROR'],											//not found info in os machine list error
        'OS_SERVER_BD_AGENT_NOT_EXIST_ERROR' => Xphp::$_lang['WEB_OS_SERVER_BD_AGENT_NOT_EXIST_ERROR'],										//not found info in bd agent
        'OS_SERVER_CREATE_BACKUP_DIR_ERROR' => Xphp::$_lang['WEB_OS_SERVER_CREATE_BACKUP_DIR_ERROR'],										//create timepoint dir in backup storage error
        'OS_SERVER_CREATE_SNAPSHOT_ERROR' => Xphp::$_lang['WEB_OS_SERVER_CREATE_SNAPSHOT_ERROR'],										//create snapshot error
        'OS_SERVER_FIND_BACKUP_FILE_ID_ERROR' => Xphp::$_lang['WEB_OS_SERVER_FIND_BACKUP_FILE_ID_ERROR'],									//get backup file id for timepoint error
        'OS_SERVER_MACHINE_REBOOT_ERROR' => Xphp::$_lang['WEB_OS_SERVER_MACHINE_REBOOT_ERROR'],											//machine rebooted after last full backup
        'OS_SERVER_STORAGE_CHANGED_ERROR' => Xphp::$_lang['WEB_OS_SERVER_STORAGE_CHANGED_ERROR'],										//storage changed error
        'OS_SERVER_LIST_DISK_ERROR' => Xphp::$_lang['WEB_OS_SERVER_LIST_DISK_ERROR'],												//list disk in machine error
        'OS_SERVER_SAVE_SELF_EXPLAN_FILE_ERROR' => Xphp::$_lang['WEB_OS_SERVER_SAVE_SELF_EXPLAN_FILE_ERROR'],									//save self-description file error
        'OS_SERVER_CONTAINER_FULL_ERROR' => Xphp::$_lang['WEB_OS_SERVER_CONTAINER_FULL_ERROR'],											//container file is too large
        'OS_SERVER_READ_REMOTE_VOLUME_ERROR' => Xphp::$_lang['WEB_OS_SERVER_READ_REMOTE_VOLUME_ERROR'],										//read volume data from os client error
        'OS_SERVER_WRITE_BITMAP_FILE_ERROR' => Xphp::$_lang['WEB_OS_SERVER_WRITE_BITMAP_FILE_ERROR'],										//write bitmap file error
        'OS_SERVER_GET_VOLUME_BITMAP_ERROR' => Xphp::$_lang['WEB_OS_SERVER_GET_VOLUME_BITMAP_ERROR'],										//get volume bitmap error
        'OS_SERVER_BACKUP_MODE_ERROR' => Xphp::$_lang['WEB_OS_SERVER_BACKUP_MODE_ERROR'],											//backup mode is different from last timepoint backup mode
        'OS_SERVER_READ_PARTITION_TABLE_ERROR' => Xphp::$_lang['WEB_OS_SERVER_READ_PARTITION_TABLE_ERROR'],									//read partition table from os client error
        'OS_SERVER_GET_RECOVERY_TOTAL_SIZE_ERROR' => Xphp::$_lang['WEB_OS_SERVER_GET_RECOVERY_TOTAL_SIZE_ERROR'],								//os get recovery total size error
        'OS_SERVER_REPARTED_FLAG_ERROR' => Xphp::$_lang['WEB_OS_SERVER_REPARTED_FLAG_ERROR'],											//os reparted flag error
        'OS_SERVER_VOLUME_NOT_EXIST_ERROR' => Xphp::$_lang['WEB_OS_SERVER_VOLUME_NOT_EXIST_ERROR'],										//volume not exist error
        'OS_SERVER_WRITE_VOLUME_ERROR' => Xphp::$_lang['WEB_OS_SERVER_WRITE_VOLUME_ERROR'],											//write data to volume error
        'OS_SERVER_VOLUME_CLUSTER_SIZE_UNABLE_DIVIDE_ERROR' => Xphp::$_lang['WEB_OS_SERVER_VOLUME_CLUSTER_SIZE_UNABLE_DIVIDE_ERROR'],						//cluster size can't be divided
        'OS_SERVER_LOCK_VOLUME_ERROR' => Xphp::$_lang['WEB_OS_SERVER_LOCK_VOLUME_ERROR'],											//unmount volume error
        'OS_SERVER_RELOCKED_VOLUME_ERROR' => Xphp::$_lang['WEB_OS_SERVER_RELOCKED_VOLUME_ERROR'],										//volume has been locked, relocked error
        'OS_SERVER_FORMATTING_VOLUME_ERROR' => Xphp::$_lang['WEB_OS_SERVER_FORMATTING_VOLUME_ERROR'],										//formatting volume error
        'OS_SERVER_SPACE_NOT_ENOUGH' => Xphp::$_lang['WEB_OS_SERVER_SPACE_NOT_ENOUGH'],												//disk space not enough
        'OS_SERVER_REPARTED_ERROR' => Xphp::$_lang['WEB_OS_SERVER_REPARTED_ERROR'],												//reparted disk error
        'OS_VOLUME_NUM_CHANGED_ERROR' => Xphp::$_lang['WEB_OS_VOLUME_NUM_CHANGED_ERROR'],											//volume which we need backup num changed
        'OS_DISK_CHANGED_ERROR' => Xphp::$_lang['WEB_OS_DISK_CHANGED_ERROR'],													//volume which we need backup changed
        'OS_SERVER_STRATEGY_ALLOC_ERROR' => Xphp::$_lang['WEB_OS_SERVER_STRATEGY_ALLOC_ERROR'],											//alloc startegy error
        'OS_SERVER_DELETE_SNAPSHOT_ERROR' => Xphp::$_lang['WEB_OS_SERVER_DELETE_SNAPSHOT_ERROR'],										//delete snapshot error
        'OS_SERVER_UNKNOWN_OS_TYPE' => Xphp::$_lang['WEB_OS_SERVER_UNKNOWN_OS_TYPE'],												//unknown os type
        'OS_SERVER_CREATE_PARTITION_ERROR' => Xphp::$_lang['WEB_OS_SERVER_CREATE_PARTITION_ERROR'],										//create partition error
        'OS_SERVER_GRUB_INSTALL_ERROR' => Xphp::$_lang['WEB_OS_SERVER_GRUB_INSTALL_ERROR'],											//install grub into device error
        'OS_SERVER_REPAIR_FSTAB_FILE_ERROR' => Xphp::$_lang['WEB_OS_SERVER_REPAIR_FSTAB_FILE_ERROR'],										//rewrite /etc/fstab file for new os error
        'OS_SERVER_REPAIR_GRUB_CFG_ERROR' => Xphp::$_lang['WEB_OS_SERVER_REPAIR_GRUB_CFG_ERROR'],
        'OS_SERVER_ENCRYPT_INFO_CHANGED_ERROR' => Xphp::$_lang['WEB_OS_SERVER_ENCRYPT_INFO_CHANGED_ERROR'],									//加密信息改变
        'OS_SERVER_WINDOWS_BOOT_VOLUME_SAME_AS_SYSTEM_VOLUME_ERROR' => Xphp::$_lang['WEB_OS_SERVER_WINDOWS_BOOT_VOLUME_SAME_AS_SYSTEM_VOLUME_ERROR'],				//windows系统引导分区和系统分区相同,找不到引导分区可恢复的位置,建议打开重建分区进行恢复
        'OS_SERVER_BOOT_VOLUME_NOT_EXIST_IN_TARGET_MACHINE_ERROR' => Xphp::$_lang['WEB_OS_SERVER_BOOT_VOLUME_NOT_EXIST_IN_TARGET_MACHINE_ERROR'],				//在目标磁盘上找不到可用的引导分区用于恢复,请开启重建分区功能恢复
        'OS_SERVER_TIMEPOINT_NOT_EXIST_ERROR' => Xphp::$_lang['WEB_OS_SERVER_TIMEPOINT_NOT_EXIST_ERROR'],//rewrite grub.cfg file for new os error
        'OS_SERVER_HAS_DIFF_TIMEPOINT_EXIST_ERROR' => Xphp::$_lang['WEB_OS_SERVER_HAS_DIFF_TIMEPOINT_EXIST_ERROR'],
        'OS_SERVER_VOLUME_NOT_UNMOUNT_ERROR' => Xphp::$_lang['WEB_OS_SERVER_VOLUME_NOT_UNMOUNT_ERROR'],
        'OS_SERVER_OVERWRITE_SYSTEM_VOLUME_ERROR' => Xphp::$_lang['WEB_OS_SERVER_OVERWRITE_SYSTEM_VOLUME_ERROR'],
        'OS_SERVER_AGENT_IN_USING_ERROR' => Xphp::$_lang['WEB_OS_SERVER_AGENT_IN_USING_ERROR'],
        'OS_SERVER_VALID_BACKUP_FLAG_CHANGED_ERROR' => Xphp::$_lang['WEB_OS_SERVER_VALID_BACKUP_FLAG_CHANGED_ERROR'],
        'OS_SERVER_EMPTY_BACKUP_VOLUME_ERROR' => Xphp::$_lang['WEB_OS_SERVER_EMPTY_BACKUP_VOLUME_ERROR'],
        'OS_CLIENT_DISK_BAD_SECTOR' => Xphp::$_lang['WEB_OS_CLIENT_DISK_BAD_SECTOR'],
        'OS_PARTITION_BIOSBOOT_ERROR' => Xphp::$_lang['WEB_OS_PARTITION_BIOSBOOT_ERROR'],
        'OS_SERVER_CBT_INVALID' => Xphp::$_lang['WEB_OS_SERVER_CBT_INVALID'],
        'OS_SERVER_CBT_DOWNGRAGE' => Xphp::$_lang['WEB_OS_SERVER_CBT_DOWNGRAGE'],
        'OS_LIVECD_VERSION_DISCREPANCY' => Xphp::$_lang['WEB_OS_LIVECD_VERSION_DISCREPANCY'],
        'OS_SERVER_UNKNOWN_BOOT_TYPE' => Xphp::$_lang['WEB_OS_SERVER_UNKNOWN_BOOT_TYPE'],
        'OS_RESULT_IS_NULL' => Xphp::$_lang['WEB_OS_RESULT_IS_NULL'],
        //操作系统瞬时恢复
        'OS_OSVOLVINFS_CONNECTION_INIT_FAILED' => Xphp::$_lang['WEB_OS_OSVOLVINFS_CONNECTION_INIT_FAILED'],
        'OS_CONNECT_OSVOLVINFS_FAILED' => Xphp::$_lang['WEB_OS_CONNECT_OSVOLVINFS_FAILED'],
        'OS_OSVOLVINFS_CREATE_DISK_FILE_FAILED' => Xphp::$_lang['WEB_OS_OSVOLVINFS_CREATE_DISK_FILE_FAILED'],
        'OS_OSVOLVINFS_FILTER_DISK_ALREADY_EXIST' => Xphp::$_lang['WEB_OS_OSVOLVINFS_FILTER_DISK_ALREADY_EXIST'],
        'OS_OSVOLVINFS_FILTER_DISK_NOT_EXIST' => Xphp::$_lang['WEB_OS_OSVOLVINFS_FILTER_DISK_NOT_EXIST'],
        'OS_OSVOLVINFS_TASK_NOT_FOUND' => Xphp::$_lang['WEB_OS_OSVOLVINFS_TASK_NOT_FOUND'],
        'OS_INST_TASK_HAS_BEEN_CREATED' => Xphp::$_lang['WEB_OS_INST_TASK_HAS_BEEN_CREATED'],
        'OS_OPEN_BACKUP_FILE_ERROR' => Xphp::$_lang['WEB_OS_OPEN_BACKUP_FILE_ERROR'],
        'OS_OPEN_BITMAP_FILE_ERROR' => Xphp::$_lang['WEB_OS_OPEN_BITMAP_FILE_ERROR'],
        'OS_CONNECT_DISK_LIB_ERROR' => Xphp::$_lang['WEB_OS_CONNECT_DISK_LIB_ERROR'],
        'OS_READ_BITMAP_FILE_ERROR' => Xphp::$_lang['WEB_OS_READ_BITMAP_FILE_ERROR'],
        'OS_TASK_TYPE_ERROR' => Xphp::$_lang['WEB_OS_TASK_TYPE_ERROR'],
        'OS_SERVER_OPERATION_NO_CONDITION' => Xphp::$_lang['WEB_OS_SERVER_OPERATION_NO_CONDITION'],
        'OS_SERVER_STOP_MIGRATION_ERROR' => Xphp::$_lang['WEB_OS_SERVER_STOP_MIGRATION_ERROR'],
        'OS_SERVER_DELETE_INSTANTANEOUS_RECOVERY_TASK_ERROR' => Xphp::$_lang['WEB_OS_SERVER_DELETE_INSTANTANEOUS_RECOVERY_TASK_ERROR'],
        'OS_READ_BACKUP_FILE_ERROR' => Xphp::$_lang['WEB_OS_READ_BACKUP_FILE_ERROR'],
        'OS_INST_CREATE_TASK_INVALID_INFO' => Xphp::$_lang['WEB_OS_INST_CREATE_TASK_INVALID_INFO'],
        'OS_SERVER_UNSUPPORT_VOLUME_TYPE' => Xphp::$_lang['WEB_OS_SERVER_UNSUPPORT_VOLUME_TYPE'],
        'OS_SERVER_COMPRESS_MECHOD_CHANGED_ERROR' => Xphp::$_lang['WEB_OS_SERVER_COMPRESS_MECHOD_CHANGED_ERROR'],
        'OS_GET_HARDWARE_INFO_FAILED' => Xphp::$_lang['WEB_OS_GET_HARDWARE_INFO_FAILED'],
        'OS_NOT_HAVE_DRIVER_ERROR' => Xphp::$_lang['WEB_OS_NOT_HAVE_DRIVER_ERROR'],
        'OS_GET_LINUX_MODULE_INFO_FAILED' => Xphp::$_lang['WEB_OS_GET_LINUX_MODULE_INFO_FAILED'],
        'OS_ANALYSIS_MODULE_ALIAS_ERROR' => Xphp::$_lang['WEB_OS_ANALYSIS_MODULE_ALIAS_ERROR'],
        'OS_NOT_SUPPORT_OS_TYPE_ERROR' => Xphp::$_lang['WEB_OS_NOT_SUPPORT_OS_TYPE_ERROR'],
        'OS_NOT_SUPPORT_BUS_TYPE_ERROR' => Xphp::$_lang['WEB_OS_NOT_SUPPORT_BUS_TYPE_ERROR'],
        'OS_DRIVERS_POOL_LACK_DRIVER_ERROR' => Xphp::$_lang['WEB_OS_DRIVERS_POOL_LACK_DRIVER_ERROR'],
        'OS_ADD_DRIVER_FAILED' => Xphp::$_lang['WEB_OS_ADD_DRIVER_FAILED'],
        'OS_BACKUP_AGENT_IS_RUNNING_INST_OR_MIGRATION_TASK' => Xphp::$_lang['WEB_OS_BACKUP_AGENT_IS_RUNNING_INST_OR_MIGRATION_TASK'],
        'OS_RECOVERY_AGENT_IS_RUNNING_INST_OR_MIGRATION_TASK' => Xphp::$_lang['WEB_OS_RECOVERY_AGENT_IS_RUNNING_INST_OR_MIGRATION_TASK'],
        'OS_INST_TARGET_AGENT_IS_RUNNING_BACKUP_OR_RECCOVERY_TASK' => Xphp::$_lang['WEB_OS_INST_TARGET_AGENT_IS_RUNNING_BACKUP_OR_RECCOVERY_TASK'],
        'OS_AGENT_ISCSI_HAS_BEEN_LOGGED_OUT' => Xphp::$_lang['WEB_OS_AGENT_ISCSI_HAS_BEEN_LOGGED_OUT'],
        'OS_PARTITION_BIOSBOOT_OR_BOISGPT_ERROR' => Xphp::$_lang['WEB_OS_PARTITION_BIOSBOOT_OR_BOISGPT_ERROR'],
        'OS_INST_TASK_TARGET_AGENT_OFFLINE_OR_POWEROFF' => Xphp::$_lang['WEB_OS_INST_TASK_TARGET_AGENT_OFFLINE_OR_POWEROFF'],
        'OS_CBT_FAILED_AND_STORED_AS_TAPE' => Xphp::$_lang['WEB_OS_CBT_FAILED_AND_STORED_AS_TAPE'],
        'OS_CBT_NOT_OPENED_AND_STORED_AS_TAPE' => Xphp::$_lang['WEB_OS_CBT_NOT_OPENED_AND_STORED_AS_TAPE'],
        'OS_SYSTEM_AND_RESERVED_NOT_ON_THE_SAME_DISK' => Xphp::$_lang['WEB_OS_SYSTEM_AND_RESERVED_NOT_ON_THE_SAME_DISK'],
        'OS_CBT_FAILED_AND_BACKUP_MODE_DIFFERNTIAL' => Xphp::$_lang['WEB_OS_CBT_FAILED_AND_BACKUP_MODE_DIFFERNTIAL'],
        'OS_BTRFS_UUID_CONFLICT' => Xphp::$_lang['WEB_OS_BTRFS_UUID_CONFLICT'],
        'OS_SERVER_EMPTY_BACKUP_DISK_ERROR' => Xphp::$_lang['WEB_OS_SERVER_EMPTY_BACKUP_DISK_ERROR'],
        'OS_SERVER_DISK_NOT_EXIST_ERROR' => Xphp::$_lang['WEB_OS_SERVER_DISK_NOT_EXIST_ERROR'],
        'OS_SERVER_WRITE_DEVICE_ERROR' => Xphp::$_lang['WEB_OS_SERVER_WRITE_DEVICE_ERROR'],
        'OS_STOP_CBT_MONITOR_ERROR' => Xphp::$_lang['WEB_OS_STOP_CBT_MONITOR_ERROR'],
        'OS_EXIST_REMOVABLEDEV' => Xphp::$_lang['WEB_OS_EXIST_REMOVABLEDEV'],
        'OS_INST_TASK_VOLUME_MOUNT_ERROR' => Xphp::$_lang['WEB_OS_INST_TASK_VOLUME_MOUNT_ERROR'],
        'OS_INST_TASK_AGENT_BEFORE_ISCSI_OP_SCRIPTS_RUN_ERROR' => Xphp::$_lang['WEB_OS_INST_TASK_AGENT_BEFORE_ISCSI_OP_SCRIPTS_RUN_ERROR'],
        'OS_INST_TASK_AGENT_AFTER_ISCSI_OP_SCRIPTS_RUN_ERROR' => Xphp::$_lang['WEB_OS_INST_TASK_AGENT_AFTER_ISCSI_OP_SCRIPTS_RUN_ERROR'],
        'OS_INST_TASK_AGENT_ISCSI_OP_SCRIPTS_RUN_ERROR' => Xphp::$_lang['WEB_OS_INST_TASK_AGENT_ISCSI_OP_SCRIPTS_RUN_ERROR'],
        'OS_RECOVERY_MEDIA_AND_SOURE_BACKUP_SYSTEM_INCONSISTENT' => Xphp::$_lang['WEB_OS_RECOVERY_MEDIA_AND_SOURE_BACKUP_SYSTEM_INCONSISTENT'],
        'OS_STORAGE_STRATEGY_HAS_BEEN_CHANGED' => Xphp::$_lang['WEB_OS_STORAGE_STRATEGY_HAS_BEEN_CHANGED'],
        'OS_LAST_BACKUP_ERROR_INCOMPLETE_MONITORING_DATA' => Xphp::$_lang['WEB_OS_LAST_BACKUP_ERROR_INCOMPLETE_MONITORING_DATA'],
        'OS_MEMORY_NOT_ENOUGH_WHEN_CREATE_SNAPSHOT' => Xphp::$_lang['WEB_OS_MEMORY_NOT_ENOUGH_WHEN_CREATE_SNAPSHOT'],
        'OS_BACKUP_DISK_SIZE_HAS_BEEN_CHANGED' => Xphp::$_lang['WEB_OS_BACKUP_DISK_SIZE_HAS_BEEN_CHANGED'],
        'OS_RESTART_AGENT_DRIVER_ERROR' => Xphp::$_lang['WEB_OS_RESTART_AGENT_DRIVER_ERROR'],
        //卷CDP 
        'VOL_CDP_GENERIC_SUCCESS' => Xphp::$_lang['WEB_VOL_CDP_VOL_CDP_GENERIC_SUCCESS'],
        'VOL_CDP_ERROR_PACKET_ORDER' => Xphp::$_lang['WEB_VOL_CDP_ERROR_PACKET_ORDER'],
        'VOL_CDP_ERROR_PACKET_ABNORMAL' => Xphp::$_lang['WEB_VOL_CDP_ERROR_PACKET_ABNORMAL'],
        'VOL_CDP_ERROR_UNKNOWN_OP_CODE' => Xphp::$_lang['WEB_VOL_CDP_ERROR_UNKNOWN_OP_CODE'],
        'VOL_CDP_ERROR_TASK_NOT_EXIST' => Xphp::$_lang['WEB_VOL_CDP_ERROR_TASK_NOT_EXIST'],
        'VOL_CDP_ERROR_LOAD_TASK_INFO_FAILED' => Xphp::$_lang['WEB_VOL_CDP_ERROR_LOAD_TASK_INFO_FAILED'],
        'VOL_CDP_ERROR_TASK_NOT_IN_TAKEOVER_STATUS' => Xphp::$_lang['WEB_VOL_CDP_ERROR_TASK_NOT_IN_TAKEOVER_STATUS'],
        'VOL_CDP_ERROR_TASK_NOT_IN_RUNNING_STATUS' => Xphp::$_lang['WEB_VOL_CDP_ERROR_TASK_NOT_IN_RUNNING_STATUS'],
        'VOL_CDP_ERROR_TASK_NOT_IN_FAULT_RESUME_STAGE' => Xphp::$_lang['WEB_VOL_CDP_ERROR_TASK_NOT_IN_FAULT_RESUME_STAGE'],
        'VOL_CDP_ERROR_NOT_FIND_RUNNING_TASK' => Xphp::$_lang['WEB_VOL_CDP_ERROR_NOT_FIND_RUNNING_TASK'],
        'VOL_CDP_ERROR_INVALID_VOL_ID' => Xphp::$_lang['WEB_VOL_CDP_ERROR_INVALID_VOL_ID'],
        'VOL_CDP_ERROR_AGENT_FILE_CACHE_NO_SPACE' => Xphp::$_lang['WEB_VOL_CDP_ERROR_AGENT_FILE_CACHE_NO_SPACE'],
        'VOL_CDP_ERROR_AGENT_MEMORY_CACHE_NO_SPACE' => Xphp::$_lang['WEB_VOL_CDP_ERROR_AGENT_MEMORY_CACHE_NO_SPACE'],
        'VOL_CDP_ERROR_AGENT_WRITE_CACHE_FAILED' => Xphp::$_lang['WEB_VOL_CDP_ERROR_AGENT_WRITE_CACHE_FAILED'],
        'VOL_CDP_ERROR_AGENT_FIND_CACHE_FILE_FAILED' => Xphp::$_lang['WEB_VOL_CDP_ERROR_AGENT_FIND_CACHE_FILE_FAILED'],
        'VOL_CDP_ERROR_AGENT_MAP_CACHE_MDL_FAILED' => Xphp::$_lang['WEB_VOL_CDP_ERROR_AGENT_MAP_CACHE_MDL_FAILED'],
        'VOL_CDP_ERROR_AGENT_LOAD_CACHE_CONFIG_FAILED' => Xphp::$_lang['WEB_VOL_CDP_ERROR_AGENT_LOAD_CACHE_CONFIG_FAILED'],
        'VOL_CDP_ERROR_AGENT_CREATE_CACHE_DIR_FAILED' => Xphp::$_lang['WEB_VOL_CDP_ERROR_AGENT_CREATE_CACHE_DIR_FAILED'],
        'VOL_CDP_ERROR_AGENT_OPEN_CACHE_FILE_FAILED' => Xphp::$_lang['WEB_VOL_CDP_ERROR_AGENT_OPEN_CACHE_FILE_FAILED'],
        'VOL_CDP_ERROR_AGENT_QUERY_CACHE_VOL_FAILED' => Xphp::$_lang['WEB_VOL_CDP_ERROR_AGENT_QUERY_CACHE_VOL_FAILED'],
        'VOL_CDP_ERROR_AGENT_CREATE_DATA_MONITOR_FAILED' => Xphp::$_lang['WEB_VOL_CDP_ERROR_AGENT_CREATE_DATA_MONITOR_FAILED'],
        'VOL_CDP_ERROR_AGENT_UNMOUNT_VOL_FAILED' => Xphp::$_lang['WEB_VOL_CDP_ERROR_AGENT_UNMOUNT_VOL_FAILED'],
        'VOL_CDP_ERROR_AGENT_HANDSHAKE_FAILED' => Xphp::$_lang['WEB_VOL_CDP_ERROR_AGENT_HANDSHAKE_FAILED'],
        'VOL_CDP_ERROR_OPEN_SNAPSHOT_VOL_FAILED' => Xphp::$_lang['WEB_VOL_CDP_ERROR_OPEN_SNAPSHOT_VOL_FAILED'],
        'VOL_CDP_ERROR_OPEN_BACKUP_VOL_FAILED' => Xphp::$_lang['WEB_VOL_CDP_ERROR_OPEN_BACKUP_VOL_FAILED'],
        'VOL_CDP_ERROR_OPEN_RESTORE_VOL_FAILED' => Xphp::$_lang['WEB_VOL_CDP_ERROR_OPEN_RESTORE_VOL_FAILED'],
        'VOL_CDP_ERROR_FORMAT_VOL_FAILED' => Xphp::$_lang['WEB_VOL_CDP_ERROR_FORMAT_VOL_FAILED'],
        'VOL_CDP_ERROR_CREATE_VOL_CACHE_STORAGE_FAILED' => Xphp::$_lang['WEB_VOL_CDP_ERROR_CREATE_VOL_CACHE_STORAGE_FAILED'],
        'VOL_CDP_ERROR_BITMAP_READ_FINISH' => Xphp::$_lang['WEB_VOL_CDP_ERROR_BITMAP_READ_FINISH'],
        'VOL_CDP_ERROR_LOAD_FS_BITMAP_FAILED' => Xphp::$_lang['WEB_VOL_CDP_ERROR_LOAD_FS_BITMAP_FAILED'],
        'VOL_CDP_ERROR_NOTIFY_AGENT_PERFORM_ACTION' => Xphp::$_lang['WEB_VOL_CDP_ERROR_NOTIFY_AGENT_PERFORM_ACTION'],
        'VOL_CDP_ERROR_READ_INVALID_CACHE_META' => Xphp::$_lang['WEB_VOL_CDP_ERROR_READ_INVALID_CACHE_META'],
        'VOL_CDP_ERROR_READ_INVALID_CACHE_DATA' => Xphp::$_lang['WEB_VOL_CDP_ERROR_READ_INVALID_CACHE_DATA'],
        'VOL_CDP_ERROR_WAIT_ACK_TIMEOUT' => Xphp::$_lang['WEB_VOL_CDP_ERROR_WAIT_ACK_TIMEOUT'],
        'VOL_CDP_ERROR_CONTROL_SENDER_NOT_INIT' => Xphp::$_lang['WEB_VOL_CDP_ERROR_CONTROL_SENDER_NOT_INIT'],
        'VOL_CDP_ERROR_AGENT_LINK_LOST' => Xphp::$_lang['WEB_VOL_CDP_ERROR_AGENT_LINK_LOST'],
        'VOL_CDP_ERROR_FIND_TASK_CHILD_PROCESS' => Xphp::$_lang['WEB_VOL_CDP_ERROR_FIND_TASK_CHILD_PROCESS'],
        'VOL_CDP_ERROR_CONNECT_IO_MAPPPING_ROUTINE' => Xphp::$_lang['WEB_VOL_CDP_ERROR_CONNECT_IO_MAPPPING_ROUTINE'],
        'VOL_CDP_ERROR_GET_IO_BITMAP' => Xphp::$_lang['WEB_VOL_CDP_ERROR_GET_IO_BITMAP'],
        'VOL_CDP_ERROR_BACKUP_IMAGE_IN_MERGE' => Xphp::$_lang['WEB_VOL_CDP_ERROR_BACKUP_IMAGE_IN_MERGE'],
        'VOL_CDP_ERROR_REBUILD_PARTITION' => Xphp::$_lang['WEB_VOL_CDP_ERROR_REBUILD_PARTITION'],

        'VOL_CDP_ERROR_APP_GENERIC_ERROR' => Xphp::$_lang['WEB_VOL_CDP_ERROR_APP_GENERIC_ERROR'],
        'VOL_CDP_ERROR_APP_SERVICE_NOT_RUNNING' => Xphp::$_lang['WEB_VOL_CDP_ERROR_APP_SERVICE_NOT_RUNNING'],
        'VOL_CDP_ERROR_PREPARE_APP_SERVICE_FAILED' => Xphp::$_lang['WEB_VOL_CDP_ERROR_PREPARE_APP_SERVICE_FAILED'],
        'VOL_CDP_ERROR_PREPARE_APP_SERVICE_TIMEOUT' => Xphp::$_lang['WEB_VOL_CDP_ERROR_PREPARE_APP_SERVICE_TIMEOUT'],
        'VOL_CDP_ERROR_APP_MODULE_LIST_SIZE_INVALID' => Xphp::$_lang['WEB_VOL_CDP_ERROR_APP_MODULE_LIST_SIZE_INVALID'],

        'VOL_CDP_ERROR_PARSE_APP_MODULE_FILE_INFO_FAILED' => Xphp::$_lang['WEB_VOL_CDP_ERROR_PARSE_APP_MODULE_FILE_INFO_FAILED'],
        'VOL_CDP_ERROR_PARSE_VOL_MOUNT_RELATION_FAILED' => Xphp::$_lang['WEB_VOL_CDP_ERROR_PARSE_VOL_MOUNT_RELATION_FAILED'],  //parse vol mount relation failed
        'VOL_CDP_ERROR_RESTORE_APP_CONTROL_FILE_FAILED' => Xphp::$_lang['WEB_VOL_CDP_ERROR_RESTORE_APP_CONTROL_FILE_FAILED'],
        'VOL_CDP_ERROR_RESTORE_APP_FILE_PATH_FAILED' => Xphp::$_lang['WEB_VOL_CDP_ERROR_RESTORE_APP_FILE_PATH_FAILED'],
        'VOL_CDP_ERROR_GET_STANDBY_APP_CONTROLFILE_FAILED' => Xphp::$_lang['WEB_VOL_CDP_ERROR_GET_STANDBY_APP_CONTROLFILE_FAILED'],  //get standby app controlfile path failed
        'VOL_CDP_ERROR_EXECUTE_CMD_SCRIPT_FAILED' => Xphp::$_lang['WEB_VOL_CDP_ERROR_EXECUTE_CMD_SCRIPT_FAILED'],  //5510   execute command script failed
        'VOL_CDP_ERROR_DETACH_APP_MODULE_FAILED' => Xphp::$_lang['WEB_VOL_CDP_ERROR_DETACH_APP_MODULE_FAILED'],  //detach app module failed
        'VOL_CDP_ERROR_ATTACH_APP_MODULE_FAILED' => Xphp::$_lang['WEB_VOL_CDP_ERROR_ATTACH_APP_MODULE_FAILED'],  //attach app module failed
        'VOL_CDP_ERROR_MONITOR_APP_STATUS_IS_ABNORMAL' => Xphp::$_lang['WEB_VOL_CDP_ERROR_MONITOR_APP_STATUS_IS_ABNORMAL'],  //monitor app status is abnormal
        'VOL_CDP_ERROR_TAKEOVER_APP_FAILED' => Xphp::$_lang['WEB_VOL_CDP_ERROR_TAKEOVER_APP_FAILED'],  //takeover app failed
        'VOL_CDP_ERROR_TAKEOVER_APP_NOT_FOUND_CONTROL_FILES' => Xphp::$_lang['WEB_VOL_CDP_ERROR_TAKEOVER_APP_NOT_FOUND_CONTROL_FILES'],  //takeover app not found control files
        'VOL_CDP_ERROR_EXECUTE_CMD_SCRIPT_NOT_EXIST' => Xphp::$_lang['WEB_VOL_CDP_ERROR_EXECUTE_CMD_SCRIPT_NOT_EXIST'],  //execute command script not exist
        'VOL_CDP_ERROR_SCAN_APP_COMPLETED_BUT_SOME_FAILED' => Xphp::$_lang['WEB_VOL_CDP_ERROR_SCAN_APP_COMPLETED_BUT_SOME_FAILED'],  //scan app info completed but had some app scan faild
        'VOL_CDP_ERROR_CHANGE_APP_FILE_OWNER_FAILED' => Xphp::$_lang['WEB_VOL_CDP_ERROR_CHANGE_APP_FILE_OWNER_FAILED'],

        'VOL_CDP_ERROR_REALTIME_BACKUP_LICENSE_EXHAUST' => Xphp::$_lang['WEB_VOL_CDP_ERROR_REALTIME_BACKUP_LICENSE_EXHAUST'],
        'VOL_CDP_ERROR_AUTO_TAKEOVER_LICENSE_EXHAUST' => Xphp::$_lang['WEB_VOL_CDP_ERROR_AUTO_TAKEOVER_LICENSE_EXHAUST'],

        'VOL_CDP_ERROR_ASSOCIATED_TASK_EXIST_ON_THE_HOST' => Xphp::$_lang['WEB_VOL_CDP_ERROR_ASSOCIATED_TASK_EXIST_ON_THE_HOST'],
        'VOL_CDP_ERROR_AGENT_NET_FAULT' => Xphp::$_lang['WEB_VOL_CDP_ERROR_AGENT_NET_FAULT'],
        'VOL_CDP_ERROR_AGENT_APP_STATUS_ABNORMAL' => Xphp::$_lang['WEB_VOL_CDP_ERROR_AGENT_APP_STATUS_ABNORMAL'],
        'VOL_CDP_ERROR_AGENT_MONITORING_SCRIPT_EXECUTE_FAILED' => Xphp::$_lang['WEB_VOL_CDP_ERROR_AGENT_MONITORING_SCRIPT_EXECUTE_FAILED'],
        'VOL_CDP_ERROR_AUTHORIZED_CAPACITY_INSUFFICIENT' => Xphp::$_lang['WEB_VOL_CDP_ERROR_AUTHORIZED_CAPACITY_INSUFFICIENT'],
        'VOL_CDP_ERROR_AUTHORIZED_CAPACITY_INSUFFICIENT_TO_BACKUP' => Xphp::$_lang['WEB_VOL_CDP_ERROR_AUTHORIZED_CAPACITY_INSUFFICIENT_TO_BACKUP'],
        'VOL_CDP_ERROR_BACKUP_STORAGE_ROOT_PATH_NOT_EXIST' => Xphp::$_lang['WEB_VOL_CDP_ERROR_BACKUP_STORAGE_ROOT_PATH_NOT_EXIST'],
        'VOL_CDP_ERROR_READ_CACHE_DATA_INDEX_NOT_CONTINUITY' => Xphp::$_lang['WEB_VOL_CDP_ERROR_READ_CACHE_DATA_INDEX_NOT_CONTINUITY'],

        'VOL_CDP_ERROR_OPEN_VOL_FILTER_DRIVER_CONTROL_DEVICE_FAILED' => Xphp::$_lang['WEB_VOL_CDP_ERROR_OPEN_VOL_FILTER_DRIVER_CONTROL_DEVICE_FAILED'],
        'VOL_CDP_ERROR_INITIALIZER_CACHE_FAILED' => Xphp::$_lang['WEB_VOL_CDP_ERROR_INITIALIZER_CACHE_FAILED'],
        'VOL_CDP_ERROR_MMAP_CACHE_FAILED' => Xphp::$_lang['WEB_VOL_CDP_ERROR_MMAP_CACHE_FAILED'],
        'VOL_CDP_ERROR_DETECT_UNCONFIGURED_FILE_CACHE' => Xphp::$_lang['WEB_VOL_CDP_ERROR_DETECT_UNCONFIGURED_FILE_CACHE'],
        'VOL_CDP_ERROR_CACHE_READ_OFFSET_ABNORMAL' => Xphp::$_lang['WEB_VOL_CDP_ERROR_CACHE_READ_OFFSET_ABNORMAL'],
        'VOL_CDP_ERROR_ALLOC_MEMORY_FOR_COPY_IO_UNIT_FAILED' => Xphp::$_lang['WEB_VOL_CDP_ERROR_ALLOC_MEMORY_FOR_COPY_IO_UNIT_FAILED'],
        'VOL_CDP_ERROR_CACHE_VOL_SPACE_NOT_ENOUGH' => Xphp::$_lang['WEB_VOL_CDP_ERROR_CACHE_VOL_SPACE_NOT_ENOUGH'],
        'VOL_CDP_ERROR_DETECT_HAVE_VOL_NOT_IN_MONITOR_STATE' => Xphp::$_lang['WEB_VOL_CDP_ERROR_DETECT_HAVE_VOL_NOT_IN_MONITOR_STATE'],
        'VOL_CDP_ERROR_FAILED_TO_CREATE_BACKUP_STORAGE_ROOT_PATH' => Xphp::$_lang['WEB_VOL_CDP_ERROR_FAILED_TO_CREATE_BACKUP_STORAGE_ROOT_PATH'],
        'VOL_CDP_ERROR_SERVER_OPEN_CACHE_FILE_FAILED' => Xphp::$_lang['WEB_VOL_CDP_ERROR_SERVER_OPEN_CACHE_FILE_FAILED'],
        'VOL_CDP_ERROR_SERVER_WRITE_CACHE_FAILED' => Xphp::$_lang['WEB_VOL_CDP_ERROR_SERVER_WRITE_CACHE_FAILED'],
        'VOL_CDP_ERROR_CACHE_INSUFFICIENT_COVERT_TO_CBT' => Xphp::$_lang['WEB_VOL_CDP_ERROR_CACHE_INSUFFICIENT_COVERT_TO_CBT'],
        'VOL_CDP_ERROR_HOST_SYSTEM_VOLUME_IS_DYNAMIC' => Xphp::$_lang['WEB_VOL_CDP_ERROR_HOST_SYSTEM_VOLUME_IS_DYNAMIC'],
        'VOL_CDP_ERROR_DETECT_DATA_PACK_TIMESTAMP_EXCEPTION' => Xphp::$_lang['WEB_VOL_CDP_ERROR_DETECT_DATA_PACK_TIMESTAMP_EXCEPTION'],
        'VOL_CDP_ERROR_VOL_FILTER_DEV_NOT_EXIST' => Xphp::$_lang['WEB_VOL_CDP_ERROR_VOL_FILTER_DEV_NOT_EXIST'],
        'VOL_CDP_ERROR_AGENT_DATA_SEND_STOPPED' => Xphp::$_lang['WEB_VOL_CDP_ERROR_AGENT_DATA_SEND_STOPPED'],
        'VOL_CDP_ERROR_TAKEOVER_VM_PREFIX_FAILED' => Xphp::$_lang['WEB_VOL_CDP_ERROR_TAKEOVER_VM_PREFIX_FAILED'],
        'VOL_CDP_ERROR_TAKEOVER_VM_PREFIX_TIMEOUT' => Xphp::$_lang['WEB_VOL_CDP_ERROR_TAKEOVER_VM_PREFIX_TIMEOUT'],
        'VOL_CDP_ERROR_TAKEOVER_VM_HANDSHAKE_TIMEOUT' => Xphp::$_lang['WEB_VOL_CDP_ERROR_TAKEOVER_VM_HANDSHAKE_TIMEOUT'],
        'VOL_CDP_ERROR_MASTER_AGENT_IS_ONLINE_BEFORE_TAKEOVER' => Xphp::$_lang['WEB_VOL_CDP_ERROR_MASTER_AGENT_IS_ONLINE_BEFORE_TAKEOVER'],
        'VOL_CDP_ERROR_CREATE_IMAGE_SNAP_FAILED' => Xphp::$_lang['WEB_VOL_CDP_ERROR_CREATE_IMAGE_SNAP_FAILED'],
        'VOL_CDP_ERROR_LOAD_TAKEOVER_VM_BASE_CONFIG' => Xphp::$_lang['WEB_VOL_CDP_ERROR_LOAD_TAKEOVER_VM_BASE_CONFIG'],
        'VOL_CDP_ERROR_SET_TAKEOVER_VM_DISK_CONFIG' => Xphp::$_lang['WEB_VOL_CDP_ERROR_SET_TAKEOVER_VM_DISK_CONFIG'],
        'VOL_CDP_ERROR_ADD_INTER_NETCARD_TO_TAKEOVER_VM' => Xphp::$_lang['WEB_VOL_CDP_ERROR_ADD_INTER_NETCARD_TO_TAKEOVER_VM'],
        'VOL_CDP_ERROR_CREATE_TAKEOVER_VM' => Xphp::$_lang['WEB_VOL_CDP_ERROR_CREATE_TAKEOVER_VM'],
        'VOL_CDP_ERROR_DELETE_TAKEOVER_VM' => Xphp::$_lang['WEB_VOL_CDP_ERROR_DELETE_TAKEOVER_VM'],
        'VOL_CDP_ERROR_START_TAKEOVER_VM' => Xphp::$_lang['WEB_VOL_CDP_ERROR_START_TAKEOVER_VM'],
        'VOL_CDP_ERROR_STOP_TAKEOVER_VM' => Xphp::$_lang['WEB_VOL_CDP_ERROR_STOP_TAKEOVER_VM'],
        'VOL_CDP_ERROR_GET_TAKEOVER_VM_STATUS' => Xphp::$_lang['WEB_VOL_CDP_ERROR_GET_TAKEOVER_VM_STATUS'],
        'VOL_CDP_ERROR_FILL_PARAMETER_FOR_PREFIX' => Xphp::$_lang['WEB_VOL_CDP_ERROR_FILL_PARAMETER_FOR_PREFIX'],
        'VOL_CDP_ERROR_MEM_STANDBY_HANDSHAKE_TIMEOUT' => Xphp::$_lang['WEB_VOL_CDP_ERROR_MEM_STANDBY_HANDSHAKE_TIMEOUT'],
        'VOL_CDP_SERVER_OPERATION_MAGIC_ERROR' => Xphp::$_lang['WEB_VOL_CDP_SERVER_OPERATION_MAGIC_ERROR'],
        'VOL_CDP_SERVER_OPERATION_OPCODE_INVALID_ERROR' => Xphp::$_lang['WEB_VOL_CDP_SERVER_OPERATION_OPCODE_INVALID_ERROR'],
        'VOL_CDP_ERROR_SKIP_SCAN_VIRUS' => Xphp::$_lang['WEB_VOL_CDP_ERROR_SKIP_SCAN_VIRUS'],
        'VOL_CDP_ERROR_MONITOR_EXCEPTION_OCCURRED' => Xphp::$_lang['WEB_VOL_CDP_ERROR_MONITOR_EXCEPTION_OCCURRED'],
        'VOL_CDP_AGENT_WRITE_DATA_ERROR' => Xphp::$_lang['WEB_VOL_CDP_AGENT_WRITE_DATA_ERROR'],
        'VOL_CDP_LOAD_BALANCING_CHANGE_NODE' => Xphp::$_lang['WEB_VOL_CDP_LOAD_BALANCING_CHANGE_NODE'],
        'VOL_CDP_ERROR_WAIT_MEM_STANDBY_REBOOT_TIMEOUT' => Xphp::$_lang['WEB_VOL_CDP_ERROR_WAIT_MEM_STANDBY_REBOOT_TIMEOUT'],
        'VOL_CDP_ERROR_GET_DISK_SIGN_ERROR' => Xphp::$_lang['WEB_VOL_CDP_ERROR_GET_DISK_SIGN_ERROR'],
        'VOL_CDP_ERROR_THERE_ARE_NO_PART_NO_FS_DISK' => Xphp::$_lang['WEB_VOL_CDP_ERROR_THERE_ARE_NO_PART_NO_FS_DISK'],
        'VOL_CDP_ERROR_TAKEOVER_STANDBY_HANDSHAKE_TIMEOUT' => Xphp::$_lang['WEB_VOL_CDP_ERROR_TAKEOVER_STANDBY_HANDSHAKE_TIMEOUT'],
        'VOL_CDP_ERROR_FAILBACK_STANDBY_HANDSHAKE_TIMEOUT' => Xphp::$_lang['WEB_VOL_CDP_ERROR_FAILBACK_STANDBY_HANDSHAKE_TIMEOUT'],
        'VOL_CDP_ERROR_FIND_VIRUS_AND_STOP_TASK_BY_STRATEGY' => Xphp::$_lang['WEB_VOL_CDP_ERROR_FIND_VIRUS_AND_STOP_TASK_BY_STRATEGY'],
        'VOL_CDP_ERROR_HANDSHAKE_TO_REBOOTED_AGENT_TIMEOUT' => Xphp::$_lang['WEB_VOL_CDP_ERROR_HANDSHAKE_TO_REBOOTED_AGENT_TIMEOUT'],
        'VOL_CDP_ERROR_VOLUME_GROUP_NAME_CONFLICTED' => Xphp::$_lang['WEB_VOL_CDP_ERROR_VOLUME_GROUP_NAME_CONFLICTED'],
        'VOL_CDP_ERROR_DISK_FILTER_DRIVER_NOT_LOADED' => Xphp::$_lang['WEB_VOL_CDP_ERROR_DISK_FILTER_DRIVER_NOT_LOADED'],
        'VOL_CDP_ERROR_IMAGE_SNAPS_ALREADY_EXIST' => Xphp::$_lang['WEB_VOL_CDP_ERROR_IMAGE_SNAPS_ALREADY_EXIST'],
        'VOL_CDP_ERROR_CLIENT_REPLICATION_LICENSE_EXHAUST' => Xphp::$_lang['WEB_VOL_CDP_ERROR_CLIENT_REPLICATION_LICENSE_EXHAUST'],
        'VOL_CDP_ERROR_EMD_TAKEOVER_LICENSE_EXHAUST' => Xphp::$_lang['WEB_VOL_CDP_ERROR_EMD_TAKEOVER_LICENSE_EXHAUST'],
        //节点
        'NODE_NOT_SUPPORT_STORAGE_ERROR' => Xphp::$_lang['WEB_ERROR_NODE_NOT_SUPPORT_STORAGE_ERROR'],
        'NODE_SERVER_TIME_CONVERT_ERROR' => Xphp::$_lang['WEB_ERROR_NODE_SERVER_TIME_CONVERT_ERROR'],
        'NODE_SERVER_QUERY_ALL_STRATEGY_ERROR' => Xphp::$_lang['WEB_ERROR_NODE_SERVER_QUERY_ALL_STRATEGY_ERROR'],
        'NODE_SERVER_STRATEGY_NULL_ERROR' => Xphp::$_lang['WEB_ERROR_NODE_SERVER_STRATEGY_NULL_ERROR'],
        'NODE_SERVER_STRATEGY_ALREADY_START_ERROR' => Xphp::$_lang['WEB_ERROR_NODE_SERVER_STRATEGY_ALREADY_START_ERROR'],
        'NODE_SERVER_STRATEGY_TIME_CONFIG_ERROR' => Xphp::$_lang['WEB_ERROR_NODE_SERVER_STRATEGY_TIME_CONFIG_ERROR'],
        'NODE_SERVER_NAS_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_NODE_SERVER_NAS_EXIST_ERROR'],
        'NODE_SERVER_NAS_PARAM_ERROR' => Xphp::$_lang['WEB_ERROR_NODE_SERVER_NAS_PARAM_ERROR'],
        'NODE_SERVER_SPACE_NOT_ENOUGH_TO_UPDATE_ERROR' => Xphp::$_lang['WEB_ERROR_NODE_SERVER_SPACE_NOT_ENOUGH_TO_UPDATE_ERROR'], 	//space not enough to do update
        'NODE_SERVER_TAPE_CARRIAGE_ALREADY_IN_GROUP' => Xphp::$_lang['WEB_ERROR_NODE_SERVER_TAPE_CARRIAGE_ALREADY_IN_GROUP'],
        'NODE_SERVER_TAPE_CARRIAGE_CANNOT_DO_ANY_OPERATION' => Xphp::$_lang['WEB_ERROR_NODE_SERVER_TAPE_CARRIAGE_CANNOT_DO_ANY_OPERATION'],


        'NODE_SERVER_USER_RESOURCE_TRANSFERRING_ERROR' => Xphp::$_lang['WEB_ERROR_NODE_SERVER_USER_RESOURCE_TRANSFERRING_ERROR'], 	//user resource are transferring
        'NODE_SERVER_REMOTE_NODE_CONNECT_ERROR' => Xphp::$_lang['WEB_ERROR_NODE_SERVER_REMOTE_NODE_CONNECT_ERROR'],
        'NODE_SERVER_REMOTE_NODE_NOT_MASTER_NODE' => Xphp::$_lang['WEB_ERROR_NODE_SERVER_REMOTE_NODE_NOT_MASTER_NODE'],
        'NODE_SERVER_ALARM_PUSH_STRATEGY_NOT_FOUND_OR_DISABLED' => Xphp::$_lang['WEB_ERROR_NODE_SERVER_ALARM_PUSH_STRATEGY_NOT_FOUND_OR_DISABLED'],
        'NODE_SERVER_NODE_NETWORK_ACCESS_ERROR' => Xphp::$_lang['WEB_ERROR_NODE_SERVER_NODE_NETWORK_ACCESS_ERROR'],

        'NODE_MANAGER_CANNOT_OPERATE_MASTER_NODE_ERROR' => Xphp::$_lang['WEB_ERROR_NODE_MANAGER_CANNOT_OPERATE_MASTER_NODE_ERROR'],
        'NODE_MANAGER_TARGET_NODE_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_NODE_MANAGER_TARGET_NODE_EXIST_ERROR'],
        'NODE_MANAGER_TARGET_NODE_ALREADY_USED_BY_OTHER_SYSTEM_ERROR' => Xphp::$_lang['WEB_ERROR_NODE_MANAGER_TARGET_NODE_ALREADY_USED_BY_OTHER_SYSTEM_ERROR'],
        'NODE_MANAGER_CANNOT_ADD_MASTER_NODE_ERROR' => Xphp::$_lang['WEB_ERROR_NODE_MANAGER_CANNOT_ADD_MASTER_NODE_ERROR'],
        'NODE_MANAGER_BACKUP_NODE_MAXIMIZING' => Xphp::$_lang['WEB_ERROR_NODE_MANAGER_BACKUP_NODE_MAXIMIZING'],

        'NODE_SERVER_STORAGE_IS_ALREADY_SYNCING_ERROR' => Xphp::$_lang['WEB_ERROR_NODE_SERVER_STORAGE_IS_ALREADY_SYNCING_ERROR'],
        'NODE_NOT_SUPPORT_WORM_AS_VDP_NOT_LOADED' => Xphp::$_lang['WEB_ERROR_NODE_NOT_SUPPORT_WORM_AS_VDP_NOT_LOADED'],

        'DATA_STORAGE_INVALID_DEVICE_TYPE' => Xphp::$_lang['WEB_ERROR_DATA_STORAGE_INVALID_DEVICE_TYPE'],
        'DATA_STORAGE_UNSUPPORTED_NETWORK_TYPE' => Xphp::$_lang['WEB_ERROR_DATA_STORAGE_UNSUPPORTED_NETWORK_TYPE'],
        'DATA_STORAGE_UNSUPPORTED_DEVICE_TYPE' => Xphp::$_lang['WEB_ERROR_DATA_STORAGE_UNSUPPORTED_DEVICE_TYPE'],
        'DATA_STORAGE_DEVICE_TYPE_NOT_MATCH' => Xphp::$_lang['WEB_ERROR_DATA_STORAGE_DEVICE_TYPE_NOT_MATCH'],
        'DATA_STORAGE_FILE_ALREADY_OPEN' => Xphp::$_lang['WEB_ERROR_DATA_STORAGE_FILE_ALREADY_OPEN'],
        'DATA_STORAGE_INITIALIZER_SESSION_FAILED' => Xphp::$_lang['WEB_ERROR_DATA_STORAGE_INITIALIZER_SESSION_FAILED'],
        'DATA_STORAGE_NOT_SUPPORT_OPERATION' => Xphp::$_lang['WEB_ERROR_DATA_STORAGE_NOT_SUPPORT_OPERATION'],
        'DATA_STORAGE_CONTEXT_UNINITIALIZED' => Xphp::$_lang['WEB_ERROR_DATA_STORAGE_CONTEXT_UNINITIALIZED'],
        'DATA_STORAGE_NOT_FOUND_WORK_PROCESS' => Xphp::$_lang['WEB_ERROR_DATA_STORAGE_NOT_FOUND_WORK_PROCESS'],
        'DATA_STORAGE_CREATE_WORK_PROCESS_FAILED' => Xphp::$_lang['WEB_ERROR_DATA_STORAGE_CREATE_WORK_PROCESS_FAILED'],
        'DATA_STORAGE_INCOMPATIBLE_NETWORK_AND_DEVICE_TYPE' => Xphp::$_lang['WEB_ERROR_DATA_STORAGE_INCOMPATIBLE_NETWORK_AND_DEVICE_TYPE'],

        'DATA_STORAGE_TAPE_NOT_FOUND_AVAILABLE_CARRIAGE' => Xphp::$_lang['WEB_ERROR_DATA_STORAGE_TAPE_NOT_FOUND_AVAILABLE_CARRIAGE'],
        'DATA_STORAGE_TAPE_NOT_FOUND_IDLE_DRIVER' => Xphp::$_lang['WEB_ERROR_DATA_STORAGE_TAPE_NOT_FOUND_IDLE_DRIVER'],
        'DATA_STORAGE_TAPE_OPEN_CARRIAGE_FAILED' => Xphp::$_lang['WEB_ERROR_DATA_STORAGE_TAPE_OPEN_CARRIAGE_FAILED'],
        'DATA_STORAGE_TAPE_WRITE_STORAGE_HEAD_FAILED' => Xphp::$_lang['WEB_ERROR_DATA_STORAGE_TAPE_WRITE_STORAGE_HEAD_FAILED'],
        'DATA_STORAGE_TAPE_WRITE_FILE_HEAD_FAILED' => Xphp::$_lang['WEB_ERROR_DATA_STORAGE_TAPE_WRITE_FILE_HEAD_FAILED'],
        'DATA_STORAGE_TAPE_WRITE_DATA_FAILED' => Xphp::$_lang['WEB_ERROR_DATA_STORAGE_TAPE_WRITE_DATA_FAILED'],
        'DATA_STORAGE_TAPE_READ_STORAGE_HEAD_FAILED' => Xphp::$_lang['WEB_ERROR_DATA_STORAGE_TAPE_READ_STORAGE_HEAD_FAILED'],
        'DATA_STORAGE_TAPE_READ_FILE_HEAD_FAILED' => Xphp::$_lang['WEB_ERROR_DATA_STORAGE_TAPE_READ_FILE_HEAD_FAILED'],
        'DATA_STORAGE_TAPE_READ_FILE_TAIL_FAILED' => Xphp::$_lang['WEB_ERROR_DATA_STORAGE_TAPE_READ_FILE_TAIL_FAILED'],
        'DATA_STORAGE_TAPE_READ_DATA_FAILED' => Xphp::$_lang['WEB_ERROR_DATA_STORAGE_TAPE_READ_DATA_FAILED'],
        'DATA_STORAGE_TAPE_CARRIAGE_ALREADY_USING' => Xphp::$_lang['WEB_ERROR_DATA_STORAGE_TAPE_CARRIAGE_ALREADY_USING'],
        'DATA_STORAGE_TAPE_NOT_FOUND_AVAILABLE_CARRIAGE_TO_WRITE' => Xphp::$_lang['WEB_ERROR_DATA_STORAGE_TAPE_NOT_FOUND_AVAILABLE_CARRIAGE_TO_WRITE'],
        'DATA_STORAGE_TAPE_SPACE_NOT_ENOUGH' => Xphp::$_lang['WEB_ERROR_DATA_STORAGE_TAPE_SPACE_NOT_ENOUGH'],
        'DATA_STORAGE_TAPE_CANNOT_REWRITE_AND_NEED_ADD_IDLE' => Xphp::$_lang['WEB_ERROR_DATA_STORAGE_TAPE_CANNOT_REWRITE_AND_NEED_ADD_IDLE'],
        'DATA_STORAGE_OPERATION_ABORTED' => Xphp::$_lang['WEB_ERROR_DATA_STORAGE_OPERATION_ABORTED'],


        'BD_TAPE_NOT_FOUND_LIB' => Xphp::$_lang['WEB_ERROR_BD_TAPE_NOT_FOUND_LIB'],
        'BD_TAPE_NOT_FOUND_DRIVER' => Xphp::$_lang['WEB_ERROR_BD_TAPE_NOT_FOUND_DRIVER'],
        'BD_TAPE_NOT_FOUND_IDLE_DRIVER' => Xphp::$_lang['WEB_ERROR_BD_TAPE_NOT_FOUND_IDLE_DRIVER'],
        'BD_TAPE_NOT_FOUND_CARRIAGE' => Xphp::$_lang['WEB_ERROR_BD_TAPE_NOT_FOUND_CARRIAGE'],
        'BD_TAPE_NOT_FOUND_AVAILABLE_CARRIAGE' => Xphp::$_lang['WEB_ERROR_BD_TAPE_NOT_FOUND_AVAILABLE_CARRIAGE'],
        'BD_TAPE_LOAD_TAPE_CARRIAGE_FAILED' => Xphp::$_lang['WEB_ERROR_BD_TAPE_LOAD_TAPE_CARRIAGE_FAILED'],
        'BD_TAPE_UNLOAD_TAPE_CARRIAGE_FAILED' => Xphp::$_lang['WEB_ERROR_BD_TAPE_UNLOAD_TAPE_CARRIAGE_FAILED'],
        'BD_TAPE_OPEN_DRIVE_DEVICE_FAILED' => Xphp::$_lang['WEB_ERROR_BD_TAPE_OPEN_DRIVE_DEVICE_FAILED'],
        'BD_TAPE_DRIVER_BUSY' => Xphp::$_lang['WEB_ERROR_BD_TAPE_DRIVER_BUSY'],
        'BD_TAPE_NOT_AVAILABLE' => Xphp::$_lang['WEB_ERROR_BD_TAPE_NOT_AVAILABLE'],
        'BD_TAPE_IO_ERROR' => Xphp::$_lang['WEB_ERROR_BD_TAPE_IO_ERROR'],
        'BD_TAPE_CAPACITY_NOT_ENOUGH' => Xphp::$_lang['WEB_ERROR_BD_TAPE_CAPACITY_NOT_ENOUGH'],
        'BD_TAPE_DRIVER_FAILURE' => Xphp::$_lang['WEB_ERROR_BD_TAPE_DRIVER_FAILURE'],
        'BD_TAPE_MEDIA_MISSING' => Xphp::$_lang['WEB_ERROR_BD_TAPE_MEDIA_MISSING'],
        'BD_TAPE_MEDIA_DAMAGED' => Xphp::$_lang['WEB_ERROR_BD_TAPE_MEDIA_DAMAGED'],
        'BD_TAPE_TRANSFER_ERROR' => Xphp::$_lang['WEB_ERROR_BD_TAPE_TRANSFER_ERROR'],
        'BD_TAPE_CARRIAGE_ALREADY_OFFLINE' => Xphp::$_lang['WEB_ERROR_BD_TAPE_CARRIAGE_ALREADY_OFFLINE'],
        'BD_TAPE_NOT_FOUND_IDLE_IE_SLOT' => Xphp::$_lang['WEB_ERROR_BD_TAPE_NOT_FOUND_IDLE_IE_SLOT'],
        'BD_TAPE_NOT_FOUND_IDLE_SLOT' => Xphp::$_lang['WEB_ERROR_BD_TAPE_NOT_FOUND_IDLE_SLOT'],
        'BD_TAPE_MT_REWIND_FAILED' => Xphp::$_lang['WEB_ERROR_BD_TAPE_MT_REWIND_FAILED'],
        'BD_TAPE_MT_WRITE_EOF_FAILED' => Xphp::$_lang['WEB_ERROR_BD_TAPE_MT_WRITE_EOF_FAILED'],
        'BD_TAPE_MT_FORWARD_FSF_FAILED' => Xphp::$_lang['WEB_ERROR_BD_TAPE_MT_FORWARD_FSF_FAILED'],
        'BD_TAPE_MT_BACKWARD_BSF_FAILED' => Xphp::$_lang['WEB_ERROR_BD_TAPE_MT_BACKWARD_BSF_FAILED'],
        'BD_TAPE_MT_BACKWARD_BSFM_FAILED' => Xphp::$_lang['WEB_ERROR_BD_TAPE_MT_BACKWARD_BSFM_FAILED'],
        'BD_TAPE_WRITE_TAPE_HEAD_ERROR' => Xphp::$_lang['WEB_ERROR_BD_TAPE_WRITE_TAPE_HEAD_ERROR'],
        'BD_TAPE_READ_TAPE_HEAD_ERROR' => Xphp::$_lang['WEB_ERROR_BD_TAPE_READ_TAPE_HEAD_ERROR'],
        'BD_TAPE_WRITE_FILE_HEAD_ERROR' => Xphp::$_lang['WEB_ERROR_BD_TAPE_WRITE_FILE_HEAD_ERROR'],
        'BD_TAPE_READ_FILE_HEAD_ERROR' => Xphp::$_lang['WEB_ERROR_BD_TAPE_READ_FILE_HEAD_ERROR'],
        'BD_TAPE_WRITE_STORAGE_TAIL_FAILED' => Xphp::$_lang['WEB_ERROR_BD_TAPE_WRITE_STORAGE_TAIL_FAILED'],
        'BD_TAPE_READ_STORAGE_TAIL_FAILED' => Xphp::$_lang['WEB_ERROR_BD_TAPE_READ_STORAGE_TAIL_FAILED'],
        'BD_TAPE_WRITE_FILE_TAIL_FAILED' => Xphp::$_lang['WEB_ERROR_BD_TAPE_WRITE_FILE_TAIL_FAILED'],
        'BD_TAPE_READ_FILE_TAIL_FAILED' => Xphp::$_lang['WEB_ERROR_BD_TAPE_READ_FILE_TAIL_FAILED'],
        'BD_TAPE_RECORD_DB_INFO_OFFSET_FAILED' => Xphp::$_lang['WEB_ERROR_BD_TAPE_RECORD_DB_INFO_OFFSET_FAILED'],
        'BD_TAPE_READ_DB_INFO_OFFSET_FAILED' => Xphp::$_lang['WEB_ERROR_BD_TAPE_READ_DB_INFO_OFFSET_FAILED'],
        'BD_TAPE_INIT_TAPE_TAIL_CONTEXT_FAILED' => Xphp::$_lang['WEB_ERROR_BD_TAPE_INIT_TAPE_TAIL_CONTEXT_FAILED'],
        'BD_TAPE_WRITE_DATA_FAILED' => Xphp::$_lang['WEB_ERROR_BD_TAPE_WRITE_DATA_FAILED'],
        'BD_TAPE_READ_DATA_FAILED' => Xphp::$_lang['WEB_ERROR_BD_TAPE_READ_DATA_FAILED'],
        'BD_TAPE_MT_FORWARD_FSR_FAILED' => Xphp::$_lang['WEB_ERROR_BD_TAPE_MT_FORWARD_FSR_FAILED'],
        'BD_TAPE_MT_FORWARD_BSR_FAILED' => Xphp::$_lang['WEB_ERROR_BD_TAPE_MT_FORWARD_BSR_FAILED'],

        'VINFS_INIT_ERROR' => Xphp::$_lang['WEB_ERROR_VINFS_INIT_ERROR'],
        'VINFS_FILTER_FILE_NOT_EXIST' => Xphp::$_lang['WEB_ERROR_VINFS_FILTER_FILE_NOT_EXIST'],
        'VINFS_FILTER_FILE_ALREADY_EXIST' => Xphp::$_lang['WEB_ERROR_VINFS_FILTER_FILE_ALREADY_EXIST'],
        'VINFS_OPEN_NEW_BITMAP_FILE_ERROR' => Xphp::$_lang['WEB_ERROR_VINFS_OPEN_NEW_BITMAP_FILE_ERROR'],
        'VINFS_READ_NEW_BITMAP_FILE_ERROR' => Xphp::$_lang['WEB_ERROR_VINFS_READ_NEW_BITMAP_FILE_ERROR'],
        'VINFS_WRITE_NEW_BITMAP_FILE_ERROR' => Xphp::$_lang['WEB_ERROR_VINFS_WRITE_NEW_BITMAP_FILE_ERROR'],
        'VINFS_UNKNOWN_OPCODE_ERROR' => Xphp::$_lang['WEB_ERROR_VINFS_UNKNOWN_OPCODE_ERROR'],
        'VINFS_CREATE_NEW_BACKUP_PATH_ERROR' => Xphp::$_lang['WEB_ERROR_VINFS_CREATE_NEW_BACKUP_PATH_ERROR'],
        'VINFS_OPEN_BITMAP_FILE_ERROR' => Xphp::$_lang['WEB_ERROR_VINFS_OPEN_BITMAP_FILE_ERROR'],
        'VINFS_READ_BITMAP_FILE_ERROR' => Xphp::$_lang['WEB_ERROR_VINFS_READ_BITMAP_FILE_ERROR'],
        'VINFS_WRITE_BITMAP_FILE_ERROR' => Xphp::$_lang['WEB_ERROR_VINFS_WRITE_BITMAP_FILE_ERROR'],
        'VINFS_OPEN_BACKUP_FILE_ERROR' => Xphp::$_lang['WEB_ERROR_VINFS_OPEN_BACKUP_FILE_ERROR'],
        'VINFS_CREATE_VM_INSTANT_DIR_ERROR' => Xphp::$_lang['WEB_ERROR_VINFS_CREATE_VM_INSTANT_DIR_ERROR'],
        'VINFS_GET_NFS_STATUS_ERROR' => Xphp::$_lang['WEB_ERROR_VINFS_GET_NFS_STATUS_ERROR'],
        'VINFS_START_NFS_ERROR' => Xphp::$_lang['WEB_ERROR_VINFS_START_NFS_ERROR'],
        'VINFS_STOP_NFS_ERROR' => Xphp::$_lang['WEB_ERROR_VINFS_STOP_NFS_ERROR'],
        'VINFS_RESTART_NFS_ERROR' => Xphp::$_lang['WEB_ERROR_VINFS_RESTART_NFS_ERROR'],
        'VINFS_RESTART_RPCBIND_ERROR' => Xphp::$_lang['WEB_ERROR_VINFS_RESTART_RPCBIND_ERROR'],
        'VINFS_UMOUNT_VINFS_ERROR' => Xphp::$_lang['WEB_ERROR_VINFS_UMOUNT_VINFS_ERROR'],
        'VINFS_NOT_FOUND_TASK_ERROR' => Xphp::$_lang['WEB_ERROR_VINFS_NOT_FOUND_TASK_ERROR'],
        'VINFS_NOT_FOUND_DISK_ERROR' => Xphp::$_lang['WEB_ERROR_VINFS_NOT_FOUND_DISK_ERROR'],
        'VINFS_UNEXPORT_NFS_TABLE_ITEM_ERROR' => Xphp::$_lang['WEB_ERROR_VINFS_UNEXPORT_NFS_TABLE_ITEM_ERROR'],
        'VINFS_GET_NFS_TABLE_LIST_ERROR' => Xphp::$_lang['WEB_ERROR_VINFS_GET_NFS_TABLE_LIST_ERROR'],



        'VXEFS_INIT_ERROR' => Xphp::$_lang['WEB_ERROR_VXEFS_INIT_ERROR'],
        'VXEFS_MOUNT_VXEFS_ERROR' => Xphp::$_lang['WEB_ERROR_VXEFS_MOUNT_VXEFS_ERROR'],
        'VXEFS_UMOUNT_VXEFS_ERROR' => Xphp::$_lang['WEB_ERROR_VXEFS_UMOUNT_VXEFS_ERROR'],
        'VXEFS_TASK_NOT_FOUND_ERROR' => Xphp::$_lang['WEB_ERROR_VXEFS_TASK_NOT_FOUND_ERROR'],
        'VXEFS_UNKNOWN_OPCODE_ERROR' => Xphp::$_lang['WEB_ERROR_VXEFS_UNKNOWN_OPCODE_ERROR'],
        'VXEFS_FILTER_DISK_ALREADY_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_VXEFS_FILTER_DISK_ALREADY_EXIST_ERROR'],
        'VXEFS_FILTER_DISK_NOT_EXIST_ERROR' => Xphp::$_lang['WEB_ERROR_VXEFS_FILTER_DISK_NOT_EXIST_ERROR'],
        'VXEFS_CREATE_CACHE_DIR_ERROR' => Xphp::$_lang['WEB_ERROR_VXEFS_CREATE_CACHE_DIR_ERROR'],
        'VXEFS_ORIGINAL_BAT_INDEX_NOT_FOUND_ERROR' => Xphp::$_lang['WEB_ERROR_VXEFS_ORIGINAL_BAT_INDEX_NOT_FOUND_ERROR'],
        'VXEFS_FUSE_UNEXPECTED_READ_REQUEST_ERROR' => Xphp::$_lang['WEB_ERROR_VXEFS_FUSE_UNEXPECTED_READ_REQUEST_ERROR'],

        //WEB
        'PF_USER_USER_PASS_ERROR' => Xphp::$_lang['WEB_ERROR_PF_USER_USER_PASS_ERROR'],
        'PF_USER_LOGIN_LOCK_ERROR' => Xphp::$_lang['WEB_ERROR_PF_USER_LOGIN_LOCK_ERROR'],

        'PF_SETTING_NOTICE_SMSMODEM_CONNECT_DB_ERROR' => Xphp::$_lang['WEB_ERROR_PF_SETTING_NOTICE_SMSMODEM_CONNECT_DB_ERROR'],
        'PF_SETTING_NOTICE_SMSMODEM_INSERT_DB_ERROR' => Xphp::$_lang['WEB_ERROR_PF_SETTING_NOTICE_SMSMODEM_INSERT_DB_ERROR'],

        'PF_SOCKET_CREATE_ERROR' => Xphp::$_lang['WEB_ERROR_PF_SOCKET_CREATE_ERROR'],
        'PF_SOCKET_CONNECT_ERROR' => Xphp::$_lang['WEB_ERROR_PF_SOCKET_CONNECT_ERROR'],
        'PF_SOCKET_SEND_ERROR' => Xphp::$_lang['WEB_ERROR_PF_SOCKET_SEND_ERROR'],
        'PF_SOCKET_RECV_ERROR' => Xphp::$_lang['WEB_ERROR_PF_SOCKET_RECV_ERROR'],
        'PF_SOCKET_GET_OP_STATUS_ERROR' => Xphp::$_lang['WEB_ERROR_PF_SOCKET_GET_OP_STATUS_ERROR'],
        'PF_SOCKET_GET_OP_TIMEOUT' => Xphp::$_lang['WEB_ERROR_PF_SOCKET_GET_OP_TIMEOUT'],
        'PF_SOCKET_OP_STATUS_ERROR' => Xphp::$_lang['WEB_ERROR_PF_SOCKET_OP_STATUS_ERROR'],
        'PF_SOCKET_SYSTEM_SERVICE_ERROR' => Xphp::$_lang['WEB_ERROR_PF_SOCKET_SYSTEM_SERVICE_ERROR'],
        'PF_SOCKET_CURL_ERROR' => Xphp::$_lang['WEB_ERROR_PF_CONNECT_HOST_FAIL_CHECK_NETWORK'],
        'UPDATE_PRESERVE' => Xphp::$_lang['WEB_SETTINGS_UPDATE_PRESERVE'],
        'UPDATE_PARAMS_ERROR' => Xphp::$_lang['WEB_SETTINGS_UPDATE_PARAMS_ERROR'],
        'UPDATE_PARAMS_VERSION_ERROR' => Xphp::$_lang['WEB_SETTINGS_UPDATE_PARAMS_VERSION_ERROR'],
        'UPDATE_BLACK_LIST_ERROR' => Xphp::$_lang['WEB_SETTINGS_UPDATE_BLACK_LIST_ERROR'],
        'UPDATE_NETWORK_ERROR' => Xphp::$_lang['WEB_SETTINGS_UPDATE_PING_NETWORK_ERROR'],
        'UPDATE_REQUEST_ERROR' => Xphp::$_lang['WEB_SETTINGS_UPDATE_GET_NETWORK_ERROR'],

        'M365_SERVER_ORGANIZATION_HAS_BEEN_ADDED' => Xphp::$_lang['WEB_M365_SERVER_ORGANIZATION_HAS_BEEN_ADDED'],
        'M365_SERVER_CHECK_ORGANIZATION_ERROR' => Xphp::$_lang['WEB_M365_SERVER_CHECK_ORGANIZATION_ERROR'],
        'M365_SERVER_GENERATE_PUBLIC_CERT_ERROR' => Xphp::$_lang['WEB_M365_SERVER_GENERATE_PUBLIC_CERT_ERROR'],
        'M365_SERVER_GET_AZURE_CLI_OUTPUT_ERROR' => Xphp::$_lang['WEB_M365_SERVER_GET_AZURE_CLI_OUTPUT_ERROR'],
        'M365_SERVER_GET_CMD_OUTPUT_ERROR' => Xphp::$_lang['WEB_M365_SERVER_GET_CMD_OUTPUT_ERROR'],
        'M365_SERVER_AZURE_AD_LOGIN_TOKEN_EXPIRED' => Xphp::$_lang['WEB_M365_SERVER_AZURE_AD_LOGIN_TOKEN_EXPIRED'],
        'M365_SERVER_CREATE_AZURE_AD_APP_ERROR' => Xphp::$_lang['WEB_M365_SERVER_CREATE_AZURE_AD_APP_ERROR'],
        'M365_SERVER_SET_AZURE_AD_APP_SECRET_ERROR' => Xphp::$_lang['WEB_M365_SERVER_SET_AZURE_AD_APP_SECRET_ERROR'],
        'M365_SERVER_SET_AZURE_AD_APP_CERT_ERROR' => Xphp::$_lang['WEB_M365_SERVER_SET_AZURE_AD_APP_CERT_ERROR'],
        'M365_SERVER_SET_AZURE_AD_APP_PERMISSION_ERROR' => Xphp::$_lang['WEB_M365_SERVER_SET_AZURE_AD_APP_PERMISSION_ERROR'],
        'M365_SERVER_GET_ORGANIZATION_INFO_ERROR' => Xphp::$_lang['WEB_M365_SERVER_GET_ORGANIZATION_INFO_ERROR'],
        'M365_SERVER_DELETE_AGENT_APP_ERROR' => Xphp::$_lang['WEB_M365_SERVER_DELETE_AGENT_APP_ERROR'],
        'M365_SERVER_DELETE_AZURE_AD_APP_ERROR' => Xphp::$_lang['WEB_M365_SERVER_DELETE_AZURE_AD_APP_ERROR'],
        'M365_SERVER_ADD_M365_ORGANIZATION_ERROR' => Xphp::$_lang['WEB_M365_SERVER_ADD_M365_ORGANIZATION_ERROR'],
        'M365_SERVER_EDIT_M365_ORGANIZATION_ERROR' => Xphp::$_lang['WEB_M365_SERVER_EDIT_M365_ORGANIZATION_ERROR'],
        'M365_SERVER_DELETE_M365_ORGANIZATION_ERROR' => Xphp::$_lang['WEB_M365_SERVER_DELETE_M365_ORGANIZATION_ERROR'],
        'M365_SERVER_GET_BACKUP_INFO_ERROR' => Xphp::$_lang['WEB_M365_SERVER_GET_BACKUP_INFO_ERROR'],
        'M365_SERVER_GET_RECOVERY_INFO_ERROR' => Xphp::$_lang['WEB_M365_SERVER_GET_RECOVERY_INFO_ERROR'],
        'M365_SERVER_BD_AGENT_NOT_EXIST_ERROR' => Xphp::$_lang['WEB_M365_SERVER_BD_AGENT_NOT_EXIST_ERROR'],
        'M365_SERVER_NOT_FIND_AVAILABLE_RPC_CLIENT_ERROR' => Xphp::$_lang['WEB_M365_SERVER_NOT_FIND_AVAILABLE_RPC_CLIENT_ERROR'],
        'M365_SERVER_CREATE_BACKUP_DIR_ERROR' => Xphp::$_lang['WEB_M365_SERVER_CREATE_BACKUP_DIR_ERROR'],
        'M365_SERVER_FIND_BACKUP_FILE_ID_ERROR' => Xphp::$_lang['WEB_M365_SERVER_FIND_BACKUP_FILE_ID_ERROR'],
        'M365_SERVER_FIND_BACKUP_TIMEPOINT_ERROR' => Xphp::$_lang['WEB_M365_SERVER_FIND_BACKUP_TIMEPOINT_ERROR'],
        'M365_SERVER_SAVE_SELF_EXPLAN_FILE_ERROR' => Xphp::$_lang['WEB_M365_SERVER_SAVE_SELF_EXPLAN_FILE_ERROR'],
        'M365_SERVER_CONTAINER_FULL_ERROR' => Xphp::$_lang['WEB_M365_SERVER_CONTAINER_FULL_ERROR'],
        'M365_SERVER_GET_RECOVERY_TOTAL_SIZE_ERROR' => Xphp::$_lang['WEB_M365_SERVER_GET_RECOVERY_TOTAL_SIZE_ERROR'],
        'M365_SERVER_ENCRYPT_INFO_CHANGED_ERROR' => Xphp::$_lang['WEB_M365_SERVER_ENCRYPT_INFO_CHANGED_ERROR'],
        'M365_SERVER_FIND_BACKUP_OBJECT_ERROR' => Xphp::$_lang['WEB_M365_SERVER_FIND_BACKUP_OBJECT_ERROR'],
        'M365_SERVER_START_PROXY_ERROR' => Xphp::$_lang['WEB_M365_SERVER_START_PROXY_ERROR'],
        'M365_SERVER_FIND_PROXY_PROCESS_ERROR' => Xphp::$_lang['WEB_M365_SERVER_FIND_PROXY_PROCESS_ERROR'],
        'M365_SERVER_CONNECT_PROXY_PROCESS_ERROR' => Xphp::$_lang['WEB_M365_SERVER_CONNECT_PROXY_PROCESS_ERROR'],
        'M365_SERVER_STOP_PROXY_PROCESS_ERROR' => Xphp::$_lang['WEB_M365_SERVER_STOP_PROXY_PROCESS_ERROR'],
        'M365_SERVER_FORCE_KILL_PROXY_PROCESS_ERROR' => Xphp::$_lang['WEB_M365_SERVER_FORCE_KILL_PROXY_PROCESS_ERROR'],
        'M365_SERVER_NOT_CONNECT_PROXY_PROCESS_ERROR' => Xphp::$_lang['WEB_M365_SERVER_NOT_CONNECT_PROXY_PROCESS_ERROR'],
        'M365_SERVER_SEND_PACEKT_TO_PROXY_PROCESS_ERROR' => Xphp::$_lang['WEB_M365_SERVER_SEND_PACEKT_TO_PROXY_PROCESS_ERROR'],
        'M365_SERVER_RECV_PACEKT_FROM_PROXY_PROCESS_ERROR' => Xphp::$_lang['WEB_M365_SERVER_RECV_PACEKT_FROM_PROXY_PROCESS_ERROR'],
        'M365_SERVER_UNKNOWN_RPC_PRIVATE_OPCODE_TYPE' => Xphp::$_lang['WEB_M365_SERVER_UNKNOWN_RPC_PRIVATE_OPCODE_TYPE'],
        'M365_SERVER_USER_CAN_NOT_BACKUP' => Xphp::$_lang['WEB_M365_SERVER_USER_CAN_NOT_BACKUP'],
        'M365_SERVER_TRANSPORT_THREAD_EXIT_BY_USER_SCANNER_THREAD_ERROR' => Xphp::$_lang['WEB_M365_SERVER_TRANSPORT_THREAD_EXIT_BY_USER_SCANNER_THREAD_ERROR'],
        'M365_SERVER_CONNECT_USER_ERROR' => Xphp::$_lang['WEB_M365_SERVER_CONNECT_USER_ERROR'],
        'M365_SERVER_TIMEPOINT_NOT_EXIST_ERROR' => Xphp::$_lang['WEB_M365_SERVER_TIMEPOINT_NOT_EXIST_ERROR'],
        'M365_SERVER_STORAGE_CHANGED_ERROR' => Xphp::$_lang['WEB_M365_SERVER_STORAGE_CHANGED_ERROR'],
        'M365_SERVER_UPDATE_USER_DELTA_TOKEN_ERROR' => Xphp::$_lang['WEB_M365_SERVER_UPDATE_USER_DELTA_TOKEN_ERROR'],
        'M365_SERVER_UPDATE_GROUP_DELTA_TOKEN_ERROR' => Xphp::$_lang['WEB_M365_SERVER_UPDATE_GROUP_DELTA_TOKEN_ERROR'],
        'M365_SERVER_USER_NOT_EXISTS_ERROR' => Xphp::$_lang['WEB_M365_SERVER_USER_NOT_EXISTS_ERROR'],
        'M365_SERVER_COMPRESS_INFO_CHANGED_ERROR' => Xphp::$_lang['WEB_M365_SERVER_COMPRESS_INFO_CHANGED_ERROR'],
        'M365_SERVER_REFRESH_ORGANIZATION_ERROR' => Xphp::$_lang['WEB_M365_SERVER_REFRESH_ORGANIZATION_ERROR'],
        'M365_SERVER_AUTH_IS_FULL_ERROR' => Xphp::$_lang['WEB_M365_SERVER_AUTH_IS_FULL_ERROR'],
        'M365_SERVER_USER_IS_IN_OTHER_TASKS_ERROR' => Xphp::$_lang['WEB_M365_SERVER_USER_IS_IN_OTHER_TASKS_ERROR'],
        'M365_SERVER_META_FILE_EXIST_ERROR' => Xphp::$_lang['WEB_M365_SERVER_META_FILE_EXIST_ERROR'],
        'M365_SERVER_NODE_CHANGED_ERROR' => Xphp::$_lang['WEB_M365_SERVER_NODE_CHANGED_ERROR'],
        'M365_SERVER_UNKNOWN_EXCH_DATA_TYPE' => Xphp::$_lang['WEB_M365_SERVER_UNKNOWN_EXCH_DATA_TYPE'],
        'M365_SERVER_GET_EXCH_DATA_ERROR' => Xphp::$_lang['WEB_M365_SERVER_GET_EXCH_DATA_ERROR'],
        'M365_CREATE_USER_INDEX_TABLE_IN_SQLITE_ERROR' => Xphp::$_lang['WEB_M365_CREATE_USER_INDEX_TABLE_IN_SQLITE_ERROR'],
        'M365_SERVER_INSERT_EXCH_INDEX_DATA_TO_SQLITE_ERROR' => Xphp::$_lang['WEB_M365_SERVER_INSERT_EXCH_INDEX_DATA_TO_SQLITE_ERROR'],
        'M365_SERVER_GET_MESSAGE_MIMECONTENT_ERROR' => Xphp::$_lang['WEB_M365_SERVER_GET_MESSAGE_MIMECONTENT_ERROR'],
        'M365_SERVER_GET_MESSAGE_XML_DATA_ERROR' => Xphp::$_lang['WEB_M365_SERVER_GET_MESSAGE_XML_DATA_ERROR'],
        'M365_SERVER_GET_ITEM_STRUCTCONTENT_DATA_ERROR' => Xphp::$_lang['WEB_M365_SERVER_GET_ITEM_STRUCTCONTENT_DATA_ERROR'],
        'M365_SERVER_FIND_INDEX_DB_FILE_ERROR' => Xphp::$_lang['WEB_M365_SERVER_FIND_INDEX_DB_FILE_ERROR'],
        'M365_SERVER_SCAN_DB_FILE_ERROR' => Xphp::$_lang['WEB_M365_SERVER_SCAN_DB_FILE_ERROR'],
        'M365_SERVER_MOVE_NEW_DB_FILE_ERROR' => Xphp::$_lang['WEB_M365_SERVER_MOVE_NEW_DB_FILE_ERROR'],
        'M365_SERVER_UNKNOWN_EXCH_RECOVERY_OBJECT_TYPE_ERROR' => Xphp::$_lang['WEB_M365_SERVER_UNKNOWN_EXCH_RECOVERY_OBJECT_TYPE_ERROR'],
        'M365_SERVER_USE_SMTP_SEND_EMAIL_ERROR' => Xphp::$_lang['WEB_M365_SERVER_USE_SMTP_SEND_EMAIL_ERROR'],
        'M365_SERVER_PARSE_MIMECONTENT_ERROR' => Xphp::$_lang['WEB_M365_SERVER_PARSE_MIMECONTENT_ERROR'],
        'M365_SERVER_GET_EXCH_BACKUP_DATA_ERROR' => Xphp::$_lang['WEB_M365_SERVER_GET_EXCH_BACKUP_DATA_ERROR'],
        'M365_SERVER_GET_SMTP_CONFIG_ERROR' => Xphp::$_lang['WEB_M365_SERVER_GET_SMTP_CONFIG_ERROR'],
        'M365_SERVER_RESOURCE_NOT_EXITS_ERROR' => Xphp::$_lang['WEB_M365_SERVER_RESOURCE_NOT_EXITS_ERROR'],
        'M365_SERVER_GET_RESOURCE_ERROR' => Xphp::$_lang['WEB_M365_SERVER_GET_RESOURCE_ERROR'],
        'M365_SERVER_USER_NOT_AUTH' => Xphp::$_lang['WEB_M365_SERVER_USER_NOT_AUTH'],

        'KUBE_CLIENT_CLUSTER_GET_VERSION_ERROR' => Xphp::$_lang['WEB_KUBE_CLIENT_CLUSTER_GET_VERSION_ERROR'],
        'KUBE_CLIENT_CLUSTER_API_RESPONSE_EMPTY_ERROR' => Xphp::$_lang['WEB_KUBE_CLIENT_CLUSTER_API_RESPONSE_EMPTY_ERROR'],
        'KUBE_CLIENT_CLUSTER_API_RESPONSE_STATUS_IS_FAILURE_ERROR' => Xphp::$_lang['WEB_KUBE_CLIENT_CLUSTER_API_RESPONSE_STATUS_IS_FAILURE_ERROR'],
        'KUBE_CLIENT_CLUSTER_API_RESPONSE_HTTP_CODE_400_BAD_REQUEST_ERROR' => Xphp::$_lang['WEB_KUBE_CLIENT_CLUSTER_API_RESPONSE_HTTP_CODE_400_BAD_REQUEST_ERROR'],
        'KUBE_CLIENT_CLUSTER_API_RESPONSE_HTTP_CODE_401_UNAUTHORIZED_ERROR' => Xphp::$_lang['WEB_KUBE_CLIENT_CLUSTER_API_RESPONSE_HTTP_CODE_401_UNAUTHORIZED_ERROR'],
        'KUBE_CLIENT_CLUSTER_API_RESPONSE_HTTP_CODE_403_FORBIDDEN_ERROR' => Xphp::$_lang['WEB_KUBE_CLIENT_CLUSTER_API_RESPONSE_HTTP_CODE_403_FORBIDDEN_ERROR'],
        'KUBE_CLIENT_CLUSTER_API_RESPONSE_HTTP_CODE_404_NOT_FOUND_ERROR' => Xphp::$_lang['WEB_KUBE_CLIENT_CLUSTER_API_RESPONSE_HTTP_CODE_404_NOT_FOUND_ERROR'],
        'KUBE_CLIENT_CLUSTER_API_RESPONSE_HTTP_CODE_409_CONFLICT_ERROR' => Xphp::$_lang['WEB_KUBE_CLIENT_CLUSTER_API_RESPONSE_HTTP_CODE_409_CONFLICT_ERROR'],
        'KUBE_CLIENT_CLUSTER_API_RESPONSE_HTTP_CODE_4XX_FAILED_ERROR' => Xphp::$_lang['WEB_KUBE_CLIENT_CLUSTER_API_RESPONSE_HTTP_CODE_4XX_FAILED_ERROR'],
        'KUBE_CLIENT_CLUSTER_API_RESPONSE_HTTP_CODE_5XX_SERVER_ERROR' => Xphp::$_lang['WEB_KUBE_CLIENT_CLUSTER_API_RESPONSE_HTTP_CODE_5XX_SERVER_ERROR'],
        'KUBE_CLIENT_CLUSTER_API_RESPONSE_HTTP_CODE_GENERIC_ERROR' => Xphp::$_lang['WEB_KUBE_CLIENT_CLUSTER_API_RESPONSE_HTTP_CODE_GENERIC_ERROR'],
        'KUBE_CLIENT_CLUSTER_LOAD_KUBE_CONFIG_FAILED_ERROR' => Xphp::$_lang['WEB_KUBE_CLIENT_CLUSTER_LOAD_KUBE_CONFIG_FAILED_ERROR'],
        'KUBE_CLIENT_CLUSTER_CONNECTION_FAILED_ERROR' => Xphp::$_lang['WEB_KUBE_CLIENT_CLUSTER_CONNECTION_FAILED_ERROR'],
        'KUBE_CLIENT_CLUSTER_RESOURCE_NOT_EXIST_ERROR' => Xphp::$_lang['WEB_KUBE_CLIENT_CLUSTER_RESOURCE_NOT_EXIST_ERROR'],
        'KUBE_CLIENT_CLUSTER_GET_NODES_ERROR' => Xphp::$_lang['WEB_KUBE_CLIENT_CLUSTER_GET_NODES_ERROR'],
        'KUBE_CLIENT_CLUSTER_GET_NAMESPACES_ERROR' => Xphp::$_lang['WEB_KUBE_CLIENT_CLUSTER_GET_NAMESPACES_ERROR'],
        'KUBE_CLIENT_CLUSTER_GET_PVCS_ERROR' => Xphp::$_lang['WEB_KUBE_CLIENT_CLUSTER_GET_PVCS_ERROR'],
        'KUBE_CLIENT_CLUSTER_GET_GROUP_VERSIONS_ERROR' => Xphp::$_lang['WEB_KUBE_CLIENT_CLUSTER_GET_GROUP_VERSIONS_ERROR'],
        'KUBE_CLIENT_CLUSTER_GET_CRD_RESOURCE_TYPES_ERROR' => Xphp::$_lang['WEB_KUBE_CLIENT_CLUSTER_GET_CRD_RESOURCE_TYPES_ERROR'],
        'KUBE_CLIENT_CLUSTER_GET_CORE_RESOURCE_TYPES_ERROR' => Xphp::$_lang['WEB_KUBE_CLIENT_CLUSTER_GET_CORE_RESOURCE_TYPES_ERROR'],
        'KUBE_CLIENT_CLUSTER_EXEC_FAILED_ERROR' => Xphp::$_lang['WEB_KUBE_CLIENT_CLUSTER_EXEC_FAILED_ERROR'],
        'KUBE_CLIENT_CLUSTER_RESOURCES_LIST_FAILED_ERROR' => Xphp::$_lang['WEB_KUBE_CLIENT_CLUSTER_RESOURCES_LIST_FAILED_ERROR'],
        'KUBE_CLIENT_INVALID_RESOURCE_JSON_RESPONSE_ERROR' => Xphp::$_lang['WEB_KUBE_CLIENT_INVALID_RESOURCE_JSON_RESPONSE_ERROR'],
        'KUBE_CLIENT_MISSING_KEY_IN_JSON_RESOURCE_RESPONSE_ERROR' => Xphp::$_lang['WEB_KUBE_CLIENT_MISSING_KEY_IN_JSON_RESOURCE_RESPONSE_ERROR'],
        'KUBE_CLIENT_VERSION_IS_EMPTY_ERROR' => Xphp::$_lang['WEB_KUBE_CLIENT_VERSION_IS_EMPTY_ERROR'],
        'KUBE_CLIENT_KIND_IS_EMPTY_ERROR' => Xphp::$_lang['WEB_KUBE_CLIENT_KIND_IS_EMPTY_ERROR'],
        'KUBE_CLIENT_GET_RESOURCES_LIST_ERROR' => Xphp::$_lang['WEB_KUBE_CLIENT_GET_RESOURCES_LIST_ERROR'],
        'KUBE_CLIENT_NO_WORKER_POD_FOUND_ERROR' => Xphp::$_lang['WEB_KUBE_CLIENT_NO_WORKER_POD_FOUND_ERROR'],
        'KUBE_CLIENT_NO_NAMESPACE_FOUND_ERROR' => Xphp::$_lang['WEB_KUBE_CLIENT_NO_NAMESPACE_FOUND_ERROR'],
        'KUBE_CLIENT_INVALID_RESOURCE_CATEGORY_ERROR' => Xphp::$_lang['WEB_KUBE_CLIENT_INVALID_RESOURCE_CATEGORY_ERROR'],
        'KUBE_CLIENT_START_PROCESS_FAILED_ERROR' => Xphp::$_lang['WEB_KUBE_CLIENT_START_PROCESS_FAILED_ERROR'],
        'KUBE_CLIENT_WAIT_PROCESS_FAILED_ERROR' => Xphp::$_lang['WEB_KUBE_CLIENT_WAIT_PROCESS_FAILED_ERROR'],
        'KUBE_CLIENT_GET_ROOT_PATH_OF_POD_FAILED_ERROR' => Xphp::$_lang['WEB_KUBE_CLIENT_GET_ROOT_PATH_OF_POD_FAILED_ERROR'],
        'KUBE_CLIENT_CREATE_TEMP_DIR_FAILED_ERROR' => Xphp::$_lang['WEB_KUBE_CLIENT_CREATE_TEMP_DIR_FAILED_ERROR'],
        'KUBE_CLIENT_GET_MOUNT_POINT_ERROR' => Xphp::$_lang['WEB_KUBE_CLIENT_GET_MOUNT_POINT_ERROR'],
        'KUBE_CLIENT_GET_FILE_SYSTEM_SCANNER_ERROR' => Xphp::$_lang['WEB_KUBE_CLIENT_GET_FILE_SYSTEM_SCANNER_ERROR'],
        'KUBE_CLIENT_CREATE_DIRECTORY_FAILED_ERROR' => Xphp::$_lang['WEB_KUBE_CLIENT_CREATE_DIRECTORY_FAILED_ERROR'],
        'KUBE_CLIENT_OPEN_FILE_FOR_WRITE_FAILED_ERROR' => Xphp::$_lang['WEB_KUBE_CLIENT_OPEN_FILE_FOR_WRITE_FAILED_ERROR'],
        'KUBE_CLIENT_WRITE_FILE_FAILED_ERROR' => Xphp::$_lang['WEB_KUBE_CLIENT_WRITE_FILE_FAILED_ERROR'],
        'KUBE_CLIENT_OPEN_VOLUME_DEV_FAILED_ERROR' => Xphp::$_lang['WEB_KUBE_CLIENT_OPEN_VOLUME_DEV_FAILED_ERROR'],
        'KUBE_CLIENT_GET_VOLUME_DEV_TOTAL_SIZE_FAILED_ERROR' => Xphp::$_lang['WEB_KUBE_CLIENT_GET_VOLUME_DEV_TOTAL_SIZE_FAILED_ERROR'],
        'KUBE_CLIENT_GET_VOLUME_DEV_BITMAP_FAILED_ERROR' => Xphp::$_lang['WEB_KUBE_CLIENT_GET_VOLUME_DEV_BITMAP_FAILED_ERROR'],
        'KUBE_CLIENT_READ_VOLUME_DEV_DATA_FAILED_ERROR' => Xphp::$_lang['WEB_KUBE_CLIENT_READ_VOLUME_DEV_DATA_FAILED_ERROR'],
        'KUBE_CLIENT_WRITE_VOLUME_DEV_DATA_FAILED_ERROR' => Xphp::$_lang['WEB_KUBE_CLIENT_WRITE_VOLUME_DEV_DATA_FAILED_ERROR'],
        'KUBE_CLIENT_READ_FILE_FAILED_ERROR' => Xphp::$_lang['WEB_KUBE_CLIENT_READ_FILE_FAILED_ERROR'],
        'KUBE_SERVER_CLIENT_NETWORK_CONNECTION_ERROR' => Xphp::$_lang['WEB_KUBE_SERVER_CLIENT_NETWORK_CONNECTION_ERROR'],
        'KUBE_SERVER_BUFFER_FORMAT_ERROR' => Xphp::$_lang['WEB_KUBE_SERVER_BUFFER_FORMAT_ERROR'],
        'KUBE_SERVER_JSON_PARSE_JSON_ERROR' => Xphp::$_lang['WEB_KUBE_SERVER_JSON_PARSE_JSON_ERROR'],
        'KUBE_SERVER_JSON_KEY_EMPTY_ERROR' => Xphp::$_lang['WEB_KUBE_SERVER_JSON_KEY_EMPTY_ERROR'],
        'KUBE_SERVER_JSON_KEY_IS_NOT_EXISTS_ERROR' => Xphp::$_lang['WEB_KUBE_SERVER_JSON_KEY_IS_NOT_EXISTS_ERROR'],
        'KUBE_SERVER_JSON_VALUE_TYPE_INCORRECT_ERROR' => Xphp::$_lang['WEB_KUBE_SERVER_JSON_VALUE_TYPE_INCORRECT_ERROR'],
        'KUBE_SERVER_JSON_VALUE_TYPE_INCORRECT_OF_KEY_ERROR' => Xphp::$_lang['WEB_KUBE_SERVER_JSON_VALUE_TYPE_INCORRECT_OF_KEY_ERROR'],
        'KUBE_SERVER_JSON_VALUE_IS_EMPTY_ERROR' => Xphp::$_lang['WEB_KUBE_SERVER_JSON_VALUE_IS_EMPTY_ERROR'],
        'KUBE_SERVER_JSON_VALUE_IS_NOT_STRING_OR_CONVERTIBLE_ERROR' => Xphp::$_lang['WEB_KUBE_SERVER_JSON_VALUE_IS_NOT_STRING_OR_CONVERTIBLE_ERROR'],
        'KUBE_SERVER_JSON_VALUE_IS_NOT_BOOL_OR_CONVERTIBLE_ERROR' => Xphp::$_lang['WEB_KUBE_SERVER_JSON_VALUE_IS_NOT_BOOL_OR_CONVERTIBLE_ERROR'],
        'KUBE_SERVER_JSON_VALUE_IS_NOT_INT_OR_CONVERTIBLE_ERROR' => Xphp::$_lang['WEB_KUBE_SERVER_JSON_VALUE_IS_NOT_INT_OR_CONVERTIBLE_ERROR'],
        'KUBE_SERVER_YAML_CONVERT_JSON_TO_YAML_FAILED_ERROR' => Xphp::$_lang['WEB_KUBE_SERVER_YAML_CONVERT_JSON_TO_YAML_FAILED_ERROR'],
        'KUBE_SERVER_YAML_CONVERT_YAML_TO_JSON_PARSE_FAILED_ERROR' => Xphp::$_lang['WEB_KUBE_SERVER_YAML_CONVERT_YAML_TO_JSON_PARSE_FAILED_ERROR'],
        'KUBE_SERVER_YAML_CONVERT_YAML_TO_JSON_FAILED_ERROR' => Xphp::$_lang['WEB_KUBE_SERVER_YAML_CONVERT_YAML_TO_JSON_FAILED_ERROR'],
        'KUBE_SERVER_PROCESS_CREATE_PIPE_ERROR' => Xphp::$_lang['WEB_KUBE_SERVER_PROCESS_CREATE_PIPE_ERROR'],
        'KUBE_SERVER_PROCESS_FORK_FAILED_ERROR' => Xphp::$_lang['WEB_KUBE_SERVER_PROCESS_FORK_FAILED_ERROR'],
        'KUBE_SERVER_CLUSTER_ALREADY_EXIST_ERROR' => Xphp::$_lang['WEB_KUBE_SERVER_CLUSTER_ALREADY_EXIST_ERROR'],
        'KUBE_SERVER_CLUSTER_OBTAIN_CLUSTER_INFO_ERROR' => Xphp::$_lang['WEB_KUBE_SERVER_CLUSTER_OBTAIN_CLUSTER_INFO_ERROR'],
        'KUBE_SERVER_CLUSTER_HAS_TASK_ERROR' => Xphp::$_lang['WEB_KUBE_SERVER_CLUSTER_HAS_TASK_ERROR'],
        'KUBE_SERVER_CLUSTER_DELETE_CLUSTER_ERROR' => Xphp::$_lang['WEB_KUBE_SERVER_CLUSTER_DELETE_CLUSTER_ERROR'],
        'KUBE_SERVER_CLUSTER_NOT_SUPPORT_DELETE_ERROR' => Xphp::$_lang['WEB_KUBE_SERVER_CLUSTER_NOT_SUPPORT_DELETE_ERROR'],
        'KUBE_DEPLOYMENT_INVALID_KUBE_CONFIG_ERROR' => Xphp::$_lang['WEB_KUBE_DEPLOYMENT_INVALID_KUBE_CONFIG_ERROR'],
        'KUBE_DEPLOYMENT_SYSTEM_COMMAND_EXECUTE_ERROR' => Xphp::$_lang['WEB_KUBE_DEPLOYMENT_SYSTEM_COMMAND_EXECUTE_ERROR'],
        'KUBE_DEPLOYMENT_SYSTEM_COMMAND_EXECUTE_EMPTY_OUTPUT_ERROR' => Xphp::$_lang['WEB_KUBE_DEPLOYMENT_SYSTEM_COMMAND_EXECUTE_EMPTY_OUTPUT_ERROR'],
        'KUBE_DEPLOYMENT_SSH_CONNECTION_ERROR' => Xphp::$_lang['WEB_KUBE_DEPLOYMENT_SSH_CONNECTION_ERROR'],
        'KUBE_DEPLOYMENT_SSH_AUTHENTICATION_ERROR' => Xphp::$_lang['WEB_KUBE_DEPLOYMENT_SSH_AUTHENTICATION_ERROR'],
        'KUBE_DEPLOYMENT_EXECUTE_SSH_COMMAND_ERROR' => Xphp::$_lang['WEB_KUBE_DEPLOYMENT_EXECUTE_SSH_COMMAND_ERROR'],
        'KUBE_DEPLOYMENT_HELM_COMMAND_BIN_NOT_FOUND_ERROR' => Xphp::$_lang['WEB_KUBE_DEPLOYMENT_HELM_COMMAND_BIN_NOT_FOUND_ERROR'],
        'KUBE_DEPLOYMENT_OBTAIN_HELM_COMMAND_VERSION_ERROR' => Xphp::$_lang['WEB_KUBE_DEPLOYMENT_OBTAIN_HELM_COMMAND_VERSION_ERROR'],
        'KUBE_DEPLOYMENT_CLUSTER_UNREACHABLE_IN_HELM_WITH_KUBE_CONFIG_ERROR' => Xphp::$_lang['WEB_KUBE_DEPLOYMENT_CLUSTER_UNREACHABLE_IN_HELM_WITH_KUBE_CONFIG_ERROR'],
        'KUBE_DEPLOYMENT_NO_USABLE_SERVER_IP_FOR_HELM_CHARTS_ERROR' => Xphp::$_lang['WEB_KUBE_DEPLOYMENT_NO_USABLE_SERVER_IP_FOR_HELM_CHARTS_ERROR'],
        'KUBE_DEPLOYMENT_HELM_REPO_ADD_FAILED_ERROR' => Xphp::$_lang['WEB_KUBE_DEPLOYMENT_HELM_REPO_ADD_FAILED_ERROR'],
        'KUBE_DEPLOYMENT_HELM_SEARCH_REPO_FAILED_ERROR' => Xphp::$_lang['WEB_KUBE_DEPLOYMENT_HELM_SEARCH_REPO_FAILED_ERROR'],
        'KUBE_DEPLOYMENT_HELM_REPO_UPDATE_FAILED_ERROR' => Xphp::$_lang['WEB_KUBE_DEPLOYMENT_HELM_REPO_UPDATE_FAILED_ERROR'],
        'KUBE_DEPLOYMENT_HELM_INSTALL_FAILED_ERROR' => Xphp::$_lang['WEB_KUBE_DEPLOYMENT_HELM_INSTALL_FAILED_ERROR'],
        'KUBE_DEPLOYMENT_CLIENT_AGENT_ALREADY_INSTALLED_BEFORE_ERROR' => Xphp::$_lang['WEB_KUBE_DEPLOYMENT_CLIENT_AGENT_ALREADY_INSTALLED_BEFORE_ERROR'],
        'KUBE_SERVER_CLUSTER_NOT_EXISTS_ERROR' => Xphp::$_lang['WEB_KUBE_SERVER_CLUSTER_NOT_EXISTS_ERROR'],
        'KUBE_SERVER_NO_ONLINE_NODE_IN_CLUSTER_ERROR' => Xphp::$_lang['WEB_KUBE_SERVER_NO_ONLINE_NODE_IN_CLUSTER_ERROR'],
        'KUBE_SERVER_AGENT_IS_NOT_RUNNING_IN_NODE_ERROR' => Xphp::$_lang['WEB_KUBE_SERVER_AGENT_IS_NOT_RUNNING_IN_NODE_ERROR'],
        'KUBE_SERVER_GET_BACKUP_INFO_ERROR' => Xphp::$_lang['WEB_KUBE_SERVER_GET_BACKUP_INFO_ERROR'],
        'KUBE_SERVER_GET_RECOVERY_INFO_ERROR' => Xphp::$_lang['WEB_KUBE_SERVER_GET_RECOVERY_INFO_ERROR'],
        'KUBE_SERVER_PARSE_RESOURCE_RAW_JSON_FAILED_ERROR' => Xphp::$_lang['WEB_KUBE_SERVER_PARSE_RESOURCE_RAW_JSON_FAILED_ERROR'],
        'KUBE_SERVER_COMMAND_EXEC_FAILED_ERROR' => Xphp::$_lang['WEB_KUBE_SERVER_COMMAND_EXEC_FAILED_ERROR'],
        'KUBE_SERVER_COMMAND_EXEC_IN_POD_FAILED_ERROR' => Xphp::$_lang['WEB_KUBE_SERVER_COMMAND_EXEC_IN_POD_FAILED_ERROR'],
        'KUBE_SERVER_CREATE_BACKUP_DIR_ERROR' => Xphp::$_lang['WEB_KUBE_SERVER_CREATE_BACKUP_DIR_ERROR'],
        'KUBE_SERVER_GET_CLUSTER_INFO_OF_TASK_ERROR' => Xphp::$_lang['WEB_KUBE_SERVER_GET_CLUSTER_INFO_OF_TASK_ERROR'],
        'KUBE_SERVER_SERIALIZE_RESOURCES_ERROR' => Xphp::$_lang['WEB_KUBE_SERVER_SERIALIZE_RESOURCES_ERROR'],
        'KUBE_SERVER_UNSERIALIZE_RESOURCES_ERROR' => Xphp::$_lang['WEB_KUBE_SERVER_UNSERIALIZE_RESOURCES_ERROR'],
        'KUBE_SERVER_STORAGE_RESOURCE_NOT_FOUND_ERROR' => Xphp::$_lang['WEB_KUBE_SERVER_STORAGE_RESOURCE_NOT_FOUND_ERROR'],
        'KUBE_SERVER_READ_RESOURCE_FROM_STORAGE_ERROR' => Xphp::$_lang['WEB_KUBE_SERVER_READ_RESOURCE_FROM_STORAGE_ERROR'],
        'KUBE_SERVER_READ_INCORRECT_SIZE_RESOURCE_FROM_STORAGE_ERROR' => Xphp::$_lang['WEB_KUBE_SERVER_READ_INCORRECT_SIZE_RESOURCE_FROM_STORAGE_ERROR'],
        'KUBE_SERVER_INCORRECT_RESOURCE_IN_STORAGE_INDEX' => Xphp::$_lang['WEB_KUBE_SERVER_INCORRECT_RESOURCE_IN_STORAGE_INDEX'],
        'KUBE_SERVER_CREATE_RESOURCE_TIMEOUT_ERROR' => Xphp::$_lang['WEB_KUBE_SERVER_CREATE_RESOURCE_TIMEOUT_ERROR'],
        'KUBE_SERVER_DELETE_RESOURCE_TIMEOUT_ERROR' => Xphp::$_lang['WEB_KUBE_SERVER_DELETE_RESOURCE_TIMEOUT_ERROR'],
        'KUBE_SERVER_INVALID_WORKLOAD_TYPE_ERROR' => Xphp::$_lang['WEB_KUBE_SERVER_INVALID_WORKLOAD_TYPE_ERROR'],
        'KUBE_SERVER_DIG_RESOURCES_OF_APP_FAILED_ERROR' => Xphp::$_lang['WEB_KUBE_SERVER_DIG_RESOURCES_OF_APP_FAILED_ERROR'],
        'KUBE_SERVER_TRANSFER_BLOCK_WITH_BITMAP_FAILED_ERROR' => Xphp::$_lang['WEB_KUBE_SERVER_TRANSFER_BLOCK_WITH_BITMAP_FAILED_ERROR'],
        'KUBE_SERVER_STORAGE_PVC_DIR_NOT_EXISTS_ERROR' => Xphp::$_lang['WEB_KUBE_SERVER_STORAGE_PVC_DIR_NOT_EXISTS_ERROR'],
        'KUBE_SERVER_STORAGE_BLOCKS_INDEX_FILE_NOT_EXISTS_ERROR' => Xphp::$_lang['WEB_KUBE_SERVER_STORAGE_BLOCKS_INDEX_FILE_NOT_EXISTS_ERROR'],
        'KUBE_SERVER_STORAGE_READ_VOLUME_BLOCKS_INDEX_ERROR' => Xphp::$_lang['WEB_KUBE_SERVER_STORAGE_READ_VOLUME_BLOCKS_INDEX_ERROR'],
        'KUBE_SERVER_STORAGE_WRITE_BLOCK_INDEX_BUFFER_ERROR' => Xphp::$_lang['WEB_KUBE_SERVER_STORAGE_WRITE_BLOCK_INDEX_BUFFER_ERROR'],
        'KUBE_SERVER_STORAGE_PREVIOUS_TIMEPOINT_CORRUPTED_ERROR' => Xphp::$_lang['WEB_KUBE_SERVER_STORAGE_PREVIOUS_TIMEPOINT_CORRUPTED_ERROR'],
        'KUBE_SERVER_DEGRADATION_REASON_PREVIOUS_TIMEPOINT_NOT_EXIST_ERROR' => Xphp::$_lang['WEB_KUBE_SERVER_DEGRADATION_REASON_PREVIOUS_TIMEPOINT_NOT_EXIST_ERROR'],
        'KUBE_SERVER_DEGRADATION_REASON_BACKUP_NODE_CHANGED_ERROR' => Xphp::$_lang['WEB_KUBE_SERVER_DEGRADATION_REASON_BACKUP_NODE_CHANGED_ERROR'],
        'KUBE_SERVER_DEGRADATION_REASON_BACKUP_STORAGE_IS_CHANGED_ERROR' => Xphp::$_lang['WEB_KUBE_SERVER_DEGRADATION_REASON_BACKUP_STORAGE_IS_CHANGED_ERROR'],
        'KUBE_SERVER_DEGRADATION_REASON_LAST_TIMEPOINT_BROKEN_ERROR' => Xphp::$_lang['WEB_KUBE_SERVER_DEGRADATION_REASON_LAST_TIMEPOINT_BROKEN_ERROR'],
        'KUBE_SERVER_DEGRADATION_REASON_ENCRYPTION_INFO_CHANGED_ERROR' => Xphp::$_lang['WEB_KUBE_SERVER_DEGRADATION_REASON_ENCRYPTION_INFO_CHANGED_ERROR'],
        'KUBE_SERVER_DEGRADATION_REASON_COMPRESSION_INFO_CHANGED_ERROR' => Xphp::$_lang['WEB_KUBE_SERVER_DEGRADATION_REASON_COMPRESSION_INFO_CHANGED_ERROR'],
        'KUBE_SERVER_DEGRADATION_REASON_MAJOR_TASK_INFO_CHANGED_ERROR' => Xphp::$_lang['WEB_KUBE_SERVER_DEGRADATION_REASON_MAJOR_TASK_INFO_CHANGED_ERROR'],
        'KUBE_SERVER_BACKED_UP_STORAGE_DATA_IN_TIMEPOINT_IS_BROKEN_ERROR' => Xphp::$_lang['WEB_KUBE_SERVER_BACKED_UP_STORAGE_DATA_IN_TIMEPOINT_IS_BROKEN_ERROR'],
        'KUBE_SERVER_CREATE_RESOURCE_FAILED_ERROR' => Xphp::$_lang['WEB_KUBE_SERVER_CREATE_RESOURCE_FAILED_ERROR'],
        'KUBE_SERVER_PREPARE_RECOVERY_PVC_INSTANCE_FAILED_ERROR' => Xphp::$_lang['WEB_KUBE_SERVER_PREPARE_RECOVERY_PVC_INSTANCE_FAILED_ERROR'],
        'KUBE_SERVER_TARGET_PVC_IS_OCCUPIED_AND_CAN_NOT_DELETE_ERROR' => Xphp::$_lang['WEB_KUBE_SERVER_TARGET_PVC_IS_OCCUPIED_AND_CAN_NOT_DELETE_ERROR'],
        'KUBE_SERVER_RECOVER_PVC_DATA_FAILED_ERROR' => Xphp::$_lang['WEB_KUBE_SERVER_RECOVER_PVC_DATA_FAILED_ERROR'],
        'KUBE_SERVER_PVC_HAVE_NO_BOUND_PV_ERROR' => Xphp::$_lang['WEB_KUBE_SERVER_PVC_HAVE_NO_BOUND_PV_ERROR'],
        'KUBE_SERVER_TARGET_PVC_RECOVERY_TO_HAVE_DIFFERENT_VOLUME_MODE_ERROR' => Xphp::$_lang['WEB_KUBE_SERVER_TARGET_PVC_RECOVERY_TO_HAVE_DIFFERENT_VOLUME_MODE_ERROR'],
        'KUBE_SERVER_TARGET_PVC_RECOVERY_TO_HAVE_DIFFERENT_FILE_SYSTEM_ERROR' => Xphp::$_lang['WEB_KUBE_SERVER_TARGET_PVC_RECOVERY_TO_HAVE_DIFFERENT_FILE_SYSTEM_ERROR'],

        // 任务编排计划
        "STRATEGY_SERVER_SECTION_NOT_FOUND" => Xphp::$_lang['WEB_STRATEGY_SERVER_SECTION_NOT_FOUND'],
        "STRATEGY_SERVER_TASK_GROUP_NOT_FOUND" => Xphp::$_lang['WEB_STRATEGY_SERVER_TASK_GROUP_NOT_FOUND'],
        "STRATEGY_SERVER_PLAN_STOP" => Xphp::$_lang['WEB_STRATEGY_SERVER_PLAN_STOP'],
        "STRATEGY_SERVER_NOT_INITIAL_SECTION" => Xphp::$_lang['WEB_STRATEGY_SERVER_NOT_INITIAL_SECTION'],
        "STRATEGY_SERVER_INCONSECUTIVE_SECTION" => Xphp::$_lang['WEB_STRATEGY_SERVER_INCONSECUTIVE_SECTION'],
        "STRATEGY_SERVER_DRIVE_TASK_ERROR" => Xphp::$_lang['WEB_STRATEGY_SERVER_DRIVE_TASK_ERROR'],
        "STRATEGY_SERVER_SECTION_TRIGGER_CONDITION_NOT_MET" => Xphp::$_lang['WEB_STRATEGY_SERVER_SECTION_TRIGGER_CONDITION_NOT_MET'],


        //集群相关
        "BD_CLUSTER_GENERIC_ERROR" => Xphp::$_lang['WEB_ERROR_BD_CLUSTER_GENERIC_ERROR'],
        "BD_CLUSTER_MULTIPLE_MASTER_NODE_ALIVE" => Xphp::$_lang['WEB_ERROR_BD_CLUSTER_MULTIPLE_MASTER_NODE_ALIVE'],
        "BD_CLUSTER_NODE_IS_NOT_MASTER" => Xphp::$_lang['WEB_ERROR_BD_CLUSTER_NODE_IS_NOT_MASTER'],
        "BD_CLUSTER_QUERY_NODE_CONFIG_FAILED" => Xphp::$_lang['WEB_ERROR_BD_CLUSTER_QUERY_NODE_CONFIG_FAILED'],
        "BD_CLUSTER_NODE_VERSION_INCONSISTENCY" => Xphp::$_lang['WEB_ERROR_BD_CLUSTER_NODE_VERSION_INCONSISTENCY'],
        "BD_CLUSTER_NGINX_NO_FOUND_SIGN" => Xphp::$_lang['WEB_ERROR_BD_CLUSTER_NGINX_NO_FOUND_SIGN'],
        "BD_CLUSTER_CLUSTER_IS_NOT_RUNNING" => Xphp::$_lang['WEB_ERROR_BD_CLUSTER_CLUSTER_IS_NOT_RUNNING'],
        "BD_CLUSTER_CLUSTER_IS_NOT_STOPPED" => Xphp::$_lang['WEB_ERROR_BD_CLUSTER_CLUSTER_IS_NOT_STOPPED'],
        "BD_CLUSTER_NODE_ROLE_ERROR" => Xphp::$_lang['WEB_ERROR_BD_CLUSTER_NODE_ROLE_ERROR'],
        "BD_CLUSTER_CLUSTER_PRIORITY_ERROR" => Xphp::$_lang['WEB_ERROR_BD_CLUSTER_CLUSTER_PRIORITY_ERROR'],
        "BD_CLUSTER_VIP_DRIFT_ERROR" => Xphp::$_lang['WEB_ERROR_BD_CLUSTER_VIP_DRIFT_ERROR'],


        "BD_CLUSTER_TASK_CONFIG_SINGLE_NODE" => Xphp::$_lang['WEB_ERROR_BD_CLUSTER_TASK_CONFIG_SINGLE_NODE'],
        "BD_CLUSTER_TASK_CONFIG_SINGLE_STORATE" => Xphp::$_lang['WEB_ERROR_BD_CLUSTER_TASK_CONFIG_SINGLE_STORATE'],
        "BD_CLUSTER_TASK_CONFIG_SINGLE_APPLIANCE" => Xphp::$_lang['WEB_ERROR_BD_CLUSTER_TASK_CONFIG_SINGLE_APPLIANCE'],

        "BD_CLUSTER_LOAD_BALANCE_TIME_ERROR" => Xphp::$_lang['WEB_ERROR_BD_CLUSTER_LOAD_BALANCE_TIME_ERROR'],
        'BD_CLUSTER_LOAD_BALANCE_NODE_POOL_NO_ONLINE_NODE_ERROR' => Xphp::$_lang['WEB_ERROR_BD_CLUSTER_LOAD_BALANCE_NODE_POOL_NO_ONLINE_NODE_ERROR'],
        'BD_CLUSTER_LOAD_BALANCE_STORAGE_POOL_NO_ONLINE_STORAGE_ERROR' => Xphp::$_lang['WEB_ERROR_BD_CLUSTER_LOAD_BALANCE_STORAGE_POOL_NO_ONLINE_STORAGE_ERROR'],
        'BD_CLUSTER_LOAD_BALANCE_APPLIANCE_POOL_NO_ONLINE_APPLIANCE_ERROR' => Xphp::$_lang['WEB_ERROR_BD_CLUSTER_LOAD_BALANCE_APPLIANCE_POOL_NO_ONLINE_APPLIANCE_ERROR'],

        'BD_CLUSTER_NODE_IS_RESOURCE_LIMITED_STATUS' => Xphp::$_lang['WEB_ERROR_BD_CLUSTER_NODE_IS_RESOURCE_LIMITED_STATUS'],
        'BD_CLUSTER_ALL_NODE_IS_RESOURCE_LIMITED_STATUS' => Xphp::$_lang['WEB_ERROR_BD_CLUSTER_ALL_NODE_IS_RESOURCE_LIMITED_STATUS'],
        'BD_CLUSTER_TASK_NODE_TRANSFER_SUCCESS' => Xphp::$_lang['WEB_ERROR_BD_CLUSTER_TASK_NODE_TRANSFER_SUCCESS'],
        'BD_CLUSTER_NODE_NOT_CONFIG_RESOURCE_LIMITED' => Xphp::$_lang['WEB_ERROR_BD_CLUSTER_NODE_NOT_CONFIG_RESOURCE_LIMITED'],

        'BD_CLUSTER_MASTER_NODE_ABNORMAL' => Xphp::$_lang['WEB_ERROR_BD_CLUSTER_MASTER_NODE_ABNORMAL'],

        'TOOL_SERVER_DRIVER_DIR_EMPTY_ERROR' => Xphp::$_lang['WEB_ERROR_TOOL_SERVER_DRIVER_DIR_EMPTY_ERROR'],
        'TOOL_SERVER_MULTIPLE_DRIVERS_ERROR' => Xphp::$_lang['WEB_ERROR_TOOL_SERVER_MULTIPLE_DRIVERS_ERROR'],
        'TOOL_SERVER_LACK_DRIVER_FILE_ERROR' => Xphp::$_lang['WEB_ERROR_TOOL_SERVER_LACK_DRIVER_FILE_ERROR'],
        'TOOL_SERVER_PARSE_DRIVER_FILE_ERROR' => Xphp::$_lang['WEB_ERROR_TOOL_SERVER_PARSE_DRIVER_FILE_ERROR'],
        'TOOL_SERVER_TARGET_MACHINE_HARDWARE_INFO_EMPTY_ERROR' => Xphp::$_lang['WEB_ERROR_TOOL_SERVER_TARGET_MACHINE_HARDWARE_INFO_EMPTY_ERROR'],
        'TOOL_SERVER_OS_VERSION_INVALID_ERROR' => Xphp::$_lang['WEB_ERROR_TOOL_SERVER_OS_VERSION_INVALID_ERROR'],
        'TOOL_SERVER_CPU_ARCH_INVALID_ERROR' => Xphp::$_lang['WEB_ERROR_TOOL_SERVER_CPU_ARCH_INVALID_ERROR'],
        'TOOL_SERVER_DISK_BUS_EMPTY_ERROR' => Xphp::$_lang['WEB_ERROR_TOOL_SERVER_DISK_BUS_EMPTY_ERROR'],
        'TOOL_SERVER_NET_BUS_EMPTY_ERROR' => Xphp::$_lang['WEB_ERROR_TOOL_SERVER_NET_BUS_EMPTY_ERROR'],
        'TOOL_SERVER_UNSUPPORT_CONTROLLER_TYPE_ERROR' => Xphp::$_lang['WEB_ERROR_TOOL_SERVER_UNSUPPORT_CONTROLLER_TYPE_ERROR'],
        'TOOL_SERVER_UNKNOWN_CONTROLLER_TYPE_ERROR' => Xphp::$_lang['WEB_ERROR_TOOL_SERVER_UNKNOWN_CONTROLLER_TYPE_ERROR'],
        'TOOL_SERVER_FILE_FORMAT_ERROR' => Xphp::$_lang['WEB_ERROR_TOOL_SERVER_FILE_FORMAT_ERROR'],
        'TOOL_SERVER_FILE_CONTENT_ERROR' => Xphp::$_lang['WEB_ERROR_TOOL_SERVER_FILE_CONTENT_ERROR'],
        'TOOL_SERVER_FILE_VERSION_ERROR' => Xphp::$_lang['WEB_ERROR_TOOL_SERVER_FILE_VERSION_ERROR'],

        // 副本
        'COPY_SERVER_GET_COPY_INFO_ERROR' => Xphp::$_lang['WEB_ERROR_COPY_SERVER_GET_COPY_INFO_ERROR'],
        'COPY_SERVER_SOURCE_TIMEPOINT_CAN_NOT_COPIED' => Xphp::$_lang['WEB_ERROR_COPY_SERVER_SOURCE_TIMEPOINT_CAN_NOT_COPIED'],
        'COPY_SERVER_COPY_ITEM_NOT_FOUND_TIMEPOINT' => Xphp::$_lang['WEB_ERROR_COPY_SERVER_COPY_ITEM_NOT_FOUND_TIMEPOINT'],
        'COPY_SERVER_CHAIN_LENGTH_LIMIT' => Xphp::$_lang['WEB_ERROR_COPY_SERVER_CHAIN_LENGTH_LIMIT'],
        'COPY_SERVER_SRC_TIMEPOINT_NOT_FOUND' => Xphp::$_lang['WEB_ERROR_COPY_SERVER_SRC_TIMEPOINT_NOT_FOUND'],
        'COPY_SERVER_DEPEND_TIMEPOINT_NOT_FOUND' => Xphp::$_lang['WEB_ERROR_COPY_SERVER_DEPEND_TIMEPOINT_NOT_FOUND'],
        'COPY_SERVER_PASSWORD_CHANGED' => Xphp::$_lang['WEB_ERROR_COPY_SERVER_PASSWORD_CHANGED'],
        'COPY_SERVER_COMPRESSED_INFO_CHANGED' => Xphp::$_lang['WEB_ERROR_COPY_SERVER_COMPRESSED_INFO_CHANGED'],
        'COPY_SERVER_CONTAINER_FULL' => Xphp::$_lang['WEB_ERROR_COPY_SERVER_CONTAINER_FULL'],
        'COPY_SERVER_WRITE_BITMAP_FILE_ERROR' => Xphp::$_lang['WEB_ERROR_COPY_SERVER_WRITE_BITMAP_FILE_ERROR'],
        'COPY_SERVER_RPC_INIT_ERROR' => Xphp::$_lang['WEB_ERROR_COPY_SERVER_RPC_INIT_ERROR'],
        'COPY_SERVER_DEVICE_INFO_CHANGED' => Xphp::$_lang['WEB_ERROR_COPY_SERVER_DEVICE_INFO_CHANGED'],
        'COPY_SERVER_TIMEPOINT_READER_NOT_INIT' => Xphp::$_lang['WEB_ERROR_COPY_SERVER_TIMEPOINT_READER_NOT_INIT'],
        'COPY_SERVER_TIMEPOINT_READER_INIT_ERROR' => Xphp::$_lang['WEB_ERROR_COPY_SERVER_TIMEPOINT_READER_INIT_ERROR'],
        'COPY_SERVER_MERGING_ERROR' => Xphp::$_lang['WEB_ERROR_COPY_SERVER_MERGING_ERROR'],
        'COPY_SERVER_FILE_REOPEN_ERROR' => Xphp::$_lang['WEB_ERROR_COPY_SERVER_FILE_REOPEN_ERROR'],
        'COPY_SERVER_BITMAP_INFO_ERROR' => Xphp::$_lang['WEB_ERROR_COPY_SERVER_BITMAP_INFO_ERROR'],
        'COPY_SERVER_HANDLE_TRANSPORT_DATA_ERROR' => Xphp::$_lang['WEB_ERROR_COPY_SERVER_HANDLE_TRANSPORT_DATA_ERROR'],
        'COPY_SERVER_HASH_INC_NOT_VALID_ERROR' => Xphp::$_lang['WEB_ERROR_COPY_SERVER_HASH_INC_NOT_VALID_ERROR'],
        'COPY_SERVER_WORM_FLAG_CHANGED' => Xphp::$_lang['WEB_ERROR_COPY_SERVER_WORM_FLAG_CHANGED'],

        'EMD_VM_ISOLATE_NETWORK_CONFIG_OVER_RANGE' => Xphp::$_lang['WEB_ERROR_EMD_VM_ISOLATE_NETWORK_CONFIG_OVER_RANGE'],
        'EMD_VM_SERVER_RESOURCE_INIT_ERROR' => Xphp::$_lang['WEB_ERROR_EMD_VM_SERVER_RESOURCE_INIT_ERROR'],
        'EMD_VM_SERVER_CANT_CREATE_BRIDGE_BY_NIC_WITHOUT_IP' => Xphp::$_lang['WEB_ERROR_EMD_VM_SERVER_CANT_CREATE_BRIDGE_BY_NIC_WITHOUT_IP'],
        'EMD_VM_ISOLATE_NET_RELOCATE_CONFLICT' => Xphp::$_lang['WEB_ERROR_EMD_VM_ISOLATE_NET_RELOCATE_CONFLICT'],
        'EMD_VM_VIRTUAL_NETWORK_EXISTS_ON_NODE' => Xphp::$_lang['WEB_ERROR_EMD_VM_VIRTUAL_NETWORK_EXISTS_ON_NODE'],
        'STRATEGY_SERVER_GET_STRATEGY_ERROR' => Xphp::$_lang['WEB_ERROR_STRATEGY_SERVER_GET_STRATEGY_ERROR'],
        'STRATEGY_SERVER_GET_TASK_PEIOEITY_ERROR' => Xphp::$_lang['WEB_ERROR_STRATEGY_SERVER_GET_TASK_PEIOEITY_ERROR'],
        'STRATEGY_SERVER_TIME_CONVERT_ERROR' => Xphp::$_lang['WEB_ERROR_STRATEGY_SERVER_TIME_CONVERT_ERROR'],
        'STRATEGY_SERVER_QUERY_ALL_STRATEGY_ERROR' => Xphp::$_lang['WEB_ERROR_STRATEGY_SERVER_QUERY_ALL_STRATEGY_ERROR'],
        'STRATEGY_SERVER_STRATEGY_NULL_ERROR' => Xphp::$_lang['WEB_ERROR_STRATEGY_SERVER_STRATEGY_NULL_ERROR'],
        'STRATEGY_SERVER_STRATEGY_ALREADY_START_ERROR' => Xphp::$_lang['WEB_ERROR_STRATEGY_SERVER_STRATEGY_ALREADY_START_ERROR'],
        'STRATEGY_SERVER_TIME_STRATEGY_CONFIG_ERROR' => Xphp::$_lang['WEB_ERROR_STRATEGY_SERVER_TIME_STRATEGY_CONFIG_ERROR'],
    )
);
?>