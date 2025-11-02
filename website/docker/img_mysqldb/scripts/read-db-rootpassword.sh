# Source root password from Docker secret
if [ -f /run/secrets/mysql_db_rootpass ]; then
  MYSQL_ROOT_PASSWORD=$(cat /run/secrets/mysql_db_rootpass | tr -d '\r\n')
  export MYSQL_ROOT_PASSWORD
fi