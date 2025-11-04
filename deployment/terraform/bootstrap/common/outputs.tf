output "spaces_access_id" {
  value     = module.key.spaces_access_id
  sensitive = true
}

output "spaces_secret_key" {
  value     = module.key.spaces_secret_key
  sensitive = true
}
