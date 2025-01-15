# Overview

This is your README file. Together with it, you can find the spec files in the `docs` folder. Remember to keep them updated, just like this file!

The observer service is a laravel application.

The service is also awesome.

## Important Links

- [Dashboard](https://onenr.io/your-dashboard-url)

# Running on local

Follow the steps below to run the service on your local machine.

<Steps title="First run on local">
<Step title="Build">

```bash
make build
```

</Step>
<Step title="Start">

```bash
make start
```

</Step>
<Step title="Install dependencies">

```bash
make install
```
</Step>
<Step title="Setup Tests">

```bash
make test-setup
```
</Step>
</Steps>

On the following runs, only `make start` is required unless new migrations or dependencies have been added.
The http endpoints will be available at [localhost:23331](http://localhost:23331).
It uses a mysql database which can be found on port 39054.
