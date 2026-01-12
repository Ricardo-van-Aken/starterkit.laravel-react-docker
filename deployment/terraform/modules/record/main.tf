resource "digitalocean_record" "record" {
  domain = var.domain_id
  type   = var.type
  name   = var.name
  value  = var.value
}
