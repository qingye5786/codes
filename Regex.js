/**
 * 验证手机号码
 * 验证规则：11位数字，以1开头。
 * checkMobile('13800138000'); //调用
 * checkMobile('139888888889');//错误示例
*/
function checkMobile(str) {
    var re = /^1\d{10}$/
    if (re.test(str)) {
        return true;
    } else {
        return false;
    }
}
 
/**
 * 验证电话号码
 * 验证规则：区号+号码，区号以0开头，3位或4位
 * 号码由7位或8位数字组成
 * 区号与号码之间可以无连接符，也可以“-”连接
 * 如01088888888,010-88888888,0955-7777777 
 * checkPhone("09557777777");//调用
 */

function checkPhone(str) {
    var re = /^0\d{2,3}-?\d{7,8}$/;
    if(re.test(str)) {
        return true;
    } else {
        return false;
    }
}

/**
 * 验证邮箱
 * 验证规则：姑且把邮箱地址分成“第一部分@第二部分”这样
 * 第一部分：由字母、数字、下划线、短线“-”、点号“.”组成，
 * 第二部分：为一个域名，域名由字母、数字、短线“-”、域名后缀组成，
 * 而域名后缀一般为.xxx或.xxx.xx，一区的域名后缀一般为2-4位，如cn,com,net，现在域名有的也会大于4位
 * checkEmail("contact@cnblogs.com");//调用
 */

function checkEmail(str) {
    var re = /^(\w-*\.*)+@(\w-?)+(\.\w{2,})+$/
    if(re.test(str)) {
        return true;
    } else {
        return false;
    }
}
