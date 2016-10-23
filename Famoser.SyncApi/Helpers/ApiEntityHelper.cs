using System;
using Famoser.SyncApi.Api.Base;
using Famoser.SyncApi.Api.Communication.Entities;
using Famoser.SyncApi.Enums;
using Famoser.SyncApi.Storage.Cache.Entitites;
using Newtonsoft.Json;

namespace Famoser.SyncApi.Helpers
{
    public class ApiEntityHelper
    {
        private static T CreateApiEntity<T>(ModelInformation info, string identifier, Func<object> getModelFunc)
            where T : BaseEntity, new()
        {
            if (info.PendingAction == PendingAction.None)
                return null;

            var modl = new T()
            {
                Id = info.Id,
                VersionId = info.VersionId,
                OnlineAction = Convert(info.PendingAction),
                Identifier = identifier
            };
            if (info.PendingAction == PendingAction.Create || info.PendingAction == PendingAction.Update)
                modl.Content = JsonConvert.SerializeObject(getModelFunc.Invoke());
            if (info.PendingAction == PendingAction.Create)
                modl.CreateDateTime = info.CreateDateTime;

            return modl;
        }

        public static CollectionEntity CreateCollectionEntity(ModelInformation info, string identifier, Func<object> getModelFunc)
        {
            if (info.PendingAction == PendingAction.None)
                return null;

            var mdl = CreateApiEntity<CollectionEntity>(info, identifier, getModelFunc);
            if (mdl != null)
            {
                mdl.DeviceId = info.DeviceId;
                mdl.UserId = info.UserId;
            }
            return mdl;
        }

        public static SyncEntity CreateSyncEntity(ModelInformation info, string identifier, Func<object> getModelFunc)
        {
            if (info.PendingAction == PendingAction.None)
                return null;

            var mdl = CreateApiEntity<SyncEntity>(info, identifier, getModelFunc);
            if (mdl != null)
            {
                mdl.DeviceId = info.DeviceId;
                mdl.UserId = info.UserId;
                mdl.CollectionId = info.CollectionId;
            }
            return mdl;
        }


        private static ModelInformation CreateModelInformation(BaseEntity entity)
        {
            return new ModelInformation()
            {
                Id = entity.Id,
                VersionId = entity.VersionId,
                PendingAction = PendingAction.None,
                CreateDateTime = entity.CreateDateTime
            };
        }

        public static ModelInformation CreateModelInformation(CollectionEntity entity)
        {
            var mi = CreateModelInformation(entity as BaseEntity);
            mi.UserId = entity.UserId;
            mi.DeviceId = entity.DeviceId;
            return mi;
        }

        public static ModelInformation CreateModelInformation(SyncEntity entity)
        {
            var mi = CreateModelInformation(entity as BaseEntity);
            mi.UserId = entity.UserId;
            mi.DeviceId = entity.DeviceId;
            mi.CollectionId = entity.CollectionId;
            return mi;
        }


        private static OnlineAction Convert(PendingAction action)
        {
            switch (action)
            {
                case PendingAction.Create: return OnlineAction.Create;
                case PendingAction.Read: return OnlineAction.Read;
                case PendingAction.Update: return OnlineAction.Update;
                case PendingAction.Delete: return OnlineAction.Delete;
                default:
                    return OnlineAction.None;
            }
        }
    }
}
