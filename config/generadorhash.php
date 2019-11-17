<?PHP

  //ECHO password_hash("123456",PASSWORD_DEFAULT);

  ECHO base64_encode(openssl_random_pseudo_bytes(64));
  
//ECHO base64_encode(openssl_random_pseudo_bytes(32));
?>
