output "urn" {
  value = digitalocean_droplet.my_droplet.urn
}

output "ipv4" {
  value = digitalocean_droplet.my_droplet.ipv4_address
}
