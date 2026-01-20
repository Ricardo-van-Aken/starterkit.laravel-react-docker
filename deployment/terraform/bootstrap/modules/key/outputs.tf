output "spaces_access_id" {
  value     = digitalocean_spaces_key.admin_key.access_key
  sensitive = true
}

output "spaces_secret_key" {
  value     = digitalocean_spaces_key.admin_key.secret_key
  sensitive = true
}
