#Contributing to Serve-OPG

The rewrite of Serve-OPG is being used as an opportunity to develop skills in a number of areas that OPG Digital deem to be important.

## Golang

Serve-OPG will use Golang as the main language.

OPG Digital as a whole has decided to stop developing in PHP and adopt two main languages which should be used for any new projects going forward - Golang and Python. There are a number of reasons for the shift - Golang focused reasons are below.

PHP is a declining skill:

- less community support

- harder to hire over time

- worse response to security issues

- increased associated costs over time

Additionally, we have a fractured PHP knowledge base due to different frameworks used within OPG.

Golang is an emerging technology that is very attractive to candidates and provides a number of side benefits:

- making future hiring easier
- tech benefits around small docker image sizes
- better community support

Using Golang will also lead to tech alignment of preferred technical skills with the WebOps profession which will:

- reduce surface area
- increase opportunities for knowledge sharing and close working

Golang bills itself as being perfect for building “simple, reliable, and efficient software“ and is well-known to be more performant than PHP. The focus on simplicity, and a powerful standard library, means our reliance on using custom frameworks, as is the case with our PHP based apps, can be reduced leading to code that is easier to understand and reason about.

## Test Driven Development (TDD)

TDD is the process of writing a failing tests based on a user story or expectation, writing the simplest code to pass the test and then refactoring. This red-green-refactor cycle is repeated until all the requirements have been met.

By following this pattern, a focused design emerges where you write only the code you need that is fully covered by tests. It also ensures that the structure of your code follows many of the SOLID principals due to the necessity of ensuring your code is testable.

As an experiment, all code written in Serve-OPG should be done so following TDD. Once the project has been completed we can gain some real life insight into how it impacts a projects delivery, quality and reliability.

## Pairing

Pair programming involves having two developers work on the same task in two distinct roles - the Driver and the Navigator. The driver has their hands on the keyboard and is the one who is physically typing out the code and manipulating the code editor. The Navigator is responsible for keeping an eye on the bigger picture and spotting potential pitfalls with ideas before they can happen. 

Depending on the style of pair programming, the roles are reversed on a regular basis using either time, milestones or a failing test as the trigger to switch.

As an additional experiment for Serve-OPG, all code written should be as part of a pair. As with TDD, we can evaluate once the project is complete to see the impact this has on the code and the coders.

Tuple has a great pairing checklist that is included below to start things off on the right foot:

```text
[ ] Agree on the high-level goal out loud.
[ ] Break the work into a handful of tasks and prioritize them.
[ ] Decide your driver/navigator swapping strategy.
[ ] Configure git to share credit.
[ ] Eliminate distractions.
[ ] Work.
[ ] Analyze the session with a mini retro.
```

Full info on each step can be found [here](https://tuple.app/pair-programming-guide/template) along with some recorded pairing sessions.
