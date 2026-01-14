resource "digitalocean_project" "my_project" {
  name        = local.name
  description = var.description
  environment = var.environment
}
