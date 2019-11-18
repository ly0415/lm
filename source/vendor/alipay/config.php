<?php
$config = array (	
		//应用ID,您的APPID。
		'app_id' => "2017011905254523",

		//商户私钥
		'merchant_private_key' => "MIIEowIBAAKCAQEA1/oxCP3C+S/0iJT2XBrljiwD2REn909bf+9kWsnIFpw4GwtWr59no6EQ/BU9LiaC0jDJOd9RIXbNkdIMVTI4fKG5yde3Dkc8pr1v5I+y7IDKGEiTrcL3yQWaO9bOCWKqQyOzX4/eF5GFw8Ih23oXSEPd2QWspJmxVWAFCQDVu+Tikx3cu1mIMa7z9a7lN/tA9ctYafLqCli4iApEWmcXeE5fCMjSYnTwD2fSu48MBBCGSBWwZs3wM3bMI3KP06r5jA5o/AJQKMSH5CQEJ09sLJyeosdYiPHWbG7zdyKH1PZNhUIcOfEm2+a/keTqJ2ZEI01M80H2LSCYC5JKbO6KXwIDAQABAoIBAG5+sMGR2kNUdn2+AEBk/lZ7TEisj07micBtQGF2ZGi06btkVKgrHIHJcIAXeaJ3z2wry3dROhetyUQ2O1sHA4E32G5cb2ndpjkEKA++OOLojPxZfTxjyBNPS3Yb0nNYyBTrWeSlHRHfwJjDZED+OJUfK4vRbF8VxnUQV+MgSzkB0v5DxbX9CflUsELusCbhfU0a0H6MuHlVdh4c8Fsmf1xRPtz/tpZwLU4+FetAll2NJoE35lvoJF+jiCw5AY1PY6Y0SaJIdM/dr4UuSzFXHCkAq/0OHRGKR/ajNGDSBhq9iINhU/sUYT87G9b09MBJ2ySM0d9U3w0Fx1EhAXXvQqkCgYEA8XAqj9JHTNi/TMxLmUWFRV18Qo2QS6Atm4GD4edKal8sBnSKMqTlstlf5cNTZ6ak2cATMiuaCHswBpmAtw9q1U7rpZ19NBVrUHKLG2UU1YAIQQ5O5Ct3C/mPqtYT3W18mKzZ5p8wL9tk9OwjY89c6RpFseRfvHk/LLEtGdUzgIMCgYEA5QDoKyRQgyVVIK2w0Obdovn8I8zI8Ay2204atawl32ZxgZX1SJlXC87oPv3o4S2XAsSRpATtlT8Ltwa3rQ93FaqWK4/rPLS05ZpughlOSJSlAK9OsmF4Xdqp4CBPTj/WGQrgd+fCa7GEsUKPrUoBjooC2IM3HEUUgPYcwgsZr/UCgYBSCBU99mkpT/93XXZWJkvIrKG6jxS2zT6RtmiTyZz8FUgFDXWjDWnJ4Zd2nm3pKrKaFWuwQSY9uXUw2Njl2cQno3/nLmJK3vguRizDaw2wGKc1S2I8nhP9qpZIqiHnuvp5eUkz1WRu7jEYEl9X2y2rObTyYzCv/dYcHjq/qzOrdwKBgBvNyWJ7jT7vCG/oRsCGV0CTY3ahRYBHuufTitCl7w85q+xU3awL2hK382C6iUzVsTEH1rr4UjQ9rFlzeleLuiSqSoNNfP0o35HE90fadLPBQGtd3Ysw5GFYzClHIvnYLFFsDabhP6y9p+OxtioPAzNgNEo/XDCVfpDN0N4KZPsFAoGBALDwQ7AvmEFARfx0mBI5Q577MqVaVvLwhoiEMtL/X6aGTAxQdv2QryYfa2fjRMUBZiNu1am93NkwkWAs6+geDRNqhdRHWp10lesWcj6Eb9xsLbe4oQsd1U71nz6UrZQtmUNyRV+BZ0Bxbh98Jz33KdEl/pkTncl23zASRVMAud+j",
		
		//异步通知地址
		'notify_url' => "https://www.baidu.com/notify_url.php",
		
		//同步跳转
		'return_url' => "https://www.baidu.com/return_url.php",

		//编码格式
		'charset' => "UTF-8",

		//签名方式
		'sign_type'=>"RSA2",

		//支付宝网关
		'gatewayUrl' => "https://openapi.alipay.com/gateway.do",

		//支付宝公钥,查看地址：https://openhome.alipay.com/platform/keyManage.htm 对应APPID下的支付宝公钥。
		'alipay_public_key' => "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAnJ5Z9Bo7XsApaPKVl3xgzGxJjCMYjWXMS1dA6hCDkBVb/bMeY/Ta7KVwjhbKO3PZW59bZD7t8BAcsA1/7FUsjZUYJQMh9+FbOclv5pbSShjBzFpS5C7ZdctUxk3AOwHbyXHeh39rY4RX2a/lnLDvt3SFKzLu9ebA2hSeKLkz1m35D9cdbS8Ypxg4XjoJdDe1cwS0VmJrcJ8otxibCZPBTc0vp24d2j/9AZUMTAff/MgeoGQV+R5llKAtd5j48bDZqEKBo+RGjzquJRY1eSuCxHPMfSj4/KXHr+JRHb9+g9hTLT+hYYrOwQKRXDQzBbOG50NkqiACQdhJbnQBeVeqtwIDAQAB",
);