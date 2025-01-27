<?php

namespace Nuwave\Lighthouse\Subscriptions;

use GraphQL\Language\AST\DocumentNode;
use GraphQL\Language\AST\NodeList;
use GraphQL\Language\AST\OperationDefinitionNode;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Utils\AST;
use Illuminate\Container\Container;
use Illuminate\Support\Str;
use Nuwave\Lighthouse\Subscriptions\Contracts\ContextSerializer;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class Subscriber
{
    /**
     * A unique key for the subscriber's channel.
     *
     * This has to be unique for each subscriber, because each of them can send a different
     * query and must receive a response that is specifically tailored towards that.
     *
     * @var string
     */
    public $channel;

    /**
     * The topic subscribed to.
     *
     * @var string
     */
    public $topic;

    /**
     * The contents of the query.
     *
     * @var \GraphQL\Language\AST\DocumentNode
     */
    public $query;

    /**
     * The name of the queried field.
     *
     * Guaranteed be be unique because of
     *
     * @see \GraphQL\Validator\Rules\SingleFieldSubscription
     *
     * @var string
     */
    public $fieldName;

    /**
     * The root element of the query.
     *
     * @var mixed can be anything
     */
    public $root;

    /**
     * The args passed to the subscription query.
     *
     * @var array<string, mixed>
     */
    public $args;

    /**
     * The variables passed to the subscription query.
     *
     * @var array<string, mixed>
     */
    public $variables;

    /**
     * The context passed to the query.
     *
     * @var \Nuwave\Lighthouse\Support\Contracts\GraphQLContext
     */
    public $context;

    /**
     * @param  array<string, mixed>  $args
     */
    public function __construct(
        array $args,
        GraphQLContext $context,
        ResolveInfo $resolveInfo
    ) {
        $this->fieldName = $resolveInfo->fieldName;
        $this->channel = self::uniqueChannelName();
        $this->args = $args;
        $this->variables = $resolveInfo->variableValues;
        $this->context = $context;

        $operation = $resolveInfo->operation;
        assert($operation instanceof OperationDefinitionNode, 'Must be here, since webonyx/graphql-php validated the subscription.');

        $this->query = new DocumentNode([
            'definitions' => new NodeList(array_merge(
                $resolveInfo->fragments,
                [$operation]
            )),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function __serialize(): array
    {
        return [
            'channel' => $this->channel,
            'topic' => $this->topic,
            'query' => serialize(
                AST::toArray($this->query)
            ),
            'field_name' => $this->fieldName,
            'args' => $this->args,
            'variables' => $this->variables,
            'context' => $this->contextSerializer()->serialize($this->context),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function __unserialize(array $data): void
    {
        $this->channel = $data['channel'];
        $this->topic = $data['topic'];

        $documentNode = AST::fromArray(
            unserialize($data['query'])
        );
        assert($documentNode instanceof DocumentNode, 'We know the type since it is set during construction and serialized.');

        $this->query = $documentNode;
        $this->fieldName = $data['field_name'];
        $this->args = $data['args'];
        $this->variables = $data['variables'];
        $this->context = $this->contextSerializer()->unserialize(
            $data['context']
        );
    }

    /**
     * Set root data.
     *
     * @deprecated set the attribute directly
     */
    public function setRoot($root): self
    {
        $this->root = $root;

        return $this;
    }

    /**
     * Generate a unique private channel name.
     */
    public static function uniqueChannelName(): string
    {
        return 'private-lighthouse-' . Str::random(32) . '-' . time();
    }

    protected function contextSerializer(): ContextSerializer
    {
        return Container::getInstance()->make(ContextSerializer::class);
    }
}
