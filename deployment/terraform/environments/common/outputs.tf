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
