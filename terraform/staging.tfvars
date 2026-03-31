# Staging environment
aws_region    = "ap-southeast-1"
environment   = "staging"
app_name      = "automatecrm"

# Smaller instances for staging
db_instance_class = "db.t3.micro"
ecs_task_cpu      = 256
ecs_task_memory   = 512
desired_count     = 1

# Database
db_name     = "automatecrm"
db_username = "automatecrm_admin"
