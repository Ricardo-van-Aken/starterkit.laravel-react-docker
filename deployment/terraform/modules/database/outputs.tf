output "urn" {
  value = digitalocean_database_cluster.my_cluster.urn
}

output "host" {
  value = digitalocean_database_cluster.my_cluster.host
}

output "port" {
  value = digitalocean_database_cluster.my_cluster.port
}

output "database" {
  value = digitalocean_database_db.my_database.name
}

output "user" {
  value = digitalocean_database_user.my_user.name
}

output "password" {
  value     = digitalocean_database_user.my_user.password
  sensitive = true
}

output "ca_cert" {
  value     = data.digitalocean_database_ca.ca.certificate
  sensitive = true
}
