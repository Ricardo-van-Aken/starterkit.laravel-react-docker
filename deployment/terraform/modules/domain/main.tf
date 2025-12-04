# Create a new domain
resource "digitalocean_domain" "my_domain" {
  name       = var.name
  ip_address = var.droplet_ipv4
}

resource "digitalocean_record" "www" {
  domain = digitalocean_domain.my_domain.id
  type   = "CNAME"
  name   = "www"
  value  = "@"
}

resource "digitalocean_project_resources" "assign_resources" {
  project   = var.project_id
  resources = [digitalocean_domain.my_domain.urn]
}
