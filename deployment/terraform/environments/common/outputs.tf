output "droplet_ipv4" {
  value = module.droplet.ipv4
}

output "db_host" {
  value = module.database.host
}

output "db_port" {
  value = module.database.port
}

output "db_name" {
  value = module.database.database
}

output "db_user" {
  value = module.database.user
}

output "db_password" {
  value     = module.database.password
  sensitive = true
}

output "redis_password" {
  value     = random_password.redis_password.result
  sensitive = true
}

output "app_key" {
  value     = "base64:${random_bytes.app_key.base64}"
  sensitive = true
}
